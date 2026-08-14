<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveContextoSucursal;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\StockSucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        ]);

        try {
            DB::beginTransaction();

            $sucursalDestino = $request->input('sucursal_id', $this->sucursalDelUsuario());

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

            $sucursalDestino = $request->input('sucursal_id', $this->sucursalDelUsuario());

            $stock = StockSucursal::where('producto_id', $request->producto_id)
                ->where('sucursal_id', $sucursalDestino)
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
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
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

        try {
            DB::beginTransaction();

            $producto = Producto::findOrFail($request->producto_id);
            $cantidadATraspasar = $request->cantidad;

            $stockOrigen = StockSucursal::where('producto_id', $producto->id)
                ->where('sucursal_id', $request->sucursal_origen)
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
                'observaciones' => 'Traspaso enviado a sucursal ID: ' . $request->sucursal_destino,
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

    public function historial(Request $request)
    {
        $esAdmin = $this->usuarioEsAdmin();
        $sucursalUsuario = $this->sucursalDelUsuario();

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

        $movimientos = $query->orderBy('fecha', 'desc')->paginate(20)->withQueryString();
        $sucursales = $this->sucursalesDisponibles();

        $motivosDisponibles = MovimientoInventario::select('motivo')
            ->whereNotNull('motivo')->where('motivo', '!=', '')
            ->distinct()->orderBy('motivo')->pluck('motivo');

        return view('inventario.historial', compact(
            'movimientos', 'sucursales', 'esAdmin',
            'totalEntradas', 'totalSalidas', 'totalMovimientos', 'motivosDisponibles'
        ));
    }
}