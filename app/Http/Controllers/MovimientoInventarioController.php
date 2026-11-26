<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveContextoSucursal;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\StockSucursal;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MovimientoInventarioController extends Controller
{
    use ResuelveContextoSucursal;

    public function storeEntrada(Request $request)
    {
        $request->validate([
            'producto_id'    => 'required|exists:productos,id',
            'cantidad'       => 'required|integer|min:1',
            'costo_unitario' => 'required|numeric|min:0',
            'precio_publico' => 'nullable|numeric|min:0',
            'precio_mayoreo' => 'nullable|numeric|min:0',
            'sucursal_id'    => 'nullable|exists:sucursales,id',
        ], [
            'producto_id.required'    => 'Elige un producto del catálogo.',
            'producto_id.exists'      => 'El producto elegido ya no existe.',
            'cantidad.required'       => 'Escribe la cantidad.',
            'cantidad.integer'        => 'La cantidad debe ser un número entero.',
            'cantidad.min'            => 'La cantidad debe ser al menos 1.',
            'costo_unitario.required' => 'Escribe el costo unitario.',
            'costo_unitario.numeric'  => 'El costo debe ser un número.',
            'costo_unitario.min'      => 'El costo no puede ser negativo.',
            'precio_publico.numeric'  => 'El precio al público debe ser un número.',
            'precio_publico.min'      => 'El precio al público no puede ser negativo.',
            'precio_mayoreo.numeric'  => 'El precio de mayoreo debe ser un número.',
            'precio_mayoreo.min'      => 'El precio de mayoreo no puede ser negativo.',
            'sucursal_id.exists'      => 'La sucursal elegida no existe.',
        ]);

        // Admin: la sucursal que eligió. Empleado: SIEMPRE la suya.
        $sucursalDestino = $this->sucursalParaOperar($request);
        if (!$sucursalDestino) {
            return redirect()->back()->with('error', 'Tu usuario no tiene una sucursal asignada.');
        }

        try {
            DB::beginTransaction();

            $producto = Producto::findOrFail($request->producto_id);

            // CORRECCIÓN: ahora sí se actualizan precio_publico y precio_mayoreo
            // si el usuario los capturó en el formulario de "Registrar entrada".
            // Si no los captura, se conserva el precio que ya tenía el producto.
            $producto->update([
                'costo'          => $request->costo_unitario,
                'precio_publico' => $request->filled('precio_publico')
                    ? $request->precio_publico
                    : $producto->precio_publico,
                'precio_mayoreo' => $request->filled('precio_mayoreo')
                    ? $request->precio_mayoreo
                    : $producto->precio_mayoreo,
            ]);

            $stock = StockSucursal::firstOrCreate(
                ['producto_id' => $producto->id, 'sucursal_id' => $sucursalDestino],
                ['cantidad' => 0, 'stock_minimo' => 5]
            );
            $stock->increment('cantidad', $request->cantidad);

            MovimientoInventario::create([
                'producto_id'    => $producto->id,
                'sucursal_id'    => $sucursalDestino,
                'usuario_id'     => Auth::id(),
                'tipo'           => 'entrada',
                'cantidad'       => $request->cantidad,
                'motivo'         => 'compra',
                'costo_unitario' => $request->costo_unitario,
                'fecha'          => now(),
            ]);

            DB::commit();
            return redirect()->route('inventario.index')->with('success', 'Entrada registrada y precios actualizados con éxito.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Inventario: no se pudo registrar la entrada', [
                'usuario_id'  => Auth::id(),
                'producto_id' => $request->producto_id,
                'sucursal_id' => $sucursalDestino,
                'exception'   => $e,
            ]);
            return redirect()->back()->withInput()->with('error', 'No se pudo registrar la entrada. Inténtalo de nuevo; si el problema sigue, avisa al administrador.');
        }
    }

    public function storeSalida(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad'    => 'required|integer|min:1',
            'motivo'      => 'required|string|max:100',
            'sucursal_id' => 'nullable|exists:sucursales,id',
        ]);

        // Admin: la sucursal que eligió. Empleado: SIEMPRE la suya.
        $sucursalDestino = $this->sucursalParaOperar($request);
        if (!$sucursalDestino) {
            return redirect()->back()->with('error', 'Tu usuario no tiene una sucursal asignada.');
        }

        try {
            DB::beginTransaction();

            $stock = StockSucursal::where('producto_id', $request->producto_id)
                ->where('sucursal_id', $sucursalDestino)
                ->lockForUpdate()
                ->first();

            if (!$stock || $stock->cantidad < $request->cantidad) {
                DB::rollBack();
                $disponible = $stock ? $stock->cantidad : 0;
                return redirect()->back()->with('error', "Stock insuficiente. Disponible: {$disponible} pzas.");
            }

            $stock->decrement('cantidad', $request->cantidad);

            MovimientoInventario::create([
                'producto_id'   => $request->producto_id,
                'sucursal_id'   => $sucursalDestino,
                'usuario_id'    => Auth::id(),
                'tipo'          => 'salida',
                'cantidad'      => $request->cantidad,
                'motivo'        => $request->motivo,
                'observaciones' => $request->observaciones,
                'fecha'         => now(),
            ]);

            DB::commit();
            return redirect()->route('inventario.index')->with('success', "Salida por {$request->motivo} registrada correctamente.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Inventario: no se pudo registrar la salida', [
                'usuario_id'  => Auth::id(),
                'producto_id' => $request->producto_id,
                'sucursal_id' => $sucursalDestino,
                'exception'   => $e,
            ]);
            return redirect()->back()->withInput()->with('error', 'No se pudo registrar la salida. Inténtalo de nuevo; si el problema sigue, avisa al administrador.');
        }
    }

    public function traspasarStock(Request $request)
    {
        $request->validate([
            'producto_id'      => 'required|exists:productos,id',
            'sucursal_origen'  => 'required|exists:sucursales,id',
            'sucursal_destino' => 'required|exists:sucursales,id|different:sucursal_origen',
            'cantidad'         => 'required|integer|min:1',
        ]);

        // Un empleado (no admin) solo puede enviar llantas DESDE su propia sucursal
        if (!$this->usuarioEsAdmin() && (int) $request->sucursal_origen !== (int) $this->sucursalDelUsuario()) {
            return redirect()->back()->with('error', 'Solo puedes traspasar stock desde tu propia sucursal.');
        }

        try {
            DB::beginTransaction();

            $producto = Producto::findOrFail($request->producto_id);
            $cantidadATraspasar = $request->cantidad;
            $nombreOrigen = Sucursal::whereKey($request->sucursal_origen)->value('nombre');
            $nombreDestino = Sucursal::whereKey($request->sucursal_destino)->value('nombre');

            $stockOrigen = StockSucursal::where('producto_id', $producto->id)
                ->where('sucursal_id', $request->sucursal_origen)
                ->lockForUpdate()
                ->first();

            if (!$stockOrigen || $stockOrigen->cantidad < $cantidadATraspasar) {
                DB::rollBack();
                $disponible = $stockOrigen ? $stockOrigen->cantidad : 0;
                return redirect()->back()->with('error', "Stock insuficiente en origen. Disponible: {$disponible} pzas.");
            }

            $stockOrigen->decrement('cantidad', $cantidadATraspasar);

            MovimientoInventario::create([
                'producto_id'   => $producto->id,
                'sucursal_id'   => $request->sucursal_origen,
                'usuario_id'    => Auth::id(),
                'tipo'          => 'salida',
                'cantidad'      => $cantidadATraspasar,
                'motivo'        => 'traspaso',
                'observaciones' => 'Traspaso enviado a ' . $nombreDestino,
                'fecha'         => now(),
            ]);

            $stockDestino = StockSucursal::firstOrCreate(
                ['producto_id' => $producto->id, 'sucursal_id' => $request->sucursal_destino],
                ['cantidad' => 0, 'stock_minimo' => 5]
            );
            $stockDestino->increment('cantidad', $cantidadATraspasar);

            MovimientoInventario::create([
                'producto_id'    => $producto->id,
                'sucursal_id'    => $request->sucursal_destino,
                'usuario_id'     => Auth::id(),
                'tipo'           => 'entrada',
                'cantidad'       => $cantidadATraspasar,
                'motivo'         => 'traspaso',
                'costo_unitario' => $producto->costo,
                'observaciones'  => 'Traspaso recibido de ' . $nombreOrigen,
                'fecha'          => now(),
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Traspaso de llantas realizado con éxito.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Inventario: no se pudo realizar el traspaso', [
                'usuario_id'       => Auth::id(),
                'producto_id'      => $request->producto_id,
                'sucursal_origen'  => $request->sucursal_origen,
                'sucursal_destino' => $request->sucursal_destino,
                'exception'        => $e,
            ]);
            return redirect()->back()->withInput()->with('error', 'No se pudo realizar el traspaso. Inténtalo de nuevo; si el problema sigue, avisa al administrador.');
        }
    }

    public function historial(Request $request)
    {
        $esAdmin = $this->usuarioEsAdmin();
        $sucursalUsuario = $this->sucursalDelUsuario();

        // Producto, sucursal y usuario se cargan juntos para no hacer una consulta extra por cada fila
        $query = MovimientoInventario::with(['producto', 'sucursal', 'usuario']);

        if (!$esAdmin) {
            $query->where('sucursal_id', $sucursalUsuario);
        } elseif ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        if ($request->filled('tipo') && in_array($request->tipo, ['entrada', 'salida'])) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('motivo')) {
            if ($request->motivo === 'Venta') {
                // Cada venta guarda su folio en el motivo ("Venta VNT-..."), por eso se juntan todas
                $query->where(function ($q) {
                    $q->where('motivo', 'Venta')->orWhere('motivo', 'like', 'Venta %');
                });
            } else {
                $query->where('motivo', $request->motivo);
            }
        }
        if ($request->filled('q')) {
            $busqueda = $request->q;
            $query->whereHas('producto', function ($q) use ($busqueda) {
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

        // El id desempata movimientos con la misma fecha, para que no se repitan ni se salten entre páginas
        $movimientos = $query->orderBy('fecha', 'desc')->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $sucursales = $this->sucursalesDisponibles();

        // En la lista del filtro todas las ventas aparecen como una sola opción: "Venta"
        $motivosDisponibles = MovimientoInventario::select('motivo')
            ->whereNotNull('motivo')->where('motivo', '!=', '')
            ->where('motivo', '!=', 'Venta')->where('motivo', 'not like', 'Venta %')
            ->distinct()->orderBy('motivo')->pluck('motivo');

        $hayVentas = MovimientoInventario::where('motivo', 'Venta')->orWhere('motivo', 'like', 'Venta %')->exists();
        if ($hayVentas) {
            $motivosDisponibles = $motivosDisponibles->push('Venta')->sort(SORT_NATURAL | SORT_FLAG_CASE)->values();
        }

        return view('inventario.historial', compact(
            'movimientos', 'sucursales', 'esAdmin',
            'totalEntradas', 'totalSalidas', 'totalMovimientos', 'motivosDisponibles'
        ));
    }
}