<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Tienda;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Asistente "Para dejarla lista" de una tienda nueva. Reutiliza lo que ya existe:
 * almacén principal (se crea con la tienda), usuarios por tienda, cajas, productos de la empresa e inventario.
 */
class TiendaConfiguracionController extends Controller
{
    use UsaContexto;

    public function show(Tienda $tienda)
    {
        $this->autorizarEmpresa($tienda);

        $almacenes = $tienda->almacenes()->get();
        $principal = $almacenes->firstWhere('es_principal', true) ?? $almacenes->first();
        $admins = $tienda->usuarios()->role('administrador_tienda')->get();
        $cajas = Caja::where('tienda_id', $tienda->id)->get();
        $totalProductos = Producto::where('empresa_id', $tienda->empresa_id)->where('estado', 1)->count();
        $conStock = Inventario::whereIn('almacen_id', $almacenes->pluck('id'))->where('cantidad', '>', 0)->exists();

        $pasos = [
            ['n' => 1, 'titulo' => 'Datos de la tienda', 'listo' => true],
            ['n' => 2, 'titulo' => 'Crear almacén principal', 'listo' => (bool) $principal],
            ['n' => 3, 'titulo' => 'Crear administrador de la tienda', 'listo' => $admins->isNotEmpty()],
            ['n' => 4, 'titulo' => 'Crear caja', 'listo' => $cajas->isNotEmpty()],
            ['n' => 5, 'titulo' => 'Cargar productos', 'listo' => $totalProductos > 0],
            ['n' => 6, 'titulo' => 'Cargar stock inicial', 'listo' => $conStock],
        ];
        $actual = collect($pasos)->firstWhere('listo', false)['n'] ?? null;

        $usuarios = User::where('empresa_id', $tienda->empresa_id)->orderBy('usuario_nombres')->get()->pluck('nombre_completo', 'id');
        $codigoCaja = sprintf('CAJA-%02d', $cajas->count() + 1);
        $breadcrumb = $this->breadcrumb(['Tiendas' => route('tiendas.index'), $tienda->nombre => null, 'Configuración' => null]);

        return view('ventas.tiendas.configuracion', compact('tienda', 'pasos', 'actual', 'principal', 'admins', 'cajas', 'totalProductos', 'usuarios', 'codigoCaja', 'breadcrumb'));
    }

    public function administrador(Request $request, Tienda $tienda)
    {
        $this->autorizarEmpresa($tienda);

        if ($request->input('modo') === 'existente') {
            $data = $request->validate([
                'user_id' => ['required', Rule::exists('users', 'id')->where('empresa_id', $tienda->empresa_id)],
            ]);
            $usuario = User::findOrFail($data['user_id']);
        } else {
            $data = $request->validate([
                'usuario_nombres' => ['required', 'string', 'max:120'],
                'usuario_app' => ['required', 'string', 'max:80'],
                'usuario_apm' => ['nullable', 'string', 'max:80'],
                'email' => ['required', 'email', 'max:150', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);
            // Si tu tabla users exige otras columnas (usuario, estado...), agrégalas aquí.
            $usuario = (new User())->forceFill([
                'usuario_nombres' => $data['usuario_nombres'],
                'usuario_app' => $data['usuario_app'],
                'usuario_apm' => $data['usuario_apm'] ?? null,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'empresa_id' => $tienda->empresa_id,
            ]);
        }

        DB::transaction(function () use ($usuario, $tienda) {
            $usuario->empresa_id = $usuario->empresa_id ?: $tienda->empresa_id;
            $usuario->tienda_principal_id = $usuario->tienda_principal_id ?: $tienda->id;
            $usuario->save();
            $usuario->tiendas()->syncWithoutDetaching([$tienda->id => ['estado' => 1]]);
            if (! $usuario->hasAnyRole(['admin', 'administrador_general', 'administrador_tienda'])) {
                $usuario->assignRole('administrador_tienda');
            }
        });

        return redirect()->route('tiendas.configuracion', $tienda)->with('success', 'Administrador asignado a la tienda.');
    }

    public function caja(Request $request, Tienda $tienda)
    {
        $this->autorizarEmpresa($tienda);
        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:20', Rule::unique('cajas', 'codigo')->where('tienda_id', $tienda->id)],
            'nombre' => ['required', 'string', 'max:120'],
        ]);
        Caja::create($data + ['tienda_id' => $tienda->id, 'estado' => 1]);

        return redirect()->route('tiendas.configuracion', $tienda)->with('success', 'Caja creada.');
    }
}
