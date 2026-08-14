<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PuntoVentaController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ClienteController;

// Controllers de inventario
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ImportacionInventarioController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\ExportInventarioController;
use App\Http\Controllers\DisponibilidadController;

// --- NUEVO CONTROLLER DE CAJA ---
use App\Http\Controllers\CajaController; 

// Rutas Públicas
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas Protegidas por Autenticación Y Permisos
Route::middleware(['auth', 'permiso'])->group(function () {

    Route::post('/logout-back-navigation', function (\Illuminate\Http\Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['status' => 'session_destroyed']);
    })->name('logout.back');

    // Dashboard Principal
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    // Módulo de Empleados y Roles
    Route::get('/empleados/inactivos', [EmpleadoController::class, 'inactivos'])->name('empleados.inactivos');
    Route::put('/empleados/{empleado}/permisos', [EmpleadoController::class, 'updatePermisos'])->name('empleados.permisos.update');
    Route::patch('/empleados/{empleado}/toggle', [EmpleadoController::class, 'toggleStatus'])->name('empleados.toggle');

    Route::resource('empleados', EmpleadoController::class);
    Route::resource('roles', RoleController::class);

    // Módulo de Clientes
    Route::resource('clientes', ClienteController::class);

    // Módulo de Inventario
    Route::get('/inventario', [ProductoController::class, 'index'])->name('inventario.index');
    
    // Rutas conectadas a los modales de la vista de inventario:
    Route::post('/inventario/producto', [ProductoController::class, 'store'])->name('inventario.storeProducto');
    Route::post('/inventario/entrada', [MovimientoInventarioController::class, 'storeEntrada'])->name('inventario.storeEntrada');

    Route::get('/inventario/importar', [ImportacionInventarioController::class, 'form'])->name('inventario.importar');
    Route::post('/inventario/importar', [ImportacionInventarioController::class, 'procesar'])->name('inventario.procesar');

    Route::post('/inventario/salida', [MovimientoInventarioController::class, 'storeSalida'])->name('inventario.salida.store');
    Route::post('/inventario/traspaso', [MovimientoInventarioController::class, 'traspasarStock'])->name('inventario.traspaso.store');
    Route::get('/inventario/historial', [MovimientoInventarioController::class, 'historial'])->name('inventario.historial');

    Route::get('/inventario/exportar/excel', [ExportInventarioController::class, 'excel'])->name('inventario.exportar.excel');
    Route::get('/inventario/exportar/pdf', [ExportInventarioController::class, 'pdf'])->name('inventario.exportar.pdf');

    Route::get('/inventario/{producto}/disponibilidad', DisponibilidadController::class)->name('inventario.disponibilidad');

    // ==========================================
    // MÓDULO DE CAJA (CONTROL DE TURNOS)
    // ==========================================
    Route::get('/caja', [CajaController::class, 'index'])->name('caja.index');
    Route::post('/caja/abrir', [CajaController::class, 'abrir'])->name('caja.abrir');
    Route::post('/caja/cerrar', [CajaController::class, 'cerrar'])->name('caja.cerrar');

    // ==========================================
    // MÓDULO DE PUNTO DE VENTA
    // ==========================================
    Route::middleware(['caja.abierta'])->group(function () {
        Route::get('/ventas', [PuntoVentaController::class, 'index'])->name('ventas.index');
        Route::post('/ventas/cobrar', [PuntoVentaController::class, 'store'])->name('ventas.store');
    });

    Route::get('/ventas/ticket/{id}', [PuntoVentaController::class, 'ticket'])->name('ventas.ticket');
    Route::get('/historial-ventas', [PuntoVentaController::class, 'historial'])->name('ventas.historial');

    // Módulo de Gastos
    Route::get('/gastos', function() { return view('gastos.index'); })->name('gastos.index');

    // Módulos en construcción
    Route::get('/reportes', function() { return view('reportes.index'); })->name('reportes.index');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');