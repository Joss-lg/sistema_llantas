<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\StockSucursal;
use App\Models\Sucursal;
use App\Models\MovimientoInventario;
use App\Exports\InventarioExport;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user();
        $esAdmin = $usuario->id === 1 || $usuario->tieneRol('Administrador General');
        $sucursalUsuario = $usuario->sucursal_id ?? 1;
        $sucursalFiltro = $esAdmin ? $request->input('sucursal_id') : $sucursalUsuario;

        // IDs de productos que tuvieron una ENTRADA HOY (para la etiqueta "NUEVO" y el filtro)
        $productosNuevosHoy = MovimientoInventario::where('tipo', 'entrada')
            ->whereDate('fecha', today())
            ->pluck('producto_id')
            ->unique()
            ->toArray();

        $query = Producto::query();

        $marcasDisponibles = Producto::select('marca')
            ->whereNotNull('marca')->where('marca', '!=', '')
            ->distinct()->orderBy('marca')->pluck('marca');

        if ($sucursalFiltro) {
            $query->withSum(['stock as stock_cantidad' => function($q) use ($sucursalFiltro) {
                $q->where('sucursal_id', $sucursalFiltro);
            }], 'cantidad');
            $query->addSelect(['stock_minimo' => StockSucursal::select('stock_minimo')
                ->whereColumn('producto_id', 'productos.id')
                ->where('sucursal_id', $sucursalFiltro)->limit(1)
            ]);
        } else {
            $query->withSum('stock as stock_cantidad', 'cantidad');
            $query->addSelect(['stock_minimo' => StockSucursal::select('stock_minimo')
                ->whereColumn('producto_id', 'productos.id')->limit(1)
            ]);
        }

        // FILTRO: solo los que llegaron hoy
        if ($request->filled('solo_nuevos') && $request->solo_nuevos == '1') {
            if (count($productosNuevosHoy) > 0) {
                $query->whereIn('id', $productosNuevosHoy);
            } else {
                $query->whereRaw('1 = 0'); // Nada llegó hoy: lista vacía
            }
        }

        if ($request->filled('tipo') && $request->tipo !== 'Todos') {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('marca_filtro') && $request->marca_filtro !== 'Todas') {
            $query->where('marca', $request->marca_filtro);
        }
        if ($request->filled('q')) {
            $query->where(function($q) use ($request) {
                $q->where('marca', 'like', '%' . $request->q . '%')
                  ->orWhere('medida', 'like', '%' . $request->q . '%')
                  ->orWhere('descripcion', 'like', '%' . $request->q . '%');
            });
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
        $sucursales = $esAdmin ? Sucursal::all() : Sucursal::where('id', $sucursalUsuario)->get();

        return view('inventario.index', compact('productos', 'sucursales', 'marcasDisponibles', 'productosNuevosHoy'));
    }

    public function importar()
    {
        return view('inventario.importar');
    }

    public function procesarImportacion(Request $request)
    {
        $request->validate(['archivo_excel' => 'required|mimes:xlsx,xls,csv,txt|max:10240']);

        try {
            $usuario = Auth::user();
            $sucursalUsuario = $usuario->sucursal_id ?? 1;
            $sucursal_destino = $request->input('sucursal_id', $sucursalUsuario);

            $path = $request->file('archivo_excel')->path();
            $coleccion = (new FastExcel)->withoutHeaders()->import($path);
            $productosProcesados = 0;
            DB::beginTransaction();
            $indices = null;

            foreach ($coleccion as $fila) {
                $valores = array_values($fila);
                if (!$indices) {
                    $tempIndices = [];
                    foreach ($valores as $k => $v) {
                        if ($v === null || trim((string)$v) === '') continue;
                        $valStr = strtolower(preg_replace('/[\s\r\n]+/', '', str_replace(['á','é','í','ó','ú'], ['a','e','i','o','u'], trim((string)$v))));
                        if (str_contains($valStr, 'descrip')) $tempIndices['descripcion'] = $k;
                        elseif (str_contains($valStr, 'categ') || str_contains($valStr, 'tipo')) $tempIndices['categoria'] = $k;
                        elseif (str_contains($valStr, 'mayor') || str_contains($valStr, 'costo')) $tempIndices['mayoreo'] = $k;
                        elseif (str_contains($valStr, 'public') || str_contains($valStr, 'menude')) $tempIndices['publico'] = $k;
                        elseif (str_contains($valStr, 'stock') || str_contains($valStr, 'actual') || str_contains($valStr, 'cantid')) $tempIndices['stock'] = $k;
                    }
                    if (isset($tempIndices['descripcion']) && (isset($tempIndices['mayoreo']) || isset($tempIndices['publico']))) { $indices = $tempIndices; }
                    continue;
                }

                $descIndex = $indices['descripcion'] ?? -1;
                $descripcion = ($descIndex !== -1 && isset($valores[$descIndex])) ? $valores[$descIndex] : '';
                if (empty(trim((string)$descripcion))) continue;

                $catIndex = $indices['categoria'] ?? -1; $mayIndex = $indices['mayoreo'] ?? -1; $pubIndex = $indices['publico'] ?? -1;
                $categoria = ($catIndex !== -1 && isset($valores[$catIndex])) ? $valores[$catIndex] : 'Llanta';
                $precioMay = ($mayIndex !== -1 && isset($valores[$mayIndex])) ? $valores[$mayIndex] : 0;
                $precioPub = ($pubIndex !== -1 && isset($valores[$pubIndex])) ? $valores[$pubIndex] : 0;

                $stockRaw = null;
                if (isset($indices['stock']) && trim((string)($valores[$indices['stock']] ?? '')) !== '') {
                    $stockRaw = $valores[$indices['stock']];
                } else {
                    $valoresAlReves = array_reverse($valores);
                    foreach ($valoresAlReves as $val) {
                        $limpio = trim((string)$val);
                        if ($limpio !== '' && is_numeric(preg_replace('/[^0-9\.]/', '', $limpio))) { $stockRaw = $limpio; break; }
                    }
                }

                $precioMay = (float) preg_replace('/[^0-9\.]/', '', (string)$precioMay);
                $precioPub = (float) preg_replace('/[^0-9\.]/', '', (string)$precioPub);
                $stockLimpio = preg_replace('/[^0-9\.]/', '', (string)$stockRaw);
                $stock = (int) round((float)($stockLimpio ?: 0));

                $medida = 'S/M'; $marca = trim((string)$descripcion);
                $partes = explode(' ', $marca, 2);
                if (count($partes) > 1 && preg_match('/[0-9]/', $partes[0])) { $medida = $partes[0]; $marca = $partes[1]; }

                $producto = Producto::create([
                    'tipo' => mb_substr((string)$categoria, 0, 50),
                    'marca' => mb_strtoupper(mb_substr((string)$marca, 0, 100)),
                    'medida' => mb_strtoupper(mb_substr((string)$medida, 0, 100)),
                    'descripcion' => mb_substr((string)$descripcion, 0, 255),
                    'costo' => $precioMay, 'precio_mayoreo' => $precioMay, 'precio_publico' => $precioPub, 'estado' => true
                ]);

                StockSucursal::create(['producto_id' => $producto->id, 'sucursal_id' => $sucursal_destino, 'cantidad' => $stock, 'stock_minimo' => 5]);
                $productosProcesados++;
            }
            DB::commit();
            if ($productosProcesados === 0) { return back()->with('error', 'No detectamos productos válidos.'); }
            return redirect()->route('inventario.index')->with('success', "¡Éxito! Se importaron $productosProcesados productos en la sucursal correspondiente.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function storeProducto(Request $request)
    {
        $request->validate(['tipo' => 'required|string', 'marca' => 'required|string|max:100', 'medida' => 'required|string|max:100']);
        Producto::create(['tipo' => $request->tipo, 'marca' => mb_strtoupper($request->marca), 'medida' => mb_strtoupper($request->medida), 'descripcion' => $request->descripcion, 'costo' => 0, 'precio_mayoreo' => 0, 'precio_publico' => 0, 'estado' => true]);
        return redirect()->route('inventario.index')->with('success', 'Producto agregado en el catálogo general.');
    }

    public function storeEntrada(Request $request)
    {
        $request->validate(['producto_id' => 'required|exists:productos,id', 'cantidad' => 'required|integer|min:1', 'costo_unitario' => 'required|numeric|min:0']);

        try {
            DB::beginTransaction();

            $usuario = Auth::user();
            $sucursalUsuario = $usuario->sucursal_id ?? 1;
            $sucursal_destino = $request->input('sucursal_id', $sucursalUsuario);

            $producto = Producto::findOrFail($request->producto_id);
            $producto->update(['costo' => $request->costo_unitario]);

            $stock = StockSucursal::firstOrCreate(
                ['producto_id' => $producto->id, 'sucursal_id' => $sucursal_destino],
                ['cantidad' => 0, 'stock_minimo' => 5]
            );
            $stock->increment('cantidad', $request->cantidad);

            MovimientoInventario::create([
                'producto_id'    => $producto->id,
                'sucursal_id'    => $sucursal_destino,
                'usuario_id'     => Auth::id(),
                'tipo'           => 'entrada',
                'cantidad'       => $request->cantidad,
                'motivo'         => 'compra',
                'costo_unitario' => $request->costo_unitario,
                'fecha'          => now(),
            ]);

            DB::commit();
            return redirect()->route('inventario.index')->with('success', "Entrada registrada en tu bodega exitosamente.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function storeSalida(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad'    => 'required|integer|min:1',
            'motivo'      => 'required|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            $usuario = Auth::user();
            $sucursalUsuario = $usuario->sucursal_id ?? 1;
            $sucursal_destino = $request->input('sucursal_id', $sucursalUsuario);

            $stock = StockSucursal::where('producto_id', $request->producto_id)
                ->where('sucursal_id', $sucursal_destino)
                ->first();

            if (!$stock || $stock->cantidad < $request->cantidad) {
                DB::rollBack();
                $disponible = $stock ? $stock->cantidad : 0;
                return redirect()->back()->with('error', "Stock insuficiente. Disponible: {$disponible} pzas.");
            }

            $stock->decrement('cantidad', $request->cantidad);

            MovimientoInventario::create([
                'producto_id'   => $request->producto_id,
                'sucursal_id'   => $sucursal_destino,
                'usuario_id'    => Auth::id(),
                'tipo'          => 'salida',
                'cantidad'      => $request->cantidad,
                'motivo'        => $request->motivo,
                'observaciones' => $request->observaciones,
                'fecha'         => now(),
            ]);

            DB::commit();
            return redirect()->route('inventario.index')->with('success', "Salida por {$request->motivo} registrada correctamente.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function historial(Request $request)
    {
        $usuario = Auth::user();
        $esAdmin = $usuario->id === 1 || $usuario->tieneRol('Administrador General');
        $sucursalUsuario = $usuario->sucursal_id ?? 1;

        $query = MovimientoInventario::with(['producto', 'sucursal']);

        if (!$esAdmin) {
            $query->where('sucursal_id', $sucursalUsuario);
        } elseif ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        if ($request->filled('tipo') && in_array($request->tipo, ['entrada', 'salida'])) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('motivo')) {
            $query->where('motivo', $request->motivo);
        }
        if ($request->filled('q')) {
            $busqueda = $request->q;
            $query->whereHas('producto', function($q) use ($busqueda) {
                $q->where('marca', 'like', '%' . $busqueda . '%')
                  ->orWhere('medida', 'like', '%' . $busqueda . '%')
                  ->orWhere('descripcion', 'like', '%' . $busqueda . '%');
            });
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        $totalEntradas = (clone $query)->where('tipo', 'entrada')->sum('cantidad');
        $totalSalidas = (clone $query)->where('tipo', 'salida')->sum('cantidad');
        $totalMovimientos = (clone $query)->count();

        $movimientos = $query->orderBy('fecha', 'desc')->paginate(20)->withQueryString();
        $sucursales = $esAdmin ? Sucursal::all() : Sucursal::where('id', $sucursalUsuario)->get();

        $motivosDisponibles = MovimientoInventario::select('motivo')
            ->whereNotNull('motivo')->where('motivo', '!=', '')
            ->distinct()->orderBy('motivo')->pluck('motivo');

        return view('inventario.historial', compact(
            'movimientos', 'sucursales', 'esAdmin',
            'totalEntradas', 'totalSalidas', 'totalMovimientos', 'motivosDisponibles'
        ));
    }

    private function construirQueryExport(Request $request)
    {
        $usuario = Auth::user();
        $esAdmin = $usuario->id === 1 || $usuario->tieneRol('Administrador General');
        $sucursalUsuario = $usuario->sucursal_id ?? 1;
        $sucursalFiltro = $esAdmin ? $request->input('sucursal_id') : $sucursalUsuario;

        $query = Producto::query();

        if ($sucursalFiltro) {
            $query->withSum(['stock as stock_cantidad' => function($q) use ($sucursalFiltro) {
                $q->where('sucursal_id', $sucursalFiltro);
            }], 'cantidad');
        } else {
            $query->withSum('stock as stock_cantidad', 'cantidad');
        }

        if ($request->filled('tipo') && $request->tipo !== 'Todos') {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('marca_filtro') && $request->marca_filtro !== 'Todas') {
            $query->where('marca', $request->marca_filtro);
        }
        if ($request->filled('q')) {
            $query->where(function($q) use ($request) {
                $q->where('marca', 'like', '%' . $request->q . '%')
                  ->orWhere('medida', 'like', '%' . $request->q . '%')
                  ->orWhere('descripcion', 'like', '%' . $request->q . '%');
            });
        }

        $query->orderBy('marca')->orderBy('medida');
        return $query->get();
    }

    public function exportarExcel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);
        $productos = $this->construirQueryExport($request);
        $nombre = 'inventario_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new InventarioExport($productos), $nombre);
    }

    public function exportarPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);
        $productos = $this->construirQueryExport($request);
        $fecha = now()->format('d/m/Y H:i');
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('inventario.pdf', compact('productos', 'fecha'));
        $pdf->setPaper('a4', 'portrait');
        $nombre = 'inventario_' . now()->format('Y-m-d_His') . '.pdf';
        return $pdf->download($nombre);
    }

    public function traspasarStock(Request $request)
    {
        $request->validate([
            'producto_id'      => 'required|exists:productos,id',
            'sucursal_origen'  => 'required|exists:sucursales,id',
            'sucursal_destino' => 'required|exists:sucursales,id|different:sucursal_origen',
            'cantidad'         => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $producto = Producto::findOrFail($request->producto_id);
            $cantidadATraspasar = $request->cantidad;

            // 1. Buscar y descontar de la sucursal origen
            $stockOrigen = StockSucursal::where('producto_id', $producto->id)
                ->where('sucursal_id', $request->sucursal_origen)
                ->first();

            if (!$stockOrigen || $stockOrigen->cantidad < $cantidadATraspasar) {
                DB::rollBack();
                $disponible = $stockOrigen ? $stockOrigen->cantidad : 0;
                return redirect()->back()->with('error', "Stock insuficiente en origen. Disponible: {$disponible} pzas.");
            }

            $stockOrigen->decrement('cantidad', $cantidadATraspasar);

            // Registrar movimiento de SALIDA en sucursal origen
            MovimientoInventario::create([
                'producto_id'   => $producto->id,
                'sucursal_id'   => $request->sucursal_origen,
                'usuario_id'    => Auth::id(),
                'tipo'          => 'salida',
                'cantidad'      => $cantidadATraspasar,
                'motivo'        => 'traspaso',
                'observaciones' => 'Traspaso enviado a sucursal ID: ' . $request->sucursal_destino,
                'fecha'         => now(),
            ]);

            // 2. Sumar a la sucursal destino (o crear el registro si no existe)
            $stockDestino = StockSucursal::firstOrCreate(
                ['producto_id' => $producto->id, 'sucursal_id' => $request->sucursal_destino],
                ['cantidad' => 0, 'stock_minimo' => 5]
            );

            $stockDestino->increment('cantidad', $cantidadATraspasar);

            // Registrar movimiento de ENTRADA en sucursal destino
            MovimientoInventario::create([
                'producto_id'    => $producto->id,
                'sucursal_id'    => $request->sucursal_destino,
                'usuario_id'     => Auth::id(),
                'tipo'           => 'entrada',
                'cantidad'       => $cantidadATraspasar,
                'motivo'         => 'traspaso',
                'costo_unitario' => $producto->costo,
                'observaciones'  => 'Traspaso recibido de sucursal ID: ' . $request->sucursal_origen,
                'fecha'          => now(),
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Traspaso de llantas realizado con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al realizar el traspaso: ' . $e->getMessage());
        }
    }

    public function disponibilidad(Producto $producto, Request $request)
    {
        $usuario = Auth::user();
        $esAdmin = $usuario->id === 1 || $usuario->tieneRol('Administrador General');
        $sucursalUsuario = $usuario->sucursal_id ?? 1;

        // El admin puede estar viendo el filtro "Todas" o una sucursal específica.
        // El empleado normal siempre consulta desde la suya.
        $sucursalActual = $esAdmin
            ? $request->input('sucursal_id', $sucursalUsuario)
            : $sucursalUsuario;

        $otrasSucursales = StockSucursal::with('sucursal')
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