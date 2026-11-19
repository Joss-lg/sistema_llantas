<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveContextoSucursal;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Services\InventarioQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    use ResuelveContextoSucursal;

    /** Tipos de artículo permitidos. Son los mismos botones de categoría de la lista. */
    public const TIPOS = ['Llanta', 'Rin', 'Accesorio', 'Servicio'];

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

        // Base: sucursal, categoría, marca, búsqueda y "llegó hoy".
        // Las tarjetas cuentan TODO esto (no solo la página que se está viendo).
        $base = $this->inventarioQuery->query($request, $sucursalFiltro, conStockMinimo: true);

        if ($request->filled('solo_nuevos') && $request->solo_nuevos == '1') {
            if (count($productosNuevosHoy) > 0) {
                $base->whereIn('productos.id', $productosNuevosHoy);
            } else {
                $base->whereRaw('1 = 0');
            }
        }

        // UNA sola regla de stock para tarjetas, filtro y tabla:
        //   Sin stock  = 0 piezas (o menos)
        //   Bajo mínimo = tiene piezas, pero menos que su mínimo
        //   En stock   = tiene piezas y llega al mínimo (igual al mínimo ya cuenta como En stock)
        $stock  = 'COALESCE(stock_cantidad, 0)';
        $minimo = 'COALESCE(stock_minimo, 5)';

        $conteo = DB::query()
            ->fromSub(clone $base, 'p')
            ->selectRaw("COUNT(*) AS total,
                COALESCE(SUM(CASE WHEN {$stock} <= 0 THEN 1 ELSE 0 END), 0) AS sin_stock,
                COALESCE(SUM(CASE WHEN {$stock} > 0 AND {$stock} < {$minimo} THEN 1 ELSE 0 END), 0) AS bajo_stock,
                COALESCE(SUM(CASE WHEN {$stock} > 0 AND {$stock} >= {$minimo} THEN 1 ELSE 0 END), 0) AS en_stock")
            ->first();

        $totalProductos = (int) $conteo->total;
        $sinStock       = (int) $conteo->sin_stock;
        $bajoStock      = (int) $conteo->bajo_stock;
        $enStock        = (int) $conteo->en_stock;

        // Lista: la base más el filtro de estado del stock y el orden
        $query = clone $base;

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

        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'sin_stock':
                    $query->havingRaw("{$stock} <= 0");
                    break;
                case 'bajo_stock':
                    $query->havingRaw("{$stock} > 0 AND {$stock} < {$minimo}");
                    break;
                case 'ok':
                    $query->havingRaw("{$stock} > 0 AND {$stock} >= {$minimo}");
                    break;
            }
        }

        // Ordenamiento por precios (siempre desempata por id para que las páginas no se mezclen)
        switch ($request->input('ordenar_precio')) {
            case 'costo_mayor':   $query->orderBy('costo', 'desc'); break;
            case 'costo_menor':   $query->orderBy('costo', 'asc'); break;
            case 'publico_mayor': $query->orderBy('precio_publico', 'desc'); break;
            case 'publico_menor': $query->orderBy('precio_publico', 'asc'); break;
            case 'mayoreo_mayor': $query->orderBy('precio_mayoreo', 'desc'); break;
            case 'mayoreo_menor': $query->orderBy('precio_mayoreo', 'asc'); break;
            default:              $query->orderBy('marca')->orderBy('medida'); break;
        }
        $query->orderBy('productos.id');

        $productos         = $query->paginate(10)->withQueryString();
        $sucursales        = $this->sucursalesDisponibles();
        $marcasDisponibles = $this->inventarioQuery->marcasDisponibles();
        $tipos             = self::TIPOS;
        $sucursalNombre    = $sucursalFiltro
            ? optional($sucursales->firstWhere('id', $sucursalFiltro))->nombre
            : null;

        // Catálogo para buscar el producto en la ventana "Registrar entrada"
        $catalogo = Producto::orderBy('marca')->orderBy('medida')
            ->get(['id', 'marca', 'medida', 'descripcion'])
            ->map(function ($p) {
                $nombre = trim((string) $p->descripcion) !== ''
                    ? trim($p->descripcion)
                    : trim($p->marca . ' ' . $p->medida);
                $texto = $nombre . (trim((string) $p->medida) !== '' ? ' (' . trim($p->medida) . ')' : '');

                return [
                    'id' => $p->id,
                    't'  => $texto,
                    'b'  => mb_strtolower($texto . ' ' . $p->marca . ' ' . $p->medida),
                ];
            })
            ->values()
            ->all();

        return view('inventario.index', compact(
            'productos',
            'sucursales',
            'sucursalNombre',
            'marcasDisponibles',
            'productosNuevosHoy',
            'tipos',
            'catalogo',
            'totalProductos',
            'sinStock',
            'bajoStock',
            'enStock'
        ));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'tipo'        => ['required', Rule::in(self::TIPOS)],
            'marca'       => ['required', 'string', 'max:100'],
            'medida'      => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ], [
            'tipo.required'    => 'Elige el tipo de artículo.',
            'tipo.in'          => 'Elige un tipo de la lista.',
            'marca.required'   => 'Escribe la marca.',
            'marca.max'        => 'La marca es muy larga (máximo 100 letras).',
            'medida.required'  => 'Escribe la medida.',
            'medida.max'       => 'La medida es muy larga (máximo 100 letras).',
            'descripcion.max'  => 'La descripción es muy larga (máximo 1000 letras).',
        ]);

        $marca  = mb_strtoupper(trim($datos['marca']));
        $medida = mb_strtoupper(trim($datos['medida']));

        // Misma marca y medida = mismo producto (igual que hace la importación de Excel)
        $yaExiste = Producto::whereRaw('UPPER(TRIM(marca)) = ?', [$marca])
            ->whereRaw('UPPER(TRIM(medida)) = ?', [$medida])
            ->exists();

        if ($yaExiste) {
            return back()
                ->withInput()
                ->withErrors(['medida' => "Ya existe un producto con la marca {$marca} y la medida {$medida}. Búscalo en la lista y registra una entrada de stock."]);
        }

        DB::transaction(function () use ($datos, $marca, $medida) {
            $producto = Producto::create([
                'tipo'           => $datos['tipo'],
                'marca'          => $marca,
                'medida'         => $medida,
                'descripcion'    => $datos['descripcion'] ?? null,
                'costo'          => 0,
                'precio_mayoreo' => 0,
                'precio_publico' => 0,
                'estado'         => true,
            ]);

            $sucursales = Sucursal::all();
            foreach ($sucursales as $sucursal) {
                $producto->sucursales()->attach($sucursal->id, [
                    'cantidad'     => 0,
                    'stock_minimo' => 5,
                ]);
            }
        });

        return redirect()->route('inventario.index')
                         ->with('success', 'Producto agregado en el catálogo general e inicializado en sucursales.');
    }
}
