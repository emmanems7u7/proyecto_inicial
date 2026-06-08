<?php

use App\Http\Controllers\ApuestaController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\PartidoController;
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


/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS (SIN AUTH)
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\WelcomeController;

Route::get('/', [WelcomeController::class, 'index']);

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



    Route::get('/partidos', [PartidoController::class, 'index'])
        ->name('partidos.index');

    Route::post('/partidos/sincronizar', [PartidoController::class, 'sincronizar'])
        ->name('partidos.sincronizar');


    Route::get('/equipos', [EquipoController::class, 'index'])->name('equipos.index');

    Route::post('/equipos/sincronizar', [EquipoController::class, 'sincronizar'])->name('equipos.sincronizar');


});
Route::post('/apuestas', [ApuestaController::class, 'store'])
    ->name('apuestas.store');










