<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Support\Catalogos;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmpresaController extends Controller
{
    use UsaContexto;

    public function edit()
    {
        $empresa = Empresa::findOrFail($this->empresaId());
        $ciudades = Catalogos::ciudades();
        $breadcrumb = $this->breadcrumb(['Mi empresa' => route('empresa.edit')]);

        return view('ventas.empresa.edit', compact('empresa', 'ciudades', 'breadcrumb'));
    }

    public function update(Request $request)
    {
        $empresa = Empresa::findOrFail($this->empresaId());

        $data = $request->validate([
            'razon_social' => ['required', 'string', 'max:150'],
            'nombre_comercial' => ['nullable', 'string', 'max:150'],
            'nit' => ['nullable', 'string', 'max:30', Rule::unique('empresas', 'nit')->ignore($empresa->id)],
            'email' => ['nullable', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad_id' => ['nullable', 'exists:catalogos,id'],
        ]);

        $empresa->update($data);

        return redirect()->route('empresa.edit')->with('status', 'Datos de la empresa actualizados.');
    }
}
