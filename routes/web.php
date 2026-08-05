<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PuntoVentaController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\RoleController; // Dejamos solo RoleController (en inglés)

// Controllers de inventario (antes todo en InventarioController)
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ImportacionInventarioController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\ExportInventarioController;
use App\Http\Controllers\DisponibilidadController;

// Rutas Públicas (Login y Autenticación)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas Protegidas por Autenticación
Route::middleware(['auth'])->group(function () {

    // Destruir sesión automáticamente si el usuario navega hacia atrás
    Route::post('/logout-back-navigation', function (\Illuminate\Http\Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['status' => 'session_destroyed']);
    })->name('logout.back');

    // Dashboard Principal
    Route::get('/', function () {
        return view('dashboard');
    })->middleware('permiso')->name('dashboard');

    // Módulo de Empleados
    Route::resource('empleados', EmpleadoController::class)->middleware('permiso');

    // Módulo de Roles (Genera automáticamente roles.index, roles.create, roles.store, roles.edit, roles.update, roles.destroy)
    Route::resource('roles', RoleController::class)->middleware('permiso');

    // Módulo de Inventario
    Route::get('/inventario', [ProductoController::class, 'index'])->name('inventario.index');
    Route::post('/inventario/producto', [ProductoController::class, 'store'])->name('inventario.producto.store');

    Route::get('/inventario/importar', [ImportacionInventarioController::class, 'form'])->name('inventario.importar');
    Route::post('/inventario/importar', [ImportacionInventarioController::class, 'procesar'])->name('inventario.procesar');

    Route::post('/inventario/entrada', [MovimientoInventarioController::class, 'storeEntrada'])->name('inventario.entrada.store');
    Route::post('/inventario/salida', [MovimientoInventarioController::class, 'storeSalida'])->name('inventario.salida.store');
    Route::post('/inventario/traspaso', [MovimientoInventarioController::class, 'traspasarStock'])->name('inventario.traspaso.store');
    Route::get('/inventario/historial', [MovimientoInventarioController::class, 'historial'])->name('inventario.historial');

    Route::get('/inventario/exportar/excel', [ExportInventarioController::class, 'excel'])->name('inventario.exportar.excel');
    Route::get('/inventario/exportar/pdf', [ExportInventarioController::class, 'pdf'])->name('inventario.exportar.pdf');

    Route::get('/inventario/{producto}/disponibilidad', DisponibilidadController::class)->name('inventario.disponibilidad');

    // Módulo de Punto de Venta
    Route::get('/ventas', [PuntoVentaController::class, 'index'])->name('ventas.index');
    Route::post('/ventas/cobrar', [PuntoVentaController::class, 'store'])->name('ventas.store');
    Route::get('/ventas/ticket/{id}', [PuntoVentaController::class, 'ticket'])->name('ventas.ticket');
    Route::get('/historial-ventas', [PuntoVentaController::class, 'historial'])->name('ventas.historial');

    // Módulo de Gastos / Corte de Caja (MOCKUP)
    Route::get('/gastos', function() { return view('gastos.index'); })->name('gastos.index');

    // Módulos en construcción
    Route::get('/clientes', function() { return view('clientes.index'); })->name('clientes.index');
    Route::get('/reportes', function() { return view('reportes.index'); })->name('reportes.index');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');