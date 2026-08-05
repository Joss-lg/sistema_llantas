<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\StockSucursal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Antes esta lógica de filtros (tipo, marca_filtro, q, withSum de stock)
 * estaba duplicada palabra por palabra en index() y construirQueryExport().
 * Ahora vive en un solo lugar.
 */
class InventarioQueryService
{
    /**
     * @param  Request     $request
     * @param  int|null    $sucursalFiltro   null = todas las sucursales
     * @param  bool        $conStockMinimo   incluir columna stock_minimo (usado en el listado, no en export)
     */
    public function query(Request $request, ?int $sucursalFiltro, bool $conStockMinimo = false): Builder
    {
        $query = Producto::query();

        if ($sucursalFiltro) {
            $query->withSum(['stock as stock_cantidad' => function ($q) use ($sucursalFiltro) {
                $q->where('sucursal_id', $sucursalFiltro);
            }], 'cantidad');
        } else {
            $query->withSum('stock as stock_cantidad', 'cantidad');
        }

        if ($conStockMinimo) {
            $query->addSelect(['stock_minimo' => StockSucursal::select('stock_minimo')
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
            ->whereNotNull('marca')->where('marca', '!=', '')
            ->distinct()->orderBy('marca')->pluck('marca');
    }
}