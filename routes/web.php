<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| CONTROLADORES
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\UserController;
use App\Http\Controllers\SeccionController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ConfCorreoController;
use App\Http\Controllers\CorreoController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ConfiguracionCredencialesController;
use App\Http\Controllers\ArtisanController;
use App\Http\Controllers\UserPersonalizacionController;
use App\Http\Controllers\SeederController;

use App\Http\Controllers\Ventas\AlmacenController;
use App\Http\Controllers\Ventas\CategoriaProductoController;
use App\Http\Controllers\Ventas\ClienteController;
use App\Http\Controllers\Ventas\EmpresaController;
use App\Http\Controllers\Ventas\InventarioController;
use App\Http\Controllers\Ventas\ProductoController;
use App\Http\Controllers\Ventas\ProveedorController;
use App\Http\Controllers\Ventas\TiendaActivaController;
use App\Http\Controllers\Ventas\TiendaController;
use App\Http\Controllers\Ventas\TiendaUsuarioController;
use App\Http\Middleware\ContextoTienda;


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

use App\Http\Controllers\Ventas\MisTiendasController;
use App\Http\Controllers\Ventas\TiendaConfiguracionController;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS (SIN AUTH)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/clear-cache', function () {
    Artisan::call('optimize:clear');
});

// Autenticación Laravel
Auth::routes();

