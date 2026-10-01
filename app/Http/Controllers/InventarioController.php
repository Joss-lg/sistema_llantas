<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use App\Models\Sucursal;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class InventarioController extends Controller
{
    /**
     * Listado principal del inventario con filtros.
     */
    public function index(Request $request)
    {
        $query = Producto::query();

        // ── Filtro sucursal ──────────────────────────────────────────────
        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        // ── Filtro tipo/categoría ────────────────────────────────────────
        if ($request->filled('tipo') && $request->tipo !== 'Todos') {
            $query->where('tipo', $request->tipo);
        }

        // ── Búsqueda libre ───────────────────────────────────────────────
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('descripcion', 'like', "%{$q}%")
                    ->orWhere('marca',      'like', "%{$q}%")
                    ->orWhere('medida',     'like', "%{$q}%");
            });
        }

        // ── Filtro estado de stock ───────────────────────────────────────
        if ($request->filled('stock_status')) {
            match ($request->stock_status) {
                'sin_stock'  => $query->where('stock_cantidad', '<=', 0),
                'bajo_stock' => $query->whereRaw('stock_cantidad > 0 AND stock_cantidad <= stock_minimo'),
                'ok'         => $query->whereRaw('stock_cantidad > stock_minimo'),
                default      => null,
            };
        }

        // ── Filtro marca ─────────────────────────────────────────────────
        if ($request->filled('marca_filtro') && $request->marca_filtro !== 'Todas') {
            $query->where('marca', $request->marca_filtro);
        }

        // ── Ordenamiento por precio ──────────────────────────────────────
        match ($request->ordenar_precio) {
            'publico_mayor'  => $query->orderByDesc('precio_publico'),
            'publico_menor'  => $query->orderBy('precio_publico'),
            'mayoreo_mayor'  => $query->orderByDesc('precio_mayoreo'),
            'mayoreo_menor'  => $query->orderBy('precio_mayoreo'),
            'costo_mayor'    => $query->orderByDesc('costo'),
            'costo_menor'    => $query->orderBy('costo'),
            default          => $query->orderBy('descripcion'),
        };

        // ── Solo los llegados hoy ────────────────────────────────────────
        $productosNuevosHoy = [];
        if ($request->solo_nuevos == '1') {
            $idsHoy = MovimientoInventario::whereDate('created_at', today())
                ->where('tipo', 'entrada')
                ->pluck('producto_id')
                ->unique()
                ->toArray();
            $query->whereIn('id', $idsHoy);
            $productosNuevosHoy = $idsHoy;
        } else {
            $productosNuevosHoy = MovimientoInventario::whereDate('created_at', today())
                ->where('tipo', 'entrada')
                ->pluck('producto_id')
                ->unique()
                ->toArray();
        }

        // ── Paginación ───────────────────────────────────────────────────
        $productos = $query->paginate(20)->withQueryString();

        // ── Contadores para tarjetas de resumen ──────────────────────────
        $todosLosProductos = Producto::query();
        if ($request->filled('sucursal_id')) {
            $todosLosProductos->where('sucursal_id', $request->sucursal_id);
        }
        $base = $todosLosProductos->get();

        $sinStock  = $base->filter(fn($p) => ($p->stock_cantidad ?? 0) <= 0)->count();
        $bajoStock = $base->filter(fn($p) => ($p->stock_cantidad ?? 0) > 0 && ($p->stock_cantidad ?? 0) <= ($p->stock_minimo ?? 5))->count();
        $enStock   = $base->filter(fn($p) => ($p->stock_cantidad ?? 0) > ($p->stock_minimo ?? 5))->count();

        // ── Datos para filtros ───────────────────────────────────────────
        $sucursales       = Sucursal::orderBy('nombre')->get();
        $marcasDisponibles = Producto::whereNotNull('marca')
                                     ->distinct()
                                     ->orderBy('marca')
                                     ->pluck('marca');

        return view('inventario.index', compact(
            'productos',
            'sucursales',
            'marcasDisponibles',
            'productosNuevosHoy',
            'sinStock',
            'bajoStock',
            'enStock'
        ));
    }

    /**
     * Guarda un nuevo producto al catálogo.
     */
    public function storeProducto(Request $request)
    {
        $request->validate([
            'tipo'      => 'required|string|max:100',
            'marca'     => 'required|string|max:100',
            'medida'    => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:500',
        ]);

        Producto::create([
            'tipo'        => $request->tipo,
            'marca'       => $request->marca,
            'medida'      => $request->medida,
            'descripcion' => $request->descripcion ?? "{$request->marca} {$request->medida}",
            'stock_cantidad' => 0,
            'stock_minimo'   => 5,
            'costo'          => 0,
            'precio_publico' => 0,
            'precio_mayoreo' => 0,
        ]);

        return redirect()->route('inventario.index')
                         ->with('success', 'Producto registrado correctamente.');
    }

    /**
     * Registra una entrada de stock.
     */
    public function storeEntrada(Request $request)
    {
        $request->validate([
            'producto_id'   => 'required|exists:productos,id',
            'cantidad'      => 'required|integer|min:1',
            'costo_unitario' => 'required|numeric|min:0',
        ]);

        $producto = Producto::findOrFail($request->producto_id);

        // Actualizar precios si vienen en el formulario
        $producto->costo          = $request->costo_unitario;
        if ($request->filled('precio_publico')) {
            $producto->precio_publico = $request->precio_publico;
        }
        if ($request->filled('precio_mayoreo')) {
            $producto->precio_mayoreo = $request->precio_mayoreo;
        }
        $producto->stock_cantidad = ($producto->stock_cantidad ?? 0) + $request->cantidad;
        $producto->save();

        // Registrar movimiento
        MovimientoInventario::create([
            'producto_id'    => $producto->id,
            'sucursal_id'    => $request->sucursal_id ?? auth()->user()->sucursal_id,
            'tipo'           => 'entrada',
            'cantidad'       => $request->cantidad,
            'costo_unitario' => $request->costo_unitario,
            'user_id'        => auth()->id(),
            'notas'          => 'Entrada manual desde inventario',
        ]);

        return redirect()->route('inventario.index')
                         ->with('success', "Entrada de {$request->cantidad} unidad(es) registrada correctamente.");
    }

    /**
     * Vista del historial de movimientos.
     */
    public function historial(Request $request)
    {
        $query = MovimientoInventario::with(['producto', 'sucursal', 'user'])
                                     ->latest();

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }

        $movimientos = $query->paginate(25)->withQueryString();

        $totalEntradas  = $query->clone()->where('tipo', 'entrada')->count();
        $totalSalidas   = $query->clone()->where('tipo', 'salida')->count();
        $totalMovimientos = $totalEntradas + $totalSalidas;

        $sucursales = Sucursal::orderBy('nombre')->get();

        return view('inventario.historial', compact(
            'movimientos',
            'sucursales',
            'totalEntradas',
            'totalSalidas',
            'totalMovimientos'
        ));
    }

    /**
     * Vista para importar productos desde Excel.
     */
    public function importar()
    {
        return view('inventario.importar');
    }

    /**
     * Procesa la importación del archivo Excel.
     */
    public function procesarImportacion(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            Excel::import(new \App\Imports\ProductosImport, $request->file('archivo'));

            return redirect()->route('inventario.index')
                             ->with('success', 'Importación completada correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                             ->with('error', 'Error al importar: ' . $e->getMessage());
        }
    }

    /**
     * Exportar inventario a Excel.
     */
    public function exportarExcel(Request $request)
    {
        $filename = 'inventario_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new \App\Exports\InventarioExport($request->all()), $filename);
    }

    /**
     * Exportar inventario a PDF.
     */
    public function exportarPdf(Request $request)
    {
        $query = Producto::query();

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }
        if ($request->filled('tipo') && $request->tipo !== 'Todos') {
            $query->where('tipo', $request->tipo);
        }

        $productos = $query->orderBy('descripcion')->get();

        $pdf = Pdf::loadView('inventario.pdf', compact('productos'))
                  ->setPaper('letter', 'landscape');

        return $pdf->download('inventario_' . now()->format('Ymd') . '.pdf');
    }
}