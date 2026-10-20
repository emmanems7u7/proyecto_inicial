<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use App\Support\Catalogos;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProveedorController extends Controller
{
    use UsaContexto;

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $proveedores = Proveedor::with('tipoDocumento')
            ->where('empresa_id', $this->empresaId())
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('nombre', 'like', "%{$q}%")
                    ->orWhere('numero_documento', 'like', "%{$q}%")
                    ->orWhere('telefono', 'like', "%{$q}%");
            }))
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', (int) $request->input('estado')))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        $breadcrumb = $this->breadcrumb(['Proveedores' => route('proveedores.index')]);

        return view('ventas.proveedores.index', compact('proveedores', 'q', 'breadcrumb'));
    }

    public function create()
    {
        $proveedor = new Proveedor(['estado' => 1, 'dias_credito' => 0]);
        $tiposDocumento = Catalogos::tiposDocumento();
        $ciudades = Catalogos::ciudades();
        $breadcrumb = $this->breadcrumb(['Proveedores' => route('proveedores.index'), 'Nuevo' => null]);

        return view('ventas.proveedores.create', compact('proveedor', 'tiposDocumento', 'ciudades', 'breadcrumb'));
    }

    public function store(Request $request)
    {
        Proveedor::create($this->validar($request) + ['empresa_id' => $this->empresaId()]);

        return redirect()->route('proveedores.index')->with('success', 'Registro creado.');
    }

    public function edit(Proveedor $proveedor)
    {
        $this->autorizarEmpresa($proveedor);
        $tiposDocumento = Catalogos::tiposDocumento();
        $ciudades = Catalogos::ciudades();
        $breadcrumb = $this->breadcrumb(['Proveedores' => route('proveedores.index'), 'Editar' => null]);

        return view('ventas.proveedores.edit', compact('proveedor', 'tiposDocumento', 'ciudades', 'breadcrumb'));
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $this->autorizarEmpresa($proveedor);
        $proveedor->update($this->validar($request, $proveedor));

        return redirect()->route('proveedores.index')->with('success', 'Registro actualizado.');
    }

    public function destroy(Proveedor $proveedor)
    {
        $this->autorizarEmpresa($proveedor);

        // Fase 2: bloquear si tiene ventas/compras o saldos pendientes (CxC / CxP).
        $proveedor->delete();

        return redirect()->route('proveedores.index')->with('success', 'Registro eliminado.');
    }

    private function validar(Request $request, ?Proveedor $proveedor = null): array
    {
        return $request->validate([
            'tipo_documento_id' => ['nullable', 'exists:catalogos,id'],
            'numero_documento' => ['nullable', 'string', 'max:30',
                Rule::unique('proveedores', 'numero_documento')
                    ->where('empresa_id', $this->empresaId())
                    ->ignore($proveedor?->id)],
            'nombre' => ['required', 'string', 'max:180'],
            'email' => ['nullable', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad_id' => ['nullable', 'exists:catalogos,id'],
            'contacto_nombre' => ['nullable', 'string', 'max:120'],
            'dias_credito' => ['required', 'integer', 'min:0', 'max:365'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'estado' => ['required', 'in:0,1'],
        ]);
    }
}
