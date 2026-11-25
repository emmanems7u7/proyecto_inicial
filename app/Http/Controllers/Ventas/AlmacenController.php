<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\Inventario;
use App\Models\MovimientoInventario;
use App\Models\User;
use App\Support\TiendaContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AlmacenController extends Controller
{
    use UsaContexto;

    public function index(Request $request)
    {
        $tiendas = TiendaContext::permitidas()->pluck('nombre', 'id');
        $tiendaId = $request->filled('tienda_id') && $tiendas->has((int) $request->input('tienda_id'))
            ? (int) $request->input('tienda_id')
            : (TiendaContext::actual()?->id ?? $tiendas->keys()->first());

        $almacenes = Almacen::with('encargado')
            ->where('tienda_id', $tiendaId)
            ->orderByDesc('es_principal')->orderBy('codigo')
            ->get();

        $stats = Inventario::whereIn('almacen_id', $almacenes->pluck('id'))->where('cantidad', '>', 0)
            ->selectRaw('almacen_id, COUNT(*) as productos, SUM(cantidad) as unidades')
            ->groupBy('almacen_id')->get()->keyBy('almacen_id');

        // "En tránsito": mercadería de transferencias enviadas y aún no recibidas (no es un almacén físico).
        $transito = DB::table('transferencia_detalles as d')
            ->join('transferencias as t', 't.id', '=', 'd.transferencia_id')
            ->where('t.estado', 'en_transito')
            ->where(fn($q) => $q->where('t.tienda_origen_id', $tiendaId)->orWhere('t.tienda_destino_id', $tiendaId))
            ->selectRaw('COUNT(DISTINCT d.producto_id) as productos, COALESCE(SUM(d.cantidad), 0) as unidades')
            ->first();

        $nuevo = new Almacen(['estado' => 1, 'tienda_id' => $tiendaId, 'tipo' => 'principal']);
        $encargados = $this->encargados();
        $tiendaNombre = $tiendas[$tiendaId] ?? '';
        $breadcrumb = $this->breadcrumb(['Almacenes' => route('almacenes.index')]);

        return view('ventas.almacenes.index', compact('almacenes', 'stats', 'transito', 'nuevo', 'encargados', 'tiendas', 'tiendaId', 'tiendaNombre', 'breadcrumb'));
    }

    public function create()
    {
        $almacen = new Almacen(['estado' => 1, 'tienda_id' => TiendaContext::actual()?->id, 'tipo' => 'principal']);
        $tiendas = TiendaContext::permitidas()->pluck('nombre', 'id');
        $encargados = $this->encargados();
        $breadcrumb = $this->breadcrumb(['Almacenes' => route('almacenes.index'), 'Nuevo' => null]);

        return view('ventas.almacenes.create', compact('almacen', 'tiendas', 'encargados', 'breadcrumb'));
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        DB::transaction(function () use ($data) {
            $data['codigo'] = $data['codigo'] ?: $this->codigoSiguiente((int) $data['tienda_id']);
            if ($data['es_principal']) {
                Almacen::where('tienda_id', $data['tienda_id'])->update(['es_principal' => false]);
            }
            Almacen::create($data);
        });

        return redirect()->route('almacenes.index', ['tienda_id' => $data['tienda_id']])->with('success', 'Almacén creado.');
    }

    public function edit(Almacen $almacen)
    {
        $this->autorizarTienda($almacen->tienda_id);
        $tiendas = TiendaContext::permitidas()->pluck('nombre', 'id');
        $encargados = $this->encargados();
        $breadcrumb = $this->breadcrumb(['Almacenes' => route('almacenes.index'), 'Editar' => null]);

        return view('ventas.almacenes.edit', compact('almacen', 'tiendas', 'encargados', 'breadcrumb'));
    }

    public function update(Request $request, Almacen $almacen)
    {
        $this->autorizarTienda($almacen->tienda_id);
        $data = $this->validar($request, $almacen);
        $data['codigo'] = $data['codigo'] ?: $almacen->codigo;
        if (
            (int) $data['tienda_id'] !== (int) $almacen->tienda_id
            && MovimientoInventario::where('almacen_id', $almacen->id)->exists()
        ) {
            return back()->withInput()->with('error', 'No puedes mover de tienda un almacén que ya tiene movimientos.');
        }
        DB::transaction(function () use ($data, $almacen) {
            if ($data['es_principal']) {
                Almacen::where('tienda_id', $data['tienda_id'])->where('id', '!=', $almacen->id)->update(['es_principal' => false]);
            }
            $almacen->update($data);
        });

        return redirect()->route('almacenes.index', ['tienda_id' => $data['tienda_id']])->with('success', 'Almacén actualizado.');
    }

    public function destroy(Almacen $almacen)
    {
        $this->autorizarTienda($almacen->tienda_id);
        if (
            MovimientoInventario::where('almacen_id', $almacen->id)->exists()
            || Inventario::where('almacen_id', $almacen->id)->where('cantidad', '>', 0)->exists()
        ) {
            return back()->with('error', 'El almacén tiene stock o movimientos; desactívalo en lugar de eliminarlo.');
        }
        $almacen->delete();

        return redirect()->route('almacenes.index')->with('success', 'Almacén eliminado.');
    }

    private function encargados()
    {
        return User::where('empresa_id', $this->empresaId())->orderBy('usuario_nombres')->get()->pluck('nombre_completo', 'id');
    }

    /** ALM-01, ALM-02... por tienda. */
    private function codigoSiguiente(int $tiendaId): string
    {
        $n = Almacen::withTrashed()->where('tienda_id', $tiendaId)->count() + 1;
        do {
            $codigo = sprintf('ALM-%02d', $n++);
        } while (Almacen::withTrashed()->where('tienda_id', $tiendaId)->where('codigo', $codigo)->exists());

        return $codigo;
    }

    private function validar(Request $request, ?Almacen $almacen = null): array
    {
        $data = $request->validate([
            'tienda_id' => ['required', Rule::in(TiendaContext::ids())],
            'codigo' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('almacenes', 'codigo')->where('tienda_id', $request->input('tienda_id'))->ignore($almacen?->id)
            ],
            'nombre' => ['required', 'string', 'max:120'],
            'tipo' => ['required', Rule::in(array_keys(Almacen::TIPOS))],
            'encargado_id' => ['nullable', Rule::exists('users', 'id')->where('empresa_id', $this->empresaId())],
            'ubicaciones' => ['nullable', 'string', 'max:1000'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', 'in:0,1'],
        ]);
        // Un almacén de merma nunca es el predeterminado de ventas.
        $data['es_principal'] = $request->boolean('es_principal') && $data['tipo'] !== 'merma';

        return $data;
    }
}
