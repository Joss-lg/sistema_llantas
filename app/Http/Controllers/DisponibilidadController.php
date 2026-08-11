<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveContextoSucursal;
use App\Models\Producto;
use App\Models\StockSucursal;
use Illuminate\Http\Request;

class DisponibilidadController extends Controller
{
    use ResuelveContextoSucursal;

    public function __invoke(Producto $producto, Request $request)
    {
        $sucursalActual = $this->usuarioEsAdmin()
            ? $request->input('sucursal_id', $this->sucursalDelUsuario())
            : $this->sucursalDelUsuario();

        $otrasSucursales = StockSucursal::whereHas('sucursal', function ($q) {
                $q->where('activa', true);
            })
            ->with('sucursal:id,nombre')
            ->where('producto_id', $producto->id)
            ->where('sucursal_id', '!=', $sucursalActual)
            ->where('cantidad', '>', 0)
            ->orderByDesc('cantidad')
            ->get()
            ->map(fn ($stock) => [
                'sucursal_id' => $stock->sucursal_id,
                'sucursal'    => $stock->sucursal->nombre,
                'cantidad'    => $stock->cantidad,
            ]);

        return response()->json([
            'producto'            => trim($producto->marca . ' ' . $producto->medida),
            'sucursal_actual_id'  => $sucursalActual,
            'disponible_en_otras' => $otrasSucursales,
        ]);
    }
}