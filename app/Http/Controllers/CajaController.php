<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $corteActual = CorteCaja::with('sucursal')
                                ->where('user_id', $usuario->id)
                                ->where('estado', 'abierta')
                                ->first();

        // 2. Si NO hay caja abierta, mandamos la vista limpia (con la sucursal donde se abriría)
        if (!$corteActual) {
            return view('caja.index', [
                'corteActual'     => null,
                'sucursalUsuario' => $usuario->sucursal,
            ]);
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

        $usuario = Auth::user();

        // Si el usuario no tiene sucursal, NO se abre la caja
        if (!$usuario->sucursal_id) {
            return back()->with('error', 'Tu usuario no tiene una sucursal asignada. Pide a un administrador que te asigne una antes de abrir caja.');
        }

        // Transacción con bloqueo: si llegan dos peticiones a la vez (doble clic, dos pestañas),
        // la segunda espera a que termine la primera y ya encuentra el turno abierto.
        $abierta = DB::transaction(function () use ($request, $usuario) {
            User::where('id', $usuario->id)->lockForUpdate()->first();

            $yaAbierta = CorteCaja::where('user_id', $usuario->id)
                                  ->where('estado', 'abierta')
                                  ->lockForUpdate()
                                  ->first();

            if ($yaAbierta) {
                return false;
            }

            CorteCaja::create([
                'user_id'        => $usuario->id,
                'sucursal_id'    => $usuario->sucursal_id,
                'saldo_inicial'  => $request->saldo_inicial,
                'estado'         => 'abierta',
                'fecha_apertura' => now(),
            ]);

            return true;
        });

        if (!$abierta) {
            return back()->with('error', 'Ya tienes un turno activo.');
        }

        return redirect()->route('ventas.index')->with('success', 'Caja abierta con éxito. ¡Excelente turno!');
    }

    public function cerrar(Request $request)
    {
        // El cajero debe capturar cuánto efectivo contó en el cajón
        $request->validate([
            'efectivo_contado' => 'required|numeric|min:0',
        ], [
            'efectivo_contado.required' => 'Captura el efectivo contado para cerrar la caja.',
            'efectivo_contado.numeric'  => 'El efectivo contado debe ser un número.',
            'efectivo_contado.min'      => 'El efectivo contado no puede ser negativo.',
        ]);

        // Transacción con bloqueo: si el cierre se manda dos veces, la segunda ya no encuentra turno abierto.
        $resultado = DB::transaction(function () use ($request) {
            $corteActual = CorteCaja::where('user_id', Auth::id())
                                    ->where('estado', 'abierta')
                                    ->lockForUpdate()
                                    ->first();

            if (!$corteActual) {
                return null;
            }

            $totalVentasEfectivo = Venta::where('corte_caja_id', $corteActual->id)->where('metodo_pago', 'Efectivo')->sum('total');
            $totalGastos         = MovimientoCaja::where('corte_caja_id', $corteActual->id)->where('tipo', 'egreso')->sum('monto');
            $totalAnticipos      = MovimientoCaja::where('corte_caja_id', $corteActual->id)->where('tipo', 'ingreso')->sum('monto');

            $esperado = round(($corteActual->saldo_inicial + $totalVentasEfectivo + $totalAnticipos) - $totalGastos, 2);
            $contado  = round((float) $request->efectivo_contado, 2);

            // contado - esperado: positivo = sobrante, negativo = faltante
            $diferencia = round($contado - $esperado, 2);

            $corteActual->update([
                'estado'           => 'cerrada',
                'fecha_cierre'     => now(),
                'saldo_final'      => $esperado,
                'efectivo_contado' => $contado,
                'diferencia'       => $diferencia,
            ]);

            return ['esperado' => $esperado, 'contado' => $contado, 'diferencia' => $diferencia];
        });

        if (!$resultado) {
            return back()->with('error', 'No hay ninguna caja abierta para cerrar.');
        }

        $diferencia = $resultado['diferencia'];

        if ($diferencia > 0) {
            $detalle = 'Hay un SOBRANTE de $' . number_format($diferencia, 2) . '.';
        } elseif ($diferencia < 0) {
            $detalle = 'Hay un FALTANTE de $' . number_format(abs($diferencia), 2) . '.';
        } else {
            $detalle = 'La caja cuadra exacto.';
        }

        return redirect()->route('caja.index')->with('success', 'Corte de caja realizado. Turno finalizado. ' . $detalle);
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

        // Transacción con bloqueo del turno: así el gasto no puede entrar
        // mientras otra pestaña está cerrando la caja (y viceversa).
        $guardado = DB::transaction(function () use ($request) {
            $corteActual = CorteCaja::where('user_id', Auth::id())
                                    ->where('estado', 'abierta')
                                    ->lockForUpdate()
                                    ->first();

            if (!$corteActual) {
                return false;
            }

            MovimientoCaja::create([
                'corte_caja_id' => $corteActual->id,
                'tipo'          => 'egreso',
                'concepto'      => $request->concepto,
                'monto'         => $request->monto,
            ]);

            return true;
        });

        if (!$guardado) {
            return back()->with('error', 'No hay una caja abierta para registrar el gasto.');
        }

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

        // Transacción con bloqueo del turno: igual que en el gasto.
        $guardado = DB::transaction(function () use ($request) {
            $corteActual = CorteCaja::where('user_id', Auth::id())
                                    ->where('estado', 'abierta')
                                    ->lockForUpdate()
                                    ->first();

            if (!$corteActual) {
                return false;
            }

            MovimientoCaja::create([
                'corte_caja_id' => $corteActual->id,
                'tipo'          => 'ingreso',
                'concepto'      => $request->concepto,
                'monto'         => $request->monto,
            ]);

            return true;
        });

        if (!$guardado) {
            return back()->with('error', 'No hay una caja abierta para registrar el anticipo.');
        }

        return back()->with('success', 'Anticipo registrado correctamente.');
    }

    /**
     * Historial de Cajas
     *
     * El Administrador General ve todas las sucursales.
     * Cualquier otro usuario ve solo los datos de su sucursal.
     */
    public function historial(Request $request)
    {
        $usuario        = Auth::user();
        $esAdminGeneral = $usuario->tieneRol('Administrador General');

        $query = CorteCaja::with(['user', 'sucursal'])
            ->withSum(['ventas as total_ventas_efectivo' => function ($q) {
                $q->where('metodo_pago', 'Efectivo');
            }], 'total')
            ->withSum('ventas as total_ventas', 'total')
            ->withCount('ventas as total_transacciones')
            // Ventas de este corte hechas en una sucursal distinta a la del corte
            ->withCount(['ventas as ventas_otra_sucursal' => function ($q) {
                $q->whereColumn('ventas.sucursal_id', '!=', 'cortes_caja.sucursal_id');
            }]);

        // Sucursal: el admin general puede elegir; los demás quedan fijos en la suya
        if ($esAdminGeneral) {
            if ($request->filled('sucursal_id')) {
                $query->where('sucursal_id', $request->sucursal_id);
            }
        } else {
            $query->where('sucursal_id', $usuario->sucursal_id);
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

        // Listas de la pantalla: también limitadas a la sucursal del usuario
        if ($esAdminGeneral) {
            $sucursales         = Sucursal::all();
            $cajeros            = User::orderBy('name')->get();
            $totalCajasAbiertas = CorteCaja::where('estado', 'abierta')->count();
        } else {
            $sucursales         = Sucursal::where('id', $usuario->sucursal_id)->get();
            $cajeros            = User::where('sucursal_id', $usuario->sucursal_id)->orderBy('name')->get();
            $totalCajasAbiertas = CorteCaja::where('estado', 'abierta')
                                           ->where('sucursal_id', $usuario->sucursal_id)
                                           ->count();
        }

        return view('caja.historial', compact('cortes', 'sucursales', 'cajeros', 'totalCajasAbiertas'));
    }
}
