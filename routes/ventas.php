<?php

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
use Illuminate\Support\Facades\Route;

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
