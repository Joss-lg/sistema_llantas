<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CorteCaja;
use Symfony\Component\HttpFoundation\Response;

class VerificarCajaAbierta
{
    public function handle(Request $request, Closure $next): Response
    {
        // Revisamos si el usuario actual tiene una caja con estado 'abierta'
        $cajaAbierta = CorteCaja::where('user_id', Auth::id())
                                ->where('estado', 'abierta')
                                ->first();

        // Si NO tiene caja abierta, lo redirigimos a la pantalla de la caja
        if (!$cajaAbierta) {
            // Nota: 'caja.index' será la ruta de la pantalla que me mostraste en tu imagen
            return redirect()->route('caja.index')->with('alerta', 'Para poder cobrar, primero debes abrir tu caja ingresando el saldo inicial.');
        }

        // Si SÍ tiene caja, lo dejamos pasar al Punto de Venta
        return $next($request);
    }
}