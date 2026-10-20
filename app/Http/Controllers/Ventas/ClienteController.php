<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Support\Catalogos;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    use UsaContexto;

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $clientes = Cliente::with('tipoDocumento')
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

        $breadcrumb = $this->breadcrumb(['Clientes' => route('clientes.index')]);

        return view('ventas.clientes.index', compact('clientes', 'q', 'breadcrumb'));
    }

    public function create()
    {
        $cliente = new Cliente(['estado' => 1, 'limite_credito' => 0, 'dias_credito' => 0]);
        $tiposDocumento = Catalogos::tiposDocumento();
        $ciudades = Catalogos::ciudades();
        $breadcrumb = $this->breadcrumb(['Clientes' => route('clientes.index'), 'Nuevo' => null]);

        return view('ventas.clientes.create', compact('cliente', 'tiposDocumento', 'ciudades', 'breadcrumb'));
    }

    public function store(Request $request)
    {
        Cliente::create($this->validar($request) + ['empresa_id' => $this->empresaId()]);

        return redirect()->route('clientes.index')->with('success', 'Registro creado.');
    }

    public function edit(Cliente $cliente)
    {
        $this->autorizarEmpresa($cliente);
        $tiposDocumento = Catalogos::tiposDocumento();
        $ciudades = Catalogos::ciudades();
        $breadcrumb = $this->breadcrumb(['Clientes' => route('clientes.index'), 'Editar' => null]);

        return view('ventas.clientes.edit', compact('cliente', 'tiposDocumento', 'ciudades', 'breadcrumb'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $this->autorizarEmpresa($cliente);
        $cliente->update($this->validar($request, $cliente));

        return redirect()->route('clientes.index')->with('success', 'Registro actualizado.');
    }

    public function destroy(Cliente $cliente)
    {
        $this->autorizarEmpresa($cliente);

        // Fase 2: bloquear si tiene ventas/compras o saldos pendientes (CxC / CxP).
        $cliente->delete();

        return redirect()->route('clientes.index')->with('success', 'Registro eliminado.');
    }

    private function validar(Request $request, ?Cliente $cliente = null): array
    {
        return $request->validate([
            'tipo_documento_id' => ['nullable', 'exists:catalogos,id'],
            'numero_documento' => ['nullable', 'string', 'max:30',
                Rule::unique('clientes', 'numero_documento')
                    ->where('empresa_id', $this->empresaId())->where(fn ($q) => $q->where('complemento', $request->input('complemento') ?: null))
                    ->ignore($cliente?->id)],
            'complemento' => ['nullable', 'string', 'max:5'],
            'nombre' => ['required', 'string', 'max:180'],
            'email' => ['nullable', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad_id' => ['nullable', 'exists:catalogos,id'],
            'limite_credito' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'dias_credito' => ['required', 'integer', 'min:0', 'max:365'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'estado' => ['required', 'in:0,1'],
        ]);
    }
}
