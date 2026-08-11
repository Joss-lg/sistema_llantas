<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveContextoSucursal;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\StockSucursal;
use App\Models\Sucursal;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PuntoVentaController extends Controller
{
    use ResuelveContextoSucursal;

    public function index()
    {
        $esAdmin = $this->usuarioEsAdmin();
        $sucursales = $this->sucursalesDisponibles();
        $sucursalDefecto = $esAdmin
            ? ($sucursales->first()->id ?? null)
            : $this->sucursalDelUsuario();

        $productos = Producto::where('estado', true)
            ->with('stock')
            ->get()
            ->map(function ($producto) {
                $producto->stocks = $producto->stock->pluck('cantidad', 'sucursal_id')->toArray();
                return $producto;
            });

        return view('ventas.index', compact('productos', 'sucursales', 'esAdmin', 'sucursalDefecto'));
    }

    public function store(Request $request)
    {
        if (empty($request->carrito)) {
            return response()->json(['success' => false, 'message' => 'El carrito está vacío.']);
        }

        DB::beginTransaction();

        try {
            $sucursal_id = $this->sucursalSeleccionada($request);

            $venta = new Venta();
            $venta->folio = 'VNT-' . date('Ymd') . '-' . rand(1000, 9999);
            $venta->sucursal_id = $sucursal_id;
            $venta->user_id = Auth::id();
            $venta->nombre_cliente_temporal = $request->cliente ?: 'Público General';
            $venta->total = $request->total;
            $venta->pago_con = $request->pagoCon;
            $venta->cambio = $request->cambio;
            $venta->requiere_factura = (bool) $request->requiereFactura;
            $venta->fecha = now();
            $venta->save();

            foreach ($request->carrito as $item) {
                $detalle = new VentaDetalle();
                $detalle->venta_id = $venta->id;
                $detalle->producto_id = $item['producto_id'] ?? null;
                $detalle->nombre_producto = $item['nombre'];
                $detalle->cantidad = $item['cantidad'];
                $detalle->precio_unitario = $item['precio_unitario'];
                $detalle->descuento = $item['descuento'] ?? 0;
                $detalle->subtotal = $item['subtotal'];
                $detalle->save();

                // Procesar deducción de stock si no es un servicio puro
                if (($item['tipo'] ?? '') !== 'Servicio' && !empty($item['producto_id'])) {
                    $stock = StockSucursal::where('producto_id', $item['producto_id'])
                        ->where('sucursal_id', $sucursal_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$stock) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'No existe registro de inventario para ' . $item['nombre'] . ' en la sucursal procesada.'
                        ]);
                    }

                    if ($stock->cantidad < $item['cantidad']) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Inventario insuficiente para ' . $item['nombre'] . '. Disponibles en esta sucursal: ' . $stock->cantidad
                        ]);
                    }

                    // Actualizar Stock
                    $stock->cantidad -= $item['cantidad'];
                    $stock->save();

                    // Registrar Historial en Movimiento de Inventario
                    MovimientoInventario::create([
                        'producto_id'          => $item['producto_id'],
                        'sucursal_origen_id'   => $sucursal_id,
                        'sucursal_destino_id'  => null,
                        'user_id'              => Auth::id(),
                        'tipo'                 => 'salida',
                        'cantidad'             => $item['cantidad'],
                        'motivo'               => 'Venta ' . $venta->folio,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'ticket_url' => route('ventas.ticket', $venta->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la venta: ' . $e->getMessage()
            ]);
        }
    }

    public function historial(Request $request)
    {
        $esAdmin = $this->usuarioEsAdmin();
        $sucursalUsuario = $this->sucursalDelUsuario();

        $query = Venta::with(['detalles']);

        if (!$esAdmin) {
            $query->where('sucursal_id', $sucursalUsuario);
        } elseif ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        if ($request->filled('folio')) {
            $query->where('folio', 'like', '%' . trim($request->folio) . '%');
        }

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha', [
                $request->fecha_inicio . ' 00:00:00',
                $request->fecha_fin . ' 23:59:59'
            ]);
        }

        $ventas = $query->orderBy('fecha', 'desc')->paginate(15)->withQueryString();
        $sucursales = $esAdmin ? $this->sucursalesDisponibles() : [];

        return view('ventas.historial', compact('ventas', 'sucursales', 'esAdmin'));
    }

    public function ticket($id)
    {
        $venta = Venta::with(['detalles'])->findOrFail($id);
        return view('ventas.ticket', compact('venta'));
    }
}