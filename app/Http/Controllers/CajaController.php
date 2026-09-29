<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CorteCaja;
use App\Models\Venta;
use App\Models\MovimientoCaja;
use App\Models\Sucursal;
use App\Models\User;

class CajaController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        
        // 1. Buscar si el cajero actual tiene un turno activo
        $corteActual = CorteCaja::where('user_id', $usuario->id)
                                ->where('estado', 'abierta')
                                ->first();

        // 2. Si NO hay caja abierta, mandamos la vista limpia
        if (!$corteActual) {
            return view('caja.index', ['corteActual' => null]);
        }

        // 3. MATEMÁTICA EXACTA PARA EL CAJÓN FÍSICO
        
        // Sumamos el total de las ventas ligadas a este turno, PERO SOLO EN EFECTIVO
        $totalVentas = Venta::where('corte_caja_id', $corteActual->id)
                            ->where('metodo_pago', 'Efectivo')
                            ->sum('total');
        
        // Sumamos los Gastos y Salidas (Egresos en efectivo)
        $totalGastos = MovimientoCaja::where('corte_caja_id', $corteActual->id)
                                     ->where('tipo', 'egreso')
                                     ->sum('monto');
                                     
        // Sumamos los Anticipos / Apartados (Ingresos en efectivo)
        $totalAnticipos = MovimientoCaja::where('corte_caja_id', $corteActual->id)
                                        ->where('tipo', 'ingreso')
                                        ->sum('monto');

        // Calculamos el Saldo Actual Estimado en Caja Físico
        $saldoEstimado = ($corteActual->saldo_inicial + $totalVentas + $totalAnticipos) - $totalGastos;

        // Traemos el historial de ventas de hoy para la tabla central
        $ventasDelTurno = Venta::where('corte_caja_id', $corteActual->id)
                               ->orderBy('created_at', 'desc')
                               ->get();

        return view('caja.index', compact(
            'corteActual', 
            'totalVentas', 
            'totalGastos', 
            'totalAnticipos', 
            'saldoEstimado',
            'ventasDelTurno'
        ));
    }

    public function abrir(Request $request)
    {
        $request->validate([
            'saldo_inicial' => 'required|numeric|min:0'
        ]);

        $cajaAbierta = CorteCaja::where('user_id', Auth::id())
                                ->where('estado', 'abierta')
                                ->first();

        if ($cajaAbierta) {
            return back()->with('error', 'Ya tienes un turno activo.');
        }

        $sucursal_id = Auth::user()->sucursal_id ?? 1;

        CorteCaja::create([
            'user_id' => Auth::id(),
            'sucursal_id' => $sucursal_id, 
            'saldo_inicial' => $request->saldo_inicial,
            'estado' => 'abierta',
            'fecha_apertura' => now(),
        ]);

        return redirect()->route('ventas.index')->with('success', 'Caja abierta con éxito. ¡Excelente turno!');
    }

    public function cerrar(Request $request)
    {
        // Buscamos la caja abierta del usuario
        $corteActual = CorteCaja::where('user_id', Auth::id())
                                ->where('estado', 'abierta')
                                ->first();

        if (!$corteActual) {
            return back()->with('error', 'No hay ninguna caja abierta para cerrar.');
        }

        // Calculamos los totales finales para el historial
        $totalVentasEfectivo = Venta::where('corte_caja_id', $corteActual->id)->where('metodo_pago', 'Efectivo')->sum('total');
        $totalGastos = MovimientoCaja::where('corte_caja_id', $corteActual->id)->where('tipo', 'egreso')->sum('monto');
        $totalAnticipos = MovimientoCaja::where('corte_caja_id', $corteActual->id)->where('tipo', 'ingreso')->sum('monto');
        
        $saldoEstimado = ($corteActual->saldo_inicial + $totalVentasEfectivo + $totalAnticipos) - $totalGastos;
        
        // Actualizamos el registro para cerrarlo y construir el historial
        // CORRECCIÓN: ahora sí se guarda el saldo_final, necesario para el Historial de Cajas
        $corteActual->update([
            'estado'      => 'cerrada',
            'fecha_cierre'=> now(),
            'saldo_final' => $saldoEstimado,
        ]);

        return redirect()->route('caja.index')->with('success', 'Corte de caja realizado correctamente. Turno finalizado.');
    }

    /**
     * Historial de Cajas — solo accesible con el permiso 'caja.historial'
     * (por defecto, solo el Administrador General lo tiene vía bypass).
     */
    public function historial(Request $request)
    {
        $query = CorteCaja::with(['user', 'sucursal'])
            ->withSum(['ventas as total_ventas_efectivo' => function ($q) {
                $q->where('metodo_pago', 'Efectivo');
            }], 'total')
            ->withSum('ventas as total_ventas', 'total')
            ->withCount('ventas as total_transacciones');

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        if ($request->filled('estado') && in_array($request->estado, ['abierta', 'cerrada'])) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_apertura', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_apertura', '<=', $request->fecha_hasta);
        }

        // Las cajas abiertas siempre aparecen primero, luego por fecha de apertura descendente
        $cortes = $query->orderByRaw("estado = 'abierta' DESC")
                         ->orderBy('fecha_apertura', 'desc')
                         ->paginate(15)
                         ->withQueryString();

        $sucursales = Sucursal::all();
        $cajeros = User::orderBy('name')->get();
        $totalCajasAbiertas = CorteCaja::where('estado', 'abierta')->count();

        return view('caja.historial', compact('cortes', 'sucursales', 'cajeros', 'totalCajasAbiertas'));
    }
}