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

        // 3. MATEMÁTICA DEL CAJÓN FÍSICO
        $totalVentas = Venta::where('corte_caja_id', $corteActual->id)
                            ->where('metodo_pago', 'Efectivo')
                            ->sum('total');

        $totalGastos = MovimientoCaja::where('corte_caja_id', $corteActual->id)
                                     ->where('tipo', 'egreso')
                                     ->sum('monto');

        $totalAnticipos = MovimientoCaja::where('corte_caja_id', $corteActual->id)
                                        ->where('tipo', 'ingreso')
                                        ->sum('monto');

        $saldoEstimado = ($corteActual->saldo_inicial + $totalVentas + $totalAnticipos) - $totalGastos;

        // 4. Historial de ventas y movimientos del turno
        $ventasDelTurno = Venta::where('corte_caja_id', $corteActual->id)
                               ->orderBy('created_at', 'desc')
                               ->get();

        $gastosDelTurno = MovimientoCaja::where('corte_caja_id', $corteActual->id)
                                        ->where('tipo', 'egreso')
                                        ->orderBy('created_at', 'desc')
                                        ->get();

        $anticiposDelTurno = MovimientoCaja::where('corte_caja_id', $corteActual->id)
                                           ->where('tipo', 'ingreso')
                                           ->orderBy('created_at', 'desc')
                                           ->get();

        return view('caja.index', compact(
            'corteActual',
            'totalVentas',
            'totalGastos',
            'totalAnticipos',
            'saldoEstimado',
            'ventasDelTurno',
            'gastosDelTurno',
            'anticiposDelTurno'
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
            'user_id'        => Auth::id(),
            'sucursal_id'    => $sucursal_id,
            'saldo_inicial'  => $request->saldo_inicial,
            'estado'         => 'abierta',
            'fecha_apertura' => now(),
        ]);

        return redirect()->route('ventas.index')->with('success', 'Caja abierta con éxito. ¡Excelente turno!');
    }

    public function cerrar(Request $request)
    {
        $corteActual = CorteCaja::where('user_id', Auth::id())
                                ->where('estado', 'abierta')
                                ->first();

        if (!$corteActual) {
            return back()->with('error', 'No hay ninguna caja abierta para cerrar.');
        }

        $totalVentasEfectivo = Venta::where('corte_caja_id', $corteActual->id)->where('metodo_pago', 'Efectivo')->sum('total');
        $totalGastos         = MovimientoCaja::where('corte_caja_id', $corteActual->id)->where('tipo', 'egreso')->sum('monto');
        $totalAnticipos      = MovimientoCaja::where('corte_caja_id', $corteActual->id)->where('tipo', 'ingreso')->sum('monto');

        $saldoEstimado = ($corteActual->saldo_inicial + $totalVentasEfectivo + $totalAnticipos) - $totalGastos;

        $corteActual->update([
            'estado'       => 'cerrada',
            'fecha_cierre' => now(),
            'saldo_final'  => $saldoEstimado,
        ]);

        return redirect()->route('caja.index')->with('success', 'Corte de caja realizado correctamente. Turno finalizado.');
    }

    // ----------------------------------------
    // REGISTRAR GASTO / SALIDA DE EFECTIVO
    // ----------------------------------------
    public function storeGasto(Request $request)
    {
        $request->validate([
            'concepto' => 'required|string|max:255',
            'monto'    => 'required|numeric|min:0.01',
        ]);

        $corteActual = CorteCaja::where('user_id', Auth::id())
                                ->where('estado', 'abierta')
                                ->first();

        if (!$corteActual) {
            return back()->with('error', 'No hay una caja abierta para registrar el gasto.');
        }

        MovimientoCaja::create([
            'corte_caja_id' => $corteActual->id,
            'tipo'          => 'egreso',
            'concepto'      => $request->concepto,
            'monto'         => $request->monto,
        ]);

        return back()->with('success', 'Gasto registrado correctamente.');
    }

    // ----------------------------------------
    // REGISTRAR ANTICIPO / INGRESO DE EFECTIVO
    // ----------------------------------------
    public function storeAnticipo(Request $request)
    {
        $request->validate([
            'concepto' => 'required|string|max:255',
            'monto'    => 'required|numeric|min:0.01',
        ]);

        $corteActual = CorteCaja::where('user_id', Auth::id())
                                ->where('estado', 'abierta')
                                ->first();

        if (!$corteActual) {
            return back()->with('error', 'No hay una caja abierta para registrar el anticipo.');
        }

        MovimientoCaja::create([
            'corte_caja_id' => $corteActual->id,
            'tipo'          => 'ingreso',
            'concepto'      => $request->concepto,
            'monto'         => $request->monto,
        ]);

        return back()->with('success', 'Anticipo registrado correctamente.');
    }

    /**
     * Historial de Cajas
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

        $cortes = $query->orderByRaw("estado = 'abierta' DESC")
                         ->orderBy('fecha_apertura', 'desc')
                         ->paginate(15)
                         ->withQueryString();

        $sucursales        = Sucursal::all();
        $cajeros           = User::orderBy('name')->get();
        $totalCajasAbiertas = CorteCaja::where('estado', 'abierta')->count();

        return view('caja.historial', compact('cortes', 'sucursales', 'cajeros', 'totalCajasAbiertas'));
    }
}