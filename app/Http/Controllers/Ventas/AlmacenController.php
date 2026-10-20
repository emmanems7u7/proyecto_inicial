<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\Inventario;
use App\Models\MovimientoInventario;
use App\Support\TiendaContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AlmacenController extends Controller
{
    use UsaContexto;

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $tiendas = TiendaContext::permitidas()->pluck('nombre', 'id');

        $almacenes = Almacen::with('tienda')
            ->withCount('inventarios')
            ->whereIn('tienda_id', TiendaContext::ids())
            ->when($request->filled('tienda_id'), fn ($query) => $query->where('tienda_id', $request->input('tienda_id')))
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('nombre', 'like', "%{$q}%")->orWhere('codigo', 'like', "%{$q}%");
            }))
            ->orderBy('tienda_id')
            ->orderByDesc('es_principal')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        $breadcrumb = $this->breadcrumb(['Almacenes' => route('almacenes.index')]);

        return view('ventas.almacenes.index', compact('almacenes', 'tiendas', 'q', 'breadcrumb'));
    }

    public function create()
    {
        $almacen = new Almacen(['estado' => 1, 'tienda_id' => TiendaContext::actual()?->id]);
        $tiendas = TiendaContext::permitidas()->pluck('nombre', 'id');
        $breadcrumb = $this->breadcrumb(['Almacenes' => route('almacenes.index'), 'Nuevo' => null]);

        return view('ventas.almacenes.create', compact('almacen', 'tiendas', 'breadcrumb'));
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);

        DB::transaction(function () use ($data) {
            if ($data['es_principal']) {
                Almacen::where('tienda_id', $data['tienda_id'])->update(['es_principal' => false]);
            }
            Almacen::create($data);
        });

        return redirect()->route('almacenes.index')->with('success', 'Almacén creado.');
    }

    public function edit(Almacen $almacen)
    {
        $this->autorizarTienda($almacen->tienda_id);
        $tiendas = TiendaContext::permitidas()->pluck('nombre', 'id');
        $breadcrumb = $this->breadcrumb(['Almacenes' => route('almacenes.index'), 'Editar' => null]);

        return view('ventas.almacenes.edit', compact('almacen', 'tiendas', 'breadcrumb'));
    }

    public function update(Request $request, Almacen $almacen)
    {
        $this->autorizarTienda($almacen->tienda_id);
        $data = $this->validar($request, $almacen);

        if ((int) $data['tienda_id'] !== (int) $almacen->tienda_id
            && MovimientoInventario::where('almacen_id', $almacen->id)->exists()) {
            return back()->withInput()->with('error', 'No puedes mover de tienda un almacén que ya tiene movimientos.');
        }

        DB::transaction(function () use ($data, $almacen) {
            if ($data['es_principal']) {
                Almacen::where('tienda_id', $data['tienda_id'])->where('id', '!=', $almacen->id)->update(['es_principal' => false]);
            }
            $almacen->update($data);
        });

        return redirect()->route('almacenes.index')->with('success', 'Almacén actualizado.');
    }

    public function destroy(Almacen $almacen)
    {
        $this->autorizarTienda($almacen->tienda_id);

        if (MovimientoInventario::where('almacen_id', $almacen->id)->exists()
            || Inventario::where('almacen_id', $almacen->id)->where('cantidad', '>', 0)->exists()) {
            return back()->with('error', 'El almacén tiene stock o movimientos; desactívalo en lugar de eliminarlo.');
        }

        $almacen->delete();

        return redirect()->route('almacenes.index')->with('success', 'Almacén eliminado.');
    }

    private function validar(Request $request, ?Almacen $almacen = null): array
    {
        $data = $request->validate([
            'tienda_id' => ['required', Rule::in(TiendaContext::ids())],
            'codigo' => ['required', 'string', 'max:20',
                Rule::unique('almacenes', 'codigo')->where('tienda_id', $request->input('tienda_id'))->ignore($almacen?->id)],
            'nombre' => ['required', 'string', 'max:120'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', 'in:0,1'],
        ]);
        $data['es_principal'] = $request->boolean('es_principal');

        return $data;
    }
}
