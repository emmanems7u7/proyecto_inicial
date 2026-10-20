<?php

namespace Database\Seeders\GEN;


use App\Models\Permission;
use Database\Seeders\Traits\RunsOnce;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permisos (no dinámicos) de la fase 1. Formato: modulo.accion
 * Son los que usan las rutas (middleware can:) y el menú.
 */

class PermisosSistemaSeeder extends Seeder
{
    use RunsOnce;

    public const PERMISOS = [
        'empresa' => ['ver', 'editar'],
        'tiendas' => ['ver', 'crear', 'editar', 'eliminar', 'usuarios'],
        'almacenes' => ['ver', 'crear', 'editar', 'eliminar'],
        'categorias_producto' => ['ver', 'crear', 'editar', 'eliminar'],
        'productos' => ['ver', 'crear', 'editar', 'eliminar'],
        'clientes' => ['ver', 'crear', 'editar', 'eliminar'],
        'proveedores' => ['ver', 'crear', 'editar', 'eliminar'],
        'inventario' => ['ver', 'movimientos', 'configurar'],
        'kardex' => ['ver'],
        'lotes' => ['ver'],
    ];

    public static function nombres(): array
    {
        $nombres = [];
        foreach (self::PERMISOS as $modulo => $acciones) {
            foreach ($acciones as $accion) {
                $nombres[] = "{$modulo}.{$accion}";
            }
        }

        return $nombres;
    }

    protected function handle()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::nombres() as $nombre) {
            Permission::updateOrCreate(
                [
                    'name' => $nombre,
                    'guard_name' => 'web',
                ],
                [
                    'tipo' => 'permiso',
                    'id_relacion' => null,
                    'dinamico' => 0,
                ]
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
