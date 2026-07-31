<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PuntoVentaController;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {

    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    // Módulo de Inventario
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
    Route::get('/inventario/importar', [InventarioController::class, 'importar'])->name('inventario.importar');
    Route::post('/inventario/importar', [InventarioController::class, 'procesarImportacion'])->name('inventario.procesar');
    Route::post('/inventario/producto', [InventarioController::class, 'storeProducto'])->name('inventario.producto.store');
    Route::post('/inventario/entrada', [InventarioController::class, 'storeEntrada'])->name('inventario.entrada.store');
    Route::post('/inventario/salida', [InventarioController::class, 'storeSalida'])->name('inventario.salida.store');
    Route::get('/inventario/historial', [InventarioController::class, 'historial'])->name('inventario.historial');
    Route::get('/inventario/exportar/excel', [InventarioController::class, 'exportarExcel'])->name('inventario.exportar.excel');
    Route::get('/inventario/exportar/pdf', [InventarioController::class, 'exportarPdf'])->name('inventario.exportar.pdf');

    // Módulo de Punto de Venta
    Route::get('/ventas', [PuntoVentaController::class, 'index'])->name('ventas.index');
    Route::post('/ventas/cobrar', [PuntoVentaController::class, 'store'])->name('ventas.store');
    Route::get('/ventas/ticket/{id}', [PuntoVentaController::class, 'ticket'])->name('ventas.ticket');
    Route::get('/historial-ventas', [PuntoVentaController::class, 'historial'])->name('ventas.historial');

    // Módulo de Gastos / Corte de Caja (MOCKUP - vista directa, sin controlador todavía)
    Route::get('/gastos', function() { return view('gastos.index'); })->name('gastos.index');

    // Módulos en construcción
    Route::get('/clientes', function() { return view('clientes.index'); })->name('clientes.index');
    Route::get('/reportes', function() { return view('reportes.index'); })->name('reportes.index');
});
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
