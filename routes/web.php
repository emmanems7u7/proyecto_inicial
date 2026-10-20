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









