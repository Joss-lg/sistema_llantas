<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveContextoSucursal;
use App\Models\MovimientoInventario;
use App\Services\InventarioQueryService;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    use ResuelveContextoSucursal;

    public function __construct(private InventarioQueryService $inventarioQuery)
    {
    }

    public function index(Request $request)
    {
        $sucursalFiltro = $this->sucursalSeleccionada($request);

        // IDs de productos que tuvieron una ENTRADA HOY (para la etiqueta "NUEVO" y el filtro)
        $productosNuevosHoy = MovimientoInventario::where('tipo', 'entrada')
            ->whereDate('fecha', today())
            ->pluck('producto_id')
            ->unique()
            ->toArray();

        $query = $this->inventarioQuery->query($request, $sucursalFiltro, conStockMinimo: true);

        // FILTRO: solo los que llegaron hoy
        if ($request->filled('solo_nuevos') && $request->solo_nuevos == '1') {
            if (count($productosNuevosHoy) > 0) {
                $query->whereIn('id', $productosNuevosHoy);
            } else {
                $query->whereRaw('1 = 0'); // Nada llegó hoy: lista vacía
            }
        }

        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'sin_stock':
                    $query->having('stock_cantidad', '=', 0)->orHavingNull('stock_cantidad');
                    break;
                case 'bajo_stock':
                    $query->havingRaw('stock_cantidad < COALESCE(stock_minimo, 5)');
                    break;
                case 'ok':
                    $query->havingRaw('stock_cantidad >= COALESCE(stock_minimo, 5)');
                    break;
            }
        }

        if ($request->filled('ordenar_precio')) {
            switch ($request->ordenar_precio) {
                case 'costo_mayor': $query->orderBy('costo', 'desc'); break;
                case 'costo_menor': $query->orderBy('costo', 'asc'); break;
                case 'publico_mayor': $query->orderBy('precio_publico', 'desc'); break;
                case 'publico_menor': $query->orderBy('precio_publico', 'asc'); break;
                case 'mayoreo_mayor': $query->orderBy('precio_mayoreo', 'desc'); break;
                case 'mayoreo_menor': $query->orderBy('precio_mayoreo', 'asc'); break;
                default: $query->orderBy('marca')->orderBy('medida'); break;
            }
        } else {
            $query->orderBy('marca')->orderBy('medida');
        }

        $productos = $query->paginate(10)->withQueryString();
        $sucursales = $this->sucursalesDisponibles();
        $marcasDisponibles = $this->inventarioQuery->marcasDisponibles();

        return view('inventario.index', compact('productos', 'sucursales', 'marcasDisponibles', 'productosNuevosHoy'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|string',
            'marca' => 'required|string|max:100',
            'medida' => 'required|string|max:100',
        ]);

        \App\Models\Producto::create([
            'tipo' => $request->tipo,
            'marca' => mb_strtoupper($request->marca),
            'medida' => mb_strtoupper($request->medida),
            'descripcion' => $request->descripcion,
            'costo' => 0,
            'precio_mayoreo' => 0,
            'precio_publico' => 0,
            'estado' => true,
        ]);

        return redirect()->route('inventario.index')->with('success', 'Producto agregado en el catálogo general.');
    }
}