/*
|--------------------------------------------------------------------------
| DOBLE FACTOR (ANTES DE ENTRAR AL SISTEMA)
|--------------------------------------------------------------------------
*/
Route::get('/2fa/verify', [TwoFactorController::class, 'index'])->name('verify.index');
Route::post('/2fa/verify', [TwoFactorController::class, 'store'])->name('verify.store');
Route::post('/2fa/resend', [TwoFactorController::class, 'resend'])->name('verify.resend');

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS POR AUTH (SISTEMA)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | HOME
    |--------------------------------------------------------------------------
    */
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');



    Route::get('/email/campos-usuario', [UserController::class, 'camposUsuario']);
    /*
    |--------------------------------------------------------------------------
    | PERSONALIZACIÓN DE USUARIO
    |--------------------------------------------------------------------------
    */
    Route::post('/guardar-color-sidebar', [UserPersonalizacionController::class, 'guardarSidebarColor']);
    Route::post('/user/personalizacion/sidebar-type', [UserPersonalizacionController::class, 'updateSidebarType']);
    Route::post('/user/preferences', [UserPersonalizacionController::class, 'updateDark']);

    /*
    |--------------------------------------------------------------------------
    | PERFIL Y CONTRASEÑA
    |--------------------------------------------------------------------------
    */
    Route::get('/usuario/contraseña', [PasswordController::class, 'ActualizarContraseña'])->name('user.actualizar.contraseña');
    Route::put('password/update', [PasswordController::class, 'update'])->name('password.actualizar');
    Route::get('/usuario/perfil', [UserController::class, 'Perfil'])->name('perfil');


    /*
    |--------------------------------------------------------------------------
    | ARTISAN (PRODUCCIÓN)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['can:ejecutar-artisan'])->group(function () {
        Route::get('/artisan-panel', [ArtisanController::class, 'verificacion'])->name('artisan.admin');
        Route::post('/artisan-panel', [ArtisanController::class, 'index'])->name('artisan.verificar');
        Route::post('/artisan/run', [ArtisanController::class, 'run'])->name('artisan.run');
    });

    /*
    |--------------------------------------------------------------------------
    | ADMINISTRACIÓN DE USUARIOS
    |--------------------------------------------------------------------------
    */
    Route::middleware(['can:Administración de Usuarios'])->group(function () {

        Route::get('/usuarios', [UserController::class, 'index'])->name('users.index')->middleware('can:usuarios.ver');
        Route::get('/usuarios/crear', [UserController::class, 'create'])->name('users.create')->middleware('can:usuarios.crear');
        Route::post('/usuarios', [UserController::class, 'store'])->name('users.store')->middleware('can:usuarios.crear');
        Route::get('/usuarios/{user}', [UserController::class, 'show'])->name('users.show')->middleware('can:usuarios.ver');
        Route::get('/usuarios/edit/{id}', [UserController::class, 'edit'])->name('users.edit')->middleware('can:usuarios.editar');
        Route::put('/usuarios/{id}/{perfil}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('can:usuarios.eliminar');
        Route::get('/datos/usuario/{id}', [UserController::class, 'GetUsuario'])->name('users.get')->middleware('can:usuarios.ver');

        Route::get('/usuarios/exportar/excel', [UserController::class, 'exportExcel'])->name('usuarios.exportar_excel')->middleware('can:usuarios.exportar_excel');
        Route::get('/usuarios/exportar/pdf', [UserController::class, 'exportPDF'])->name('usuarios.exportar_pdf')->middleware('can:usuarios.exportar_pdf');
    });

    /*
    |--------------------------------------------------------------------------
    | SECCIONES Y MENÚS (ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::resource('secciones', SeccionController::class)->except(['show'])->middleware('role:admin');
    Route::resource('menus', MenuController::class)->except(['show'])->middleware('role:admin');
    Route::post('/api/sugerir-icono', [SeccionController::class, 'SugerirIcono']);
    Route::post('obtener/dato/menu', [SeccionController::class, 'cambiarSeccion'])->middleware('role:admin');
    Route::post('/secciones/ordenar', [SeccionController::class, 'ordenar'])->name('secciones.ordenar');

    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIONES
    |--------------------------------------------------------------------------
    */
    Route::middleware(['can:Configuración'])->group(function () {
        Route::get('/configuracion/correo', [ConfCorreoController::class, 'index'])->name('configuracion.correo.index')->middleware('can:configuracion_correo.ver');
        Route::post('/configuracion/correo/guardar', [ConfCorreoController::class, 'store'])->name('configuracion.correo.store')->middleware('can:configuracion_correo.actualizar');
        Route::put('configuracion_correo', [ConfCorreoController::class, 'update'])->name('configuracion_correo.update')->middleware('can:configuracion_correo.actualizar');
        Route::get('/correo/prueba', [ConfCorreoController::class, 'enviarPrueba'])->name('correo.prueba');
    });

    Route::middleware(['role:admin', 'can:Configuración General'])->group(function () {
        Route::get('/admin/configuracion', [ConfiguracionController::class, 'edit'])->name('admin.configuracion.edit');
        Route::put('/admin/configuracion', [ConfiguracionController::class, 'update'])->name('admin.configuracion.update');
    });

    Route::middleware(['role:admin', 'can:Configuración Credenciales'])->group(function () {
        Route::get('/configuracion/credenciales', [ConfiguracionCredencialesController::class, 'index'])->name('configuracion.credenciales.index');
        Route::post('/configuracion/credenciales/actualizar', [ConfiguracionCredencialesController::class, 'actualizar'])->name('configuracion.credenciales.actualizar');
    });


    Route::get('/plantillas', [CorreoController::class, 'index'])->name('plantillas.index');
    Route::get('/plantillas/crear', [CorreoController::class, 'create'])->name('plantillas.create');
    Route::post('/plantillas', [CorreoController::class, 'store'])->name('plantillas.store');

    Route::get('/plantillas/{plantilla}/editar', [CorreoController::class, 'edit'])->name('plantillas.edit');
    Route::put('/plantillas/{plantilla}', [CorreoController::class, 'update'])->name('plantillas.update');

    Route::delete('/plantillas/{plantilla}', [CorreoController::class, 'destroy'])->name('plantillas.destroy');





    Route::middleware(['role:admin'])->group(function () {

        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index')->middleware('can:roles.inicio');
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('can:roles.crear');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store')->middleware('can:roles.guardar');
        Route::get('/roles/edit/{id}', [RoleController::class, 'edit'])->name('roles.edit')->middleware('can:roles.editar');
        Route::put('/roles/{id}', [RoleController::class, 'update'])->name('roles.update')->middleware('can:roles.actualizar');
        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('can:roles.eliminar');
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index')->middleware('can:permisos.inicio');
        Route::get('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create')->middleware('can:permisos.crear');
        Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store')->middleware('can:permisos.guardar');
        Route::get('/permissions/edit/{id}', [PermissionController::class, 'edit'])->name('permissions.edit')->middleware('can:permisos.editar');
        Route::put('/permissions/{id}', [PermissionController::class, 'update'])->name('permissions.update')->middleware('can:permisos.actualizar');
        Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])->name('permissions.destroy')->middleware('can:permisos.eliminar');
        Route::get('/permissions/cargar/menu/{id}/{rol_id}', [RoleController::class, 'get_permisos_menu'])->name('permissions.menu');

    });

    /*
     |--------------------------------------------------------------------------
     | CATÁLOGOS Y CATEGORÍAS
     |--------------------------------------------------------------------------
     |
     */

    Route::middleware(['auth', 'can:Administración y Parametrización'])->group(function () {

        // Rutas para catalogos
        Route::get('/catalogos', [CatalogoController::class, 'index'])->name('catalogos.index')->middleware('can:catalogo.ver');
        Route::get('/catalogos/create', [CatalogoController::class, 'create'])->name('catalogos.create')->middleware('can:catalogo.crear');
        Route::post('/catalogos', [CatalogoController::class, 'store'])->name('catalogos.store')->middleware('can:catalogo.guardar');
        Route::get('/catalogos/{id}', [CatalogoController::class, 'show'])->name('catalogos.show')->middleware('can:catalogo.ver_detalle');
        Route::get('/catalogos/{id}/edit', [CatalogoController::class, 'edit'])->name('catalogos.edit')->middleware('can:catalogo.editar');
        Route::put('/catalogos/{id}', [CatalogoController::class, 'update'])->name('catalogos.update')->middleware('can:catalogo.actualizar');
        Route::delete('/catalogos/{id}', [CatalogoController::class, 'destroy'])->name('catalogos.destroy')->middleware('can:catalogo.eliminar');

        // Rutas para categorias
        Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index')->middleware('can:categoria.ver');
        Route::get('/categorias/create', [CategoriaController::class, 'create'])->name('categorias.create')->middleware('can:categoria.crear');
        Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store')->middleware('can:categoria.guardar');
        Route::get('/categorias/{id}', [CategoriaController::class, 'show'])->name('categorias.show')->middleware('can:categoria.ver_detalle');
        Route::get('/categorias/{id}/edit', [CategoriaController::class, 'edit'])->name('categorias.edit')->middleware('can:categoria.editar');
        Route::put('/categorias/{id}', [CategoriaController::class, 'update'])->name('categorias.update')->middleware('can:categoria.actualizar');
        Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy'])->name('categorias.destroy')->middleware('can:categoria.eliminar');

        Route::get('/catalogos/ultimo-codigo/{categoria}', [CatalogoController::class, 'ultimoCodigo'])->name('catalogos.ultimoCodigo');

    });




    Route::get('/seeders/explorador', [SeederController::class, 'index'])
        ->name('seeders.index');

    Route::get('/seeders/ver', [SeederController::class, 'verSeeder'])
        ->name('seeders.ver');




});



