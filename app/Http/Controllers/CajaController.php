<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CorteCaja;
use App\Models\Venta;
use App\Models\MovimientoCaja;

class CajaController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        
        // 1. Buscar si el cajero actual tiene un turno activo
        $corteActual = CorteCaja::where('user_id', $usuario->id)
                                ->where('estado', 'abierta')
                                ->first();

        // 2. Si NO hay caja abierta, mandamos la vista limpia para que ingrese su fondo inicial
        if (!$corteActual) {
            return view('caja.index', ['corteActual' => null]);
        }

        // 3. Si SÍ hay caja abierta, calculamos toda la matemática del Resumen de Turno
        
        // Sumamos el total de todas las ventas ligadas a este turno exacto
        $totalVentas = Venta::where('corte_caja_id', $corteActual->id)->sum('total');
        
        // Sumamos los Gastos y Salidas (Egresos)
        $totalGastos = MovimientoCaja::where('corte_caja_id', $corteActual->id)
                                     ->where('tipo', 'egreso')
                                     ->sum('monto');
                                     
        // Sumamos los Anticipos / Apartados (Ingresos)
        $totalAnticipos = MovimientoCaja::where('corte_caja_id', $corteActual->id)
                                        ->where('tipo', 'ingreso')
                                        ->sum('monto');

        // Calculamos el Saldo Actual Estimado en Caja
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
        // Validamos que forzosamente ingrese un número como fondo de caja
        $request->validate([
            'saldo_inicial' => 'required|numeric|min:0'
        ]);

        // Doble validación de seguridad por si le da doble clic al botón
        $cajaAbierta = CorteCaja::where('user_id', Auth::id())
                                ->where('estado', 'abierta')
                                ->first();

        if ($cajaAbierta) {
            return back()->with('error', 'Ya tienes un turno activo.');
        }

        // Recuperamos la sucursal del empleado o asignamos la matriz por defecto
        $sucursal_id = Auth::user()->sucursal_id ?? 1;

        // Creamos el registro del turno
        CorteCaja::create([
            'user_id' => Auth::id(),
            'sucursal_id' => $sucursal_id, 
            'saldo_inicial' => $request->saldo_inicial,
            'estado' => 'abierta',
            'fecha_apertura' => now(),
        ]);

        // ¡Y lo mandamos directo al Punto de Venta a vender!
        return redirect()->route('ventas.index')->with('success', 'Caja abierta con éxito. ¡Excelente turno!');
    }

    public function cerrar(Request $request)
    {
        // Esta función la conectaremos cuando diseñemos el modal de Cierre de Caja
        return back()->with('success', 'Lógica de cierre de caja en construcción.');
    }
}