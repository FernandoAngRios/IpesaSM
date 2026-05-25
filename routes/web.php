<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Empleados\CategoryController;
use App\Http\Controllers\Empleados\DashboardController;
use App\Http\Controllers\Empleados\InternalMessageController;
use App\Http\Controllers\Empleados\MessageController;
use App\Http\Controllers\Empleados\CajaController;
use App\Http\Controllers\Empleados\PosController;
use App\Http\Controllers\Empleados\ProductController;
use App\Http\Controllers\Empleados\ProductImportController;
use App\Http\Controllers\Empleados\EntradaController;
use App\Http\Controllers\Empleados\SucursalController;
use App\Http\Controllers\Empleados\TransferenciaController;
use App\Http\Controllers\Empleados\SolicitudTransferenciaController;
use App\Http\Controllers\Empleados\UserController;
use App\Http\Controllers\Empleados\DevolucionController;
use App\Http\Controllers\Empleados\ExportController;
use App\Http\Controllers\Empleados\TintaController;
use App\Http\Controllers\Empleados\VendedorController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

// Landing pública
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::post('/contacto', [LandingController::class, 'storeContact'])->name('contacto.store');

// Autenticación de empleados
Route::prefix('empleados')->name('empleados.')->group(function () {
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Panel protegido
    Route::middleware('employee')->group(function () {
        Route::get('/', fn() => redirect()->route('empleados.pos.index'))->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.show');

        // Productos — rutas sin parámetro primero para evitar que {product} capture "create"/"importar"/etc.
        Route::get('productos',           [ProductController::class, 'index'])->name('products.index');
        Route::get('productos/create',    [ProductController::class, 'create'])->middleware('admin')->name('products.create');
        Route::post('productos',          [ProductController::class, 'store'])->middleware('admin')->name('products.store');
        Route::get('productos/importar',  [ProductImportController::class, 'create'])->middleware('admin')->name('products.import');
        Route::post('productos/importar', [ProductImportController::class, 'store'])->middleware('admin')->name('products.import.store');
        Route::get('productos/plantilla', [ProductImportController::class, 'template'])->middleware('admin')->name('products.template');
        // Rutas con parámetro al final
        Route::get('productos/{product}',        [ProductController::class, 'show'])->name('products.show');
        Route::get('productos/{product}/edit',   [ProductController::class, 'edit'])->name('products.edit');
        Route::put('productos/{product}',        [ProductController::class, 'update'])->name('products.update');
        Route::delete('productos/{product}',             [ProductController::class, 'destroy'])->middleware('admin')->name('products.destroy');
        Route::delete('productos/{product}/imagen',      [ProductController::class, 'destroyImage'])->name('products.image.destroy');

        // Categorías — solo lectura para empleados
        Route::get('categorias', [CategoryController::class, 'index'])->name('categories.index');

        Route::get('mensajes', [MessageController::class, 'index'])->name('messages.index');
        Route::get('mensajes/{message}', [MessageController::class, 'show'])->name('messages.show');
        Route::delete('mensajes/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

        Route::get('mensajes-internos', [InternalMessageController::class, 'index'])->name('internal-messages.index');
        Route::get('mensajes-internos/nuevo', [InternalMessageController::class, 'create'])->name('internal-messages.create');
        Route::post('mensajes-internos', [InternalMessageController::class, 'store'])->name('internal-messages.store');
        Route::get('mensajes-internos/{internalMessage}', [InternalMessageController::class, 'show'])->name('internal-messages.show');
        Route::delete('mensajes-internos/{internalMessage}', [InternalMessageController::class, 'destroy'])->name('internal-messages.destroy');

        // Tintas — lectura y movimientos para todos, CRUD solo admin
        Route::get('tintas',                          [TintaController::class, 'index'])     ->name('tintas.index');
        Route::get('tintas/{tinta}',                  [TintaController::class, 'show'])      ->name('tintas.show');
        Route::post('tintas/{tinta}/movimiento',      [TintaController::class, 'movimiento'])->name('tintas.movimiento');


        // Almacenes — lectura y ajuste de stock para todos, escritura solo admin
        Route::get('almacenes',              [SucursalController::class, 'index'])->name('almacenes.index');
        Route::get('almacenes/{sucursal}',   [SucursalController::class, 'show']) ->name('almacenes.show');
        Route::post('almacenes/{sucursal}/stock', [SucursalController::class, 'ajustarStock'])
            ->name('almacenes.ajustar-stock');

        Route::get('entradas', [EntradaController::class, 'index'])->name('entradas.index');
        Route::get('entradas/nueva', [EntradaController::class, 'create'])->name('entradas.create');
        Route::post('entradas', [EntradaController::class, 'store'])->name('entradas.store');
        Route::get('entradas/{entrada}', [EntradaController::class, 'show'])->name('entradas.show');

        // POS — buscar debe ir antes de {venta} para evitar colisión
        Route::get('pos/buscar',                        [PosController::class, 'buscar'])    ->name('pos.buscar');
        Route::get('pos',                               [PosController::class, 'index'])     ->name('pos.index');
        Route::post('pos',                              [PosController::class, 'store'])     ->name('pos.store');
        Route::post('pos/{venta}/items',                [PosController::class, 'addItem'])   ->name('pos.items.add');
        Route::put('pos/{venta}/items/{item}',          [PosController::class, 'updateItem'])->name('pos.items.update');
        Route::delete('pos/{venta}/items/{item}',       [PosController::class, 'removeItem'])->name('pos.items.remove');
        Route::post('pos/{venta}/confirmar',            [PosController::class, 'confirmar'])->name('pos.confirmar');
        Route::post('pos/{venta}/cancelar',             [PosController::class, 'cancelar']) ->name('pos.cancelar');
        Route::get('pos/{venta}/ticket',                [PosController::class, 'ticket'])      ->name('pos.ticket');
        Route::get('pos/{venta}/cotizacion',             [PosController::class, 'cotizacion'])       ->name('pos.cotizacion');
        Route::post('pos/{venta}/movimiento-caja',        [PosController::class, 'movimientoCaja'])   ->name('pos.movimiento-caja');
        Route::post('pos/{venta}/libre',                  [PosController::class, 'addLibre'])         ->name('pos.libre');
        Route::get('ventas',                             [PosController::class, 'historial'])->name('ventas.index');
        Route::get('ventas/{venta}',                     [PosController::class, 'show'])     ->name('ventas.show');
        Route::get('devoluciones',                       [DevolucionController::class, 'index']) ->name('devoluciones.index');
        Route::get('devoluciones/{devolucion}',          [DevolucionController::class, 'show'])  ->name('devoluciones.show');
        Route::get('ventas/{venta}/devolucion',          [DevolucionController::class, 'create'])->name('devoluciones.create');
        Route::post('ventas/{venta}/devolucion',         [DevolucionController::class, 'store']) ->name('devoluciones.store');

        // Caja
        Route::get('caja',                          [CajaController::class, 'index'])     ->name('caja.index');
        Route::post('caja/abrir',                   [CajaController::class, 'abrir'])     ->name('caja.abrir');
        Route::get('caja/{caja}',                   [CajaController::class, 'show'])      ->name('caja.show');
        Route::get('caja/{caja}/cerrar',            [CajaController::class, 'verCierre']) ->name('caja.cierre');
        Route::get('caja/{caja}/imprimir',          [CajaController::class, 'imprimir'])  ->name('caja.imprimir');
        Route::post('caja/{caja}/movimiento',       [CajaController::class, 'movimiento'])->name('caja.movimiento');
        Route::post('caja/{caja}/cerrar',           [CajaController::class, 'cerrar'])    ->name('caja.cerrar');

        // Exportaciones
        Route::get('exportar/ventas',      [ExportController::class, 'ventas'])     ->name('exportar.ventas');
        Route::get('exportar/inventario',  [ExportController::class, 'inventario']) ->name('exportar.inventario');

        Route::get('transferencias', [TransferenciaController::class, 'index'])->name('transferencias.index');
        Route::get('transferencias/nueva', [TransferenciaController::class, 'create'])->name('transferencias.create');
        Route::post('transferencias', [TransferenciaController::class, 'store'])->name('transferencias.store');
        Route::get('transferencias/{transferencia}', [TransferenciaController::class, 'show'])->name('transferencias.show');
        Route::post('transferencias/{transferencia}/confirmar', [TransferenciaController::class, 'confirmar'])->name('transferencias.confirmar');

        // Solicitudes de mercancía entre sucursales
        Route::get('solicitudes',                                  [SolicitudTransferenciaController::class, 'index'])   ->name('solicitudes.index');
        Route::get('solicitudes/nueva',                            [SolicitudTransferenciaController::class, 'create'])  ->name('solicitudes.create');
        Route::post('solicitudes',                                 [SolicitudTransferenciaController::class, 'store'])   ->name('solicitudes.store');
        Route::get('solicitudes/{solicitud}',                      [SolicitudTransferenciaController::class, 'show'])    ->name('solicitudes.show');
        Route::post('solicitudes/{solicitud}/procesar',            [SolicitudTransferenciaController::class, 'procesar'])->name('solicitudes.procesar');
        Route::post('solicitudes/{solicitud}/recibir',             [SolicitudTransferenciaController::class, 'recibir']) ->name('solicitudes.recibir');
        Route::post('solicitudes/{solicitud}/cancelar',            [SolicitudTransferenciaController::class, 'cancelar'])->name('solicitudes.cancelar');

        Route::middleware('admin')->group(function () {
            // Entradas — editar y eliminar solo admin
            Route::get('entradas/{entrada}/edit', [EntradaController::class, 'edit'])   ->name('entradas.edit');
            Route::put('entradas/{entrada}',       [EntradaController::class, 'update']) ->name('entradas.update');
            Route::delete('entradas/{entrada}',    [EntradaController::class, 'destroy'])->name('entradas.destroy');

            // Categorías — escritura solo admin
            Route::post('categorias',             [CategoryController::class, 'store'])  ->name('categories.store');
            Route::put('categorias/{category}',   [CategoryController::class, 'update']) ->name('categories.update');
            Route::delete('categorias/{category}',[CategoryController::class, 'destroy'])->name('categories.destroy');

            // Almacenes — crear, editar, eliminar solo admin
            Route::resource('almacenes', SucursalController::class, [
                'only'       => ['create', 'store', 'edit', 'update', 'destroy'],
                'parameters' => ['almacenes' => 'sucursal'],
                'names'      => [
                    'create'  => 'almacenes.create',
                    'store'   => 'almacenes.store',
                    'edit'    => 'almacenes.edit',
                    'update'  => 'almacenes.update',
                    'destroy' => 'almacenes.destroy',
                ],
            ]);

            // Tintas — escritura solo admin
            Route::post('tintas',              [TintaController::class, 'store'])  ->name('tintas.store');
            Route::put('tintas/{tinta}',       [TintaController::class, 'update']) ->name('tintas.update');
            Route::delete('tintas/{tinta}',    [TintaController::class, 'destroy'])->name('tintas.destroy');


            // Vendedores y usuarios
            Route::get('vendedores',                    [VendedorController::class, 'index'])  ->name('vendedores.index');
            Route::post('vendedores',                   [VendedorController::class, 'store'])  ->name('vendedores.store');
            Route::put('vendedores/{vendedor}',         [VendedorController::class, 'update']) ->name('vendedores.update');
            Route::delete('vendedores/{vendedor}',      [VendedorController::class, 'destroy'])->name('vendedores.destroy');

            Route::resource('usuarios', UserController::class, [
                'only'       => ['index', 'create', 'store', 'edit', 'update', 'destroy'],
                'parameters' => ['usuarios' => 'user'],
                'names'      => [
                    'index'   => 'usuarios.index',
                    'create'  => 'usuarios.create',
                    'store'   => 'usuarios.store',
                    'edit'    => 'usuarios.edit',
                    'update'  => 'usuarios.update',
                    'destroy' => 'usuarios.destroy',
                ],
            ]);
        });
    });
});