/*
 * Sistema de ventas - Fase 1 (estructura, productos, personas e inventario).
 * Agregar en routes/web.php:   require __DIR__ . '/ventas.php';
 * Cada ruta se protege con el permiso de Spatie "modulo.accion".
 */
Route::middleware(['auth', ContextoTienda::class])->group(function () {

    // CRUD estándar: index, create/store, edit/update, destroy
    $crud = function (string $uri, string $nombre, string $controlador, string $param, string $permiso) {
        Route::get($uri, [$controlador, 'index'])->name("{$nombre}.index")->middleware("can:{$permiso}.ver");
        Route::get($uri . '/crear', [$controlador, 'create'])->name("{$nombre}.create")->middleware("can:{$permiso}.crear");
        Route::post($uri, [$controlador, 'store'])->name("{$nombre}.store")->middleware("can:{$permiso}.crear");
        Route::get($uri . '/{' . $param . '}/editar', [$controlador, 'edit'])->name("{$nombre}.edit")->middleware("can:{$permiso}.editar");
        Route::put($uri . '/{' . $param . '}', [$controlador, 'update'])->name("{$nombre}.update")->middleware("can:{$permiso}.editar");
        Route::delete($uri . '/{' . $param . '}', [$controlador, 'destroy'])->name("{$nombre}.destroy")->middleware("can:{$permiso}.eliminar");
    };

    // Tienda activa (selector del menú lateral)
    Route::post('tienda-activa', [TiendaActivaController::class, 'cambiar'])->name('tienda.cambiar');

    // Empresa
    Route::get('empresa', [EmpresaController::class, 'edit'])->name('empresa.edit')->middleware('can:empresa.ver');
    Route::put('empresa', [EmpresaController::class, 'update'])->name('empresa.update')->middleware('can:empresa.editar');

    // Tiendas y almacenes
    $crud('tiendas', 'tiendas', TiendaController::class, 'tienda', 'tiendas');
    Route::get('tiendas/{tienda}/usuarios', [TiendaUsuarioController::class, 'edit'])->name('tiendas.usuarios.edit')->middleware('can:tiendas.usuarios');
    Route::put('tiendas/{tienda}/usuarios', [TiendaUsuarioController::class, 'update'])->name('tiendas.usuarios.update')->middleware('can:tiendas.usuarios');
    $crud('almacenes', 'almacenes', AlmacenController::class, 'almacen', 'almacenes');

    // Productos
    $crud('categorias-producto', 'categorias_producto', CategoriaProductoController::class, 'categoria', 'categorias_producto');
    $crud('productos', 'productos', ProductoController::class, 'producto', 'productos');

    // Personas
    $crud('clientes', 'clientes', ClienteController::class, 'cliente', 'clientes');
    $crud('proveedores', 'proveedores', ProveedorController::class, 'proveedor', 'proveedores');

    // Inventario
    Route::prefix('inventario')->name('inventario.')->group(function () {
        Route::get('/', [InventarioController::class, 'index'])->name('index')->middleware('can:inventario.ver');
        Route::get('movimiento', [InventarioController::class, 'movimientoCreate'])->name('movimiento.create')->middleware('can:inventario.movimientos');
        Route::post('movimiento', [InventarioController::class, 'movimientoStore'])->name('movimiento.store')->middleware('can:inventario.movimientos');
        Route::get('kardex', [InventarioController::class, 'kardex'])->name('kardex')->middleware('can:kardex.ver');
        Route::get('lotes', [InventarioController::class, 'lotes'])->name('lotes')->middleware('can:lotes.ver');
        Route::get('configurar', [InventarioController::class, 'configEdit'])->name('config.edit')->middleware('can:inventario.configurar');
        Route::put('configurar/{inventario}', [InventarioController::class, 'configUpdate'])->name('config.update')->middleware('can:inventario.configurar');
    });
});


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




    Route::middleware(['auth', ContextoTienda::class])->group(function () {
        Route::get('mis-tiendas', [MisTiendasController::class, 'index'])->name('mis_tiendas.index');
        Route::post('mis-tiendas/{tienda}/entrar', [MisTiendasController::class, 'entrar'])->name('mis_tiendas.entrar');

        Route::get('tiendas/{tienda}/configuracion', [TiendaConfiguracionController::class, 'show'])->name('tiendas.configuracion')->middleware('can:tiendas.editar');
        Route::post('tiendas/{tienda}/configuracion/administrador', [TiendaConfiguracionController::class, 'administrador'])->name('tiendas.configuracion.administrador')->middleware('can:tiendas.usuarios');
        Route::post('tiendas/{tienda}/configuracion/caja', [TiendaConfiguracionController::class, 'caja'])->name('tiendas.configuracion.caja')->middleware('can:cajas.crear');
    });


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

    Route::post('/catalogos/crear-rapido', [CatalogoController::class, 'storeRapido'])->name('catalogos.storeRapido');
});








