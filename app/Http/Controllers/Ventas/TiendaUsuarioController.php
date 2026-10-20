<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Controller;
use App\Models\Tienda;
use App\Models\User;
use Illuminate\Http\Request;

class TiendaUsuarioController extends Controller
{
    use UsaContexto;

    public function edit(Tienda $tienda)
    {
        $this->autorizarEmpresa($tienda);

        $usuarios = User::with('roles')
            ->where('empresa_id', $this->empresaId())
            ->orderBy('usuario_nombres')
            ->get();

        $asignados = $tienda->usuarios()->wherePivot('estado', 1)->get()->pluck('id')->all();

        $breadcrumb = $this->breadcrumb([
            'Tiendas' => route('tiendas.index'),
            'Usuarios de ' . $tienda->nombre => null,
        ]);

        return view('ventas.tiendas.usuarios', compact('tienda', 'usuarios', 'asignados', 'breadcrumb'));
    }

    public function update(Request $request, Tienda $tienda)
    {
        $this->autorizarEmpresa($tienda);

        $ids = User::where('empresa_id', $this->empresaId())
            ->whereIn('id', (array) $request->input('usuarios', []))
            ->get()
            ->pluck('id');

        $tienda->usuarios()->sync($ids->mapWithKeys(fn ($id) => [$id => ['estado' => 1]])->all());

        return redirect()->route('tiendas.index')->with('success', 'Usuarios de la tienda actualizados.');
    }
}
