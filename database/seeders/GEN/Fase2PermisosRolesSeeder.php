<?php

namespace Database\Seeders\GEN;

use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\Traits\RunsOnce;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permisos y roles de la fase 2. Autosuficiente: crea los permisos que falten
 * (también los de la fase 1 que se asignan a "cajero") antes de asignarlos.
 * Cambia el namespace si tus seeders viven en otra carpeta (VEN, GEN...).
 */
class Fase2PermisosRolesSeeder extends Seeder
{
    use RunsOnce;

    private const PERMISOS = [
        'panel' => ['ver'],
        'cajas' => ['ver', 'crear', 'editar', 'eliminar'],
        'caja' => ['ver', 'abrir', 'cerrar', 'movimientos', 'ver_todas'],
        'cotizaciones' => ['ver', 'crear', 'editar', 'eliminar'],
        'ventas' => ['ver', 'crear', 'anular', 'devolver', 'ver_todas'],
        'ordenes_compra' => ['ver', 'crear', 'anular'],
        'compras' => ['ver', 'crear', 'anular', 'devolver'],
        'cxc' => ['ver', 'cobrar'],
        'cxp' => ['ver', 'pagar'],
        'transferencias' => ['ver', 'crear', 'enviar', 'recibir', 'anular'],
        'reportes' => ['ventas', 'inventario', 'compras', 'cuentas', 'caja', 'globales', 'auditoria'],
        'automatizaciones' => ['ver', 'editar', 'ejecutar'],
        'correos' => ['ver', 'reenviar'],
    ];

    protected function handle()
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        $todos = [];
        foreach (self::PERMISOS as $modulo => $acciones) {
            foreach ($acciones as $accion) {
                $todos[] = "{$modulo}.{$accion}";
            }
        }

        $roles = [
            'admin' => $todos,

            'administrador_general' => $todos,

            'administrador_tienda' => array_merge(
                ['panel.ver', 'cajas.ver', 'cajas.crear', 'cajas.editar',
                 'caja.ver', 'caja.abrir', 'caja.cerrar', 'caja.movimientos', 'caja.ver_todas',
                 'cotizaciones.ver', 'cotizaciones.crear', 'cotizaciones.editar', 'cotizaciones.eliminar',
                 'ventas.ver', 'ventas.crear', 'ventas.anular', 'ventas.devolver', 'ventas.ver_todas',
                 'ordenes_compra.ver', 'ordenes_compra.crear', 'ordenes_compra.anular',
                 'compras.ver', 'compras.crear', 'compras.anular', 'compras.devolver',
                 'cxc.ver', 'cxc.cobrar', 'cxp.ver', 'cxp.pagar',
                 'transferencias.ver', 'transferencias.crear', 'transferencias.enviar', 'transferencias.recibir', 'transferencias.anular',
                 'reportes.ventas', 'reportes.inventario', 'reportes.compras', 'reportes.cuentas', 'reportes.caja',
                 'automatizaciones.ver', 'correos.ver']
            ),

            'almacenero' => [
                'panel.ver', 'transferencias.ver', 'transferencias.crear', 'transferencias.enviar', 'transferencias.recibir',
                'compras.ver', 'compras.crear', 'ordenes_compra.ver', 'ordenes_compra.crear', 'reportes.inventario',
            ],

            'vendedor' => [
                'panel.ver', 'cotizaciones.ver', 'cotizaciones.crear', 'cotizaciones.editar',
                'ventas.ver', 'ventas.crear', 'cxc.ver',
            ],

            // Rol nuevo: cobra y opera caja. Incluye permisos de la fase 1 que necesita para vender.
            'cajero' => [
                'panel.ver', 'caja.ver', 'caja.abrir', 'caja.cerrar', 'caja.movimientos',
                'ventas.ver', 'ventas.crear', 'cxc.ver', 'cxc.cobrar', 'reportes.caja',
                'productos.ver', 'inventario.ver', 'clientes.ver', 'clientes.crear', 'clientes.editar',
            ],
        ];

        // Crear todo permiso que se vaya a asignar y aún no exista
        $necesarios = collect($roles)->flatten()->unique();
        foreach ($necesarios as $nombre) {
            Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web'], ['dinamico' => 0]);
        }
        $registrar->forgetCachedPermissions();

        foreach ($roles as $nombre => $permisos) {
            Role::findOrCreate($nombre, 'web')->givePermissionTo($permisos);
        }

        $registrar->forgetCachedPermissions();
    }
}
