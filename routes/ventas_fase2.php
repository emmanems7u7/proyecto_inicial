<?php

use App\Http\Controllers\Ventas\AutomatizacionController;
use App\Http\Controllers\Ventas\CajaController;
use App\Http\Controllers\Ventas\CajaSesionController;
use App\Http\Controllers\Ventas\CompraController;
use App\Http\Controllers\Ventas\CotizacionController;
use App\Http\Controllers\Ventas\CuentaPorCobrarController;
use App\Http\Controllers\Ventas\CuentaPorPagarController;
use App\Http\Controllers\Ventas\OrdenCompraController;
use App\Http\Controllers\Ventas\PanelController;
use App\Http\Controllers\Ventas\ReporteController;
use App\Http\Controllers\Ventas\TransferenciaController;
use App\Http\Controllers\Ventas\VentaController;
use App\Http\Middleware\ContextoTienda;
use Illuminate\Support\Facades\Route;

/*
 * Sistema de ventas - Fase 2 (caja, ventas, compras, cuentas, transferencias, reportes, panel, automatizaciones).
 * Agregar en routes/web.php (después de ventas.php):   require __DIR__ . '/ventas_fase2.php';
 */
Route::middleware(['auth', ContextoTienda::class])->group(function () {

    $crud = function (string $uri, string $nombre, string $controlador, string $param, string $permiso) {
        Route::get($uri, [$controlador, 'index'])->name("{$nombre}.index")->middleware("can:{$permiso}.ver");
        Route::get($uri . '/crear', [$controlador, 'create'])->name("{$nombre}.create")->middleware("can:{$permiso}.crear");
        Route::post($uri, [$controlador, 'store'])->name("{$nombre}.store")->middleware("can:{$permiso}.crear");
        Route::get($uri . '/{' . $param . '}/editar', [$controlador, 'edit'])->name("{$nombre}.edit")->middleware("can:{$permiso}.editar");
        Route::put($uri . '/{' . $param . '}', [$controlador, 'update'])->name("{$nombre}.update")->middleware("can:{$permiso}.editar");
        Route::delete($uri . '/{' . $param . '}', [$controlador, 'destroy'])->name("{$nombre}.destroy")->middleware("can:{$permiso}.eliminar");
    };

    // Panel de control
    Route::get('panel', [PanelController::class, 'index'])->name('panel.index')->middleware('can:panel.ver');

    // Cajas (configuración) y sesiones de caja
    $crud('cajas', 'cajas', CajaController::class, 'caja', 'cajas');

    Route::prefix('caja')->name('caja.')->group(function () {
        Route::get('/', [CajaSesionController::class, 'index'])->name('sesiones.index')->middleware('can:caja.ver');
        Route::get('abrir', [CajaSesionController::class, 'abrirCreate'])->name('abrir.create')->middleware('can:caja.abrir');
        Route::post('abrir', [CajaSesionController::class, 'abrirStore'])->name('abrir.store')->middleware('can:caja.abrir');
        Route::get('sesiones/{sesion}', [CajaSesionController::class, 'show'])->name('sesiones.show')->middleware('can:caja.ver');
        Route::get('sesiones/{sesion}/cerrar', [CajaSesionController::class, 'cerrarCreate'])->name('cerrar.create')->middleware('can:caja.cerrar');
        Route::post('sesiones/{sesion}/cerrar', [CajaSesionController::class, 'cerrarStore'])->name('cerrar.store')->middleware('can:caja.cerrar');
        Route::get('sesiones/{sesion}/movimiento', [CajaSesionController::class, 'movimientoCreate'])->name('movimiento.create')->middleware('can:caja.movimientos');
        Route::post('sesiones/{sesion}/movimiento', [CajaSesionController::class, 'movimientoStore'])->name('movimiento.store')->middleware('can:caja.movimientos');
    });

    // Cotizaciones
    $crud('cotizaciones', 'cotizaciones', CotizacionController::class, 'cotizacion', 'cotizaciones');
    Route::get('cotizaciones/{cotizacion}', [CotizacionController::class, 'show'])->name('cotizaciones.show')->middleware('can:cotizaciones.ver');
    Route::get('cotizaciones/{cotizacion}/imprimir', [CotizacionController::class, 'imprimir'])->name('cotizaciones.imprimir')->middleware('can:cotizaciones.ver');
    Route::post('cotizaciones/{cotizacion}/estado', [CotizacionController::class, 'cambiarEstado'])->name('cotizaciones.estado')->middleware('can:cotizaciones.editar');

    // Ventas
    Route::get('ventas', [VentaController::class, 'index'])->name('ventas.index')->middleware('can:ventas.ver');
    Route::get('ventas/crear', [VentaController::class, 'create'])->name('ventas.create')->middleware('can:ventas.crear');
    Route::post('ventas', [VentaController::class, 'store'])->name('ventas.store')->middleware('can:ventas.crear');
    Route::get('ventas/{venta}', [VentaController::class, 'show'])->name('ventas.show')->middleware('can:ventas.ver');
    Route::get('ventas/{venta}/imprimir', [VentaController::class, 'imprimir'])->name('ventas.imprimir')->middleware('can:ventas.ver');
    Route::post('ventas/{venta}/confirmar', [VentaController::class, 'confirmar'])->name('ventas.confirmar')->middleware('can:ventas.crear');
    Route::post('ventas/{venta}/anular', [VentaController::class, 'anular'])->name('ventas.anular')->middleware('can:ventas.anular');
    Route::get('ventas/{venta}/devolucion', [VentaController::class, 'devolucionCreate'])->name('ventas.devolucion.create')->middleware('can:ventas.devolver');
    Route::post('ventas/{venta}/devolucion', [VentaController::class, 'devolucionStore'])->name('ventas.devolucion.store')->middleware('can:ventas.devolver');

    // Órdenes de compra
    Route::get('ordenes-compra', [OrdenCompraController::class, 'index'])->name('ordenes_compra.index')->middleware('can:ordenes_compra.ver');
    Route::get('ordenes-compra/crear', [OrdenCompraController::class, 'create'])->name('ordenes_compra.create')->middleware('can:ordenes_compra.crear');
    Route::post('ordenes-compra', [OrdenCompraController::class, 'store'])->name('ordenes_compra.store')->middleware('can:ordenes_compra.crear');
    Route::get('ordenes-compra/{orden}', [OrdenCompraController::class, 'show'])->name('ordenes_compra.show')->middleware('can:ordenes_compra.ver');
    Route::post('ordenes-compra/{orden}/anular', [OrdenCompraController::class, 'anular'])->name('ordenes_compra.anular')->middleware('can:ordenes_compra.anular');

    // Compras
    Route::get('compras', [CompraController::class, 'index'])->name('compras.index')->middleware('can:compras.ver');
    Route::get('compras/crear', [CompraController::class, 'create'])->name('compras.create')->middleware('can:compras.crear');
    Route::post('compras', [CompraController::class, 'store'])->name('compras.store')->middleware('can:compras.crear');
    Route::get('compras/{compra}', [CompraController::class, 'show'])->name('compras.show')->middleware('can:compras.ver');
    Route::post('compras/{compra}/anular', [CompraController::class, 'anular'])->name('compras.anular')->middleware('can:compras.anular');
    Route::get('compras/{compra}/devolucion', [CompraController::class, 'devolucionCreate'])->name('compras.devolucion.create')->middleware('can:compras.devolver');
    Route::post('compras/{compra}/devolucion', [CompraController::class, 'devolucionStore'])->name('compras.devolucion.store')->middleware('can:compras.devolver');

    // Cuentas por cobrar / por pagar
    Route::get('cxc', [CuentaPorCobrarController::class, 'index'])->name('cxc.index')->middleware('can:cxc.ver');
    Route::get('cxc/cliente/{cliente}/estado-cuenta', [CuentaPorCobrarController::class, 'estadoCuenta'])->name('cxc.cliente')->middleware('can:cxc.ver');
    Route::get('cxc/{cuenta}', [CuentaPorCobrarController::class, 'show'])->name('cxc.show')->middleware('can:cxc.ver');
    Route::post('cxc/{cuenta}/pagos', [CuentaPorCobrarController::class, 'pagoStore'])->name('cxc.pago.store')->middleware('can:cxc.cobrar');

    Route::get('cxp', [CuentaPorPagarController::class, 'index'])->name('cxp.index')->middleware('can:cxp.ver');
    Route::get('cxp/proveedor/{proveedor}/estado-cuenta', [CuentaPorPagarController::class, 'estadoCuenta'])->name('cxp.proveedor')->middleware('can:cxp.ver');
    Route::get('cxp/{cuenta}', [CuentaPorPagarController::class, 'show'])->name('cxp.show')->middleware('can:cxp.ver');
    Route::post('cxp/{cuenta}/pagos', [CuentaPorPagarController::class, 'pagoStore'])->name('cxp.pago.store')->middleware('can:cxp.pagar');

    // Transferencias
    Route::get('transferencias', [TransferenciaController::class, 'index'])->name('transferencias.index')->middleware('can:transferencias.ver');
    Route::get('transferencias/crear', [TransferenciaController::class, 'create'])->name('transferencias.create')->middleware('can:transferencias.crear');
    Route::post('transferencias', [TransferenciaController::class, 'store'])->name('transferencias.store')->middleware('can:transferencias.crear');
    Route::get('transferencias/{transferencia}', [TransferenciaController::class, 'show'])->name('transferencias.show')->middleware('can:transferencias.ver');
    Route::post('transferencias/{transferencia}/enviar', [TransferenciaController::class, 'enviar'])->name('transferencias.enviar')->middleware('can:transferencias.enviar');
    Route::post('transferencias/{transferencia}/recibir', [TransferenciaController::class, 'recibir'])->name('transferencias.recibir')->middleware('can:transferencias.recibir');
    Route::post('transferencias/{transferencia}/anular', [TransferenciaController::class, 'anular'])->name('transferencias.anular')->middleware('can:transferencias.anular');

    // Reportes (?export=csv descarga en formato Excel/CSV)
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('ventas', [ReporteController::class, 'ventas'])->name('ventas')->middleware('can:reportes.ventas');
        Route::get('inventario', [ReporteController::class, 'inventario'])->name('inventario')->middleware('can:reportes.inventario');
        Route::get('compras', [ReporteController::class, 'compras'])->name('compras')->middleware('can:reportes.compras');
        Route::get('cuentas', [ReporteController::class, 'cuentas'])->name('cuentas')->middleware('can:reportes.cuentas');
        Route::get('caja', [ReporteController::class, 'caja'])->name('caja')->middleware('can:reportes.caja');
        Route::get('globales', [ReporteController::class, 'globales'])->name('globales')->middleware('can:reportes.globales');
        Route::get('auditoria', [ReporteController::class, 'auditoria'])->name('auditoria')->middleware('can:reportes.auditoria');
        Route::get('accesos', [ReporteController::class, 'accesos'])->name('accesos')->middleware('can:reportes.auditoria');
    });

    // Automatizaciones y correos
    Route::get('automatizaciones', [AutomatizacionController::class, 'index'])->name('automatizaciones.index')->middleware('can:automatizaciones.ver');
    Route::get('automatizaciones/historial', [AutomatizacionController::class, 'logs'])->name('automatizaciones.logs')->middleware('can:automatizaciones.ver');
    Route::get('automatizaciones/{automatizacion}/configurar', [AutomatizacionController::class, 'edit'])->name('automatizaciones.edit')->middleware('can:automatizaciones.editar');
    Route::put('automatizaciones/{automatizacion}', [AutomatizacionController::class, 'update'])->name('automatizaciones.update')->middleware('can:automatizaciones.editar');
    Route::post('automatizaciones/{automatizacion}/ejecutar', [AutomatizacionController::class, 'ejecutar'])->name('automatizaciones.ejecutar')->middleware('can:automatizaciones.ejecutar');

    Route::get('correos', [AutomatizacionController::class, 'correos'])->name('correos.index')->middleware('can:correos.ver');
    Route::post('correos/enviar', [AutomatizacionController::class, 'correosEnviar'])->name('correos.enviar')->middleware('can:correos.reenviar');
    Route::post('correos/{correo}/reintentar', [AutomatizacionController::class, 'correoReintentar'])->name('correos.reintentar')->middleware('can:correos.reenviar');
});
