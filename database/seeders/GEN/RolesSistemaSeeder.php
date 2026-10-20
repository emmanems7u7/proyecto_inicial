<?php

namespace Database\Seeders\GEN;


use App\Models\Role;
use Database\Seeders\Traits\RunsOnce;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Roles del diagrama (fase 1). El rol máximo es "admin".
 * Los permisos se AGREGAN (givePermissionTo), nunca se quitan los que ya tengan.
 */
class RolesSistemaSeeder extends Seeder
{
    use RunsOnce;

    protected function handle()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $todos = PermisosSistemaSeeder::nombres();
        $crud = fn(string $m) => ["{$m}.ver", "{$m}.crear", "{$m}.editar", "{$m}.eliminar"];

        $roles = [
            // Súper administrador: todo el sistema
            'admin' => $todos,

            // Administrador general: todas las tiendas, sin tocar datos de empresa ni eliminar tiendas
            'administrador_general' => array_values(array_diff($todos, ['empresa.editar', 'tiendas.eliminar'])),

            // Administrador de tienda: gestión completa de su tienda
            'administrador_tienda' => array_merge(
                [
                    'almacenes.ver',
                    'categorias_producto.ver',
                    'categorias_producto.crear',
                    'categorias_producto.editar',
                    'productos.ver',
                    'productos.crear',
                    'productos.editar',
                    'inventario.ver',
                    'inventario.movimientos',
                    'inventario.configurar',
                    'kardex.ver',
                    'lotes.ver'
                ],
                $crud('clientes'),
                $crud('proveedores')
            ),

            // Almacenero: stock y movimientos
            'almacenero' => [
                'almacenes.ver',
                'categorias_producto.ver',
                'productos.ver',
                'proveedores.ver',
                'inventario.ver',
                'inventario.movimientos',
                'kardex.ver',
                'lotes.ver',
            ],

            // Vendedor: consulta de productos/stock y clientes
            'vendedor' => [
                'productos.ver',
                'inventario.ver',
                'clientes.ver',
                'clientes.crear',
                'clientes.editar',
            ],
        ];

        foreach ($roles as $nombre => $permisos) {
            $rol = Role::findOrCreate($nombre, 'web');
            $rol->givePermissionTo($permisos);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
