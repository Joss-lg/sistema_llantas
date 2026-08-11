<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveContextoSucursal;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Services\InventarioQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    use ResuelveContextoSucursal;

    public function __construct(private InventarioQueryService $inventarioQuery)
    {
    }

    public function index(Request $request)
    {
        $sucursalFiltro = $this->sucursalSeleccionada($request);

        // IDs de productos con entrada hoy
        $productosNuevosHoy = MovimientoInventario::where('tipo', 'entrada')
            ->whereDate('fecha', today())
            ->pluck('producto_id')
            ->unique()
            ->toArray();

        $query = $this->inventarioQuery->query($request, $sucursalFiltro, conStockMinimo: true);

        // CORRECCIÓN: Agrupar por todas las columnas de la tabla productos para ser compatible con ONLY_FULL_GROUP_BY
        $query->groupBy([
            'productos.id',
            'productos.tipo',
            'productos.marca',
            'productos.medida',
            'productos.descripcion',
            'productos.costo',
            'productos.precio_mayoreo',
            'productos.precio_publico',
            'productos.estado',
            'productos.created_at',
            'productos.updated_at',
        ]);

        // Filtro solo nuevos hoy
        if ($request->filled('solo_nuevos') && $request->solo_nuevos == '1') {
            if (count($productosNuevosHoy) > 0) {
                $query->whereIn('productos.id', $productosNuevosHoy);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Filtros de estado de stock
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'sin_stock':
                    $query->havingRaw('COALESCE(stock_cantidad, 0) = 0');
                    break;
                case 'bajo_stock':
                    $query->havingRaw('COALESCE(stock_cantidad, 0) > 0 AND COALESCE(stock_cantidad, 0) < COALESCE(stock_minimo, 5)');
                    break;
                case 'ok':
                    $query->havingRaw('COALESCE(stock_cantidad, 0) >= COALESCE(stock_minimo, 5)');
                    break;
            }
        }

        // Ordenamiento por precios
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
            'tipo'   => 'required|string',
            'marca'  => 'required|string|max:100',
            'medida' => 'required|string|max:100',
        ]);

        DB::transaction(function () use ($request) {
            $producto = Producto::create([
                'tipo'           => $request->tipo,
                'marca'          => mb_strtoupper($request->marca),
                'medida'         => mb_strtoupper($request->medida),
                'descripcion'    => $request->descripcion,
                'costo'          => 0,
                'precio_mayoreo' => 0,
                'precio_publico' => 0,
                'estado'         => true,
            ]);

            // Se generan automáticamente las relaciones de stock inicial en 0 para todas las sucursales
            $sucursales = Sucursal::all();
            foreach ($sucursales as $sucursal) {
                $producto->sucursales()->attach($sucursal->id, [
                    'cantidad'     => 0,
                    'stock_minimo' => 5,
                ]);
            }
        });

        return redirect()->route('inventario.index')->with('success', 'Producto agregado en el catálogo general e inicializado en sucursales.');
    }
}
