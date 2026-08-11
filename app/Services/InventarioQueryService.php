<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\StockSucursal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class InventarioQueryService
{
    /**
     * @param Request $request
     * @param int|null $sucursalFiltro null = todas las sucursales
     * @param bool $conStockMinimo incluir subconsulta stock_minimo
     */
    public function query(Request $request, ?int $sucursalFiltro, bool $conStockMinimo = false): Builder
    {
        // Aseguramos seleccionar todas las columnas de la tabla productos
        $query = Producto::query()->select('productos.*');

        if ($sucursalFiltro) {
            $query->withSum(['stock as stock_cantidad' => function ($q) use ($sucursalFiltro) {
                $q->where('sucursal_id', $sucursalFiltro);
            }], 'cantidad');
        } else {
            $query->withSum('stock as stock_cantidad', 'cantidad');
        }

        if ($conStockMinimo) {
            $query->addSelect([
                'stock_minimo' => StockSucursal::select('stock_minimo')
                    ->whereColumn('producto_id', 'productos.id')
                    ->when($sucursalFiltro, fn ($q) => $q->where('sucursal_id', $sucursalFiltro))
                    ->limit(1),
            ]);
        }

        if ($request->filled('tipo') && $request->tipo !== 'Todos') {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('marca_filtro') && $request->marca_filtro !== 'Todas') {
            $query->where('marca', $request->marca_filtro);
        }

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('marca', 'like', '%' . $request->q . '%')
                  ->orWhere('medida', 'like', '%' . $request->q . '%')
                  ->orWhere('descripcion', 'like', '%' . $request->q . '%');
            });
        }

        return $query;
    }

    public function marcasDisponibles()
    {
        return Producto::select('marca')
            ->whereNotNull('marca')
            ->where('marca', '!=', '')
            ->distinct()
            ->orderBy('marca')
            ->pluck('marca');
    }
}