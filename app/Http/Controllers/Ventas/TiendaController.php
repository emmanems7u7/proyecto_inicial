<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\MovimientoInventario;
use App\Models\Tienda;
use App\Support\Catalogos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TiendaController extends Controller
{
    use UsaContexto;

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $tiendas = Tienda::with('ciudad')
            ->withCount(['almacenes', 'usuarios'])
            ->where('empresa_id', $this->empresaId())
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('nombre', 'like', "%{$q}%")->orWhere('codigo', 'like', "%{$q}%");
            }))
            ->orderByDesc('es_matriz')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        $breadcrumb = $this->breadcrumb(['Tiendas' => route('tiendas.index')]);

        return view('ventas.tiendas.index', compact('tiendas', 'q', 'breadcrumb'));
    }

    public function create()
    {
        $tienda = new Tienda(['estado' => 1]);
        $ciudades = Catalogos::ciudades();
        $breadcrumb = $this->breadcrumb(['Tiendas' => route('tiendas.index'), 'Nueva' => null]);

        return view('ventas.tiendas.create', compact('tienda', 'ciudades', 'breadcrumb'));
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        $empresaId = $this->empresaId();

        DB::transaction(function () use ($data, $empresaId) {
            if ($data['es_matriz']) {
                Tienda::where('empresa_id', $empresaId)->update(['es_matriz' => false]);
            }

            $tienda = Tienda::create($data + ['empresa_id' => $empresaId]);

            // Toda tienda nace con su almacén principal.
            Almacen::create([
                'tienda_id' => $tienda->id,
                'codigo' => 'ALM-01',
                'nombre' => 'Almacén principal',
                'es_principal' => true,
                'estado' => 1,
            ]);
        });

        return redirect()->route('tiendas.index')->with('success', 'Tienda creada con su almacén principal.');
    }

    public function edit(Tienda $tienda)
    {
        $this->autorizarEmpresa($tienda);
        $ciudades = Catalogos::ciudades();
        $breadcrumb = $this->breadcrumb(['Tiendas' => route('tiendas.index'), 'Editar' => null]);

        return view('ventas.tiendas.edit', compact('tienda', 'ciudades', 'breadcrumb'));
    }

    public function update(Request $request, Tienda $tienda)
    {
        $this->autorizarEmpresa($tienda);
        $data = $this->validar($request, $tienda);

        DB::transaction(function () use ($data, $tienda) {
            if ($data['es_matriz']) {
                Tienda::where('empresa_id', $tienda->empresa_id)->where('id', '!=', $tienda->id)->update(['es_matriz' => false]);
            }
            $tienda->update($data);
        });

        return redirect()->route('tiendas.index')->with('success', 'Tienda actualizada.');
    }

    public function destroy(Tienda $tienda)
    {
        $this->autorizarEmpresa($tienda);

        if ($tienda->es_matriz) {
            return back()->with('error', 'No puedes eliminar la tienda matriz.');
        }
        if (MovimientoInventario::where('tienda_id', $tienda->id)->exists()) {
            return back()->with('error', 'La tienda tiene movimientos de inventario; desactívala en lugar de eliminarla.');
        }

        DB::transaction(function () use ($tienda) {
            $tienda->almacenes()->delete();
            $tienda->usuarios()->detach();
            $tienda->delete();
        });

        return redirect()->route('tiendas.index')->with('success', 'Tienda eliminada.');
    }

    private function validar(Request $request, ?Tienda $tienda = null): array
    {
        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:20',
                Rule::unique('tiendas', 'codigo')->where('empresa_id', $this->empresaId())->ignore($tienda?->id)],
            'nombre' => ['required', 'string', 'max:120'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'ciudad_id' => ['nullable', 'exists:catalogos,id'],
            'estado' => ['required', 'in:0,1'],
        ]);
        $data['es_matriz'] = $request->boolean('es_matriz');

        return $data;
    }
}
