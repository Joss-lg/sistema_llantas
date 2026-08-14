<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveContextoSucursal;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\StockSucursal;
use App\Models\Sucursal;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\CorteCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PuntoVentaController extends Controller
{
    use ResuelveContextoSucursal;

    public function index(Request $request)
    {
        $esAdmin = $this->usuarioEsAdmin();
        $sucursales = $this->sucursalesDisponibles();
        
        $sucursalDefecto = $esAdmin
            ? ($request->input('sucursal_id') ?? ($sucursales->first()->id ?? 1))
            : $this->sucursalDelUsuario();

        // IDs de productos con entrada de inventario registrada hoy
        $productosNuevosHoy = MovimientoInventario::where('tipo', 'entrada')
            ->whereDate('fecha', today())
            ->pluck('producto_id')
            ->unique()
            ->toArray();

        $query = Producto::where('estado', true);

        if ($sucursalDefecto) {
            $query->withSum(['stock as stock_cantidad' => function($q) use ($sucursalDefecto) {
                $q->where('sucursal_id', $sucursalDefecto);
            }], 'cantidad');
        } else {
            $query->withSum('stock as stock_cantidad', 'cantidad');
        }

        $productos = $query->with('stock')->get()->map(function ($producto) use ($productosNuevosHoy) {
            $producto->stocks = $producto->stock->pluck('cantidad', 'sucursal_id')->toArray();
            $producto->stock_cantidad = (int) ($producto->stock_cantidad ?? 0);
            // Marca si el producto tuvo entrada de inventario hoy
            $producto->es_nuevo = in_array($producto->id, $productosNuevosHoy);
            return $producto;
        });

        return view('ventas.index', compact('productos', 'sucursales', 'esAdmin', 'sucursalDefecto'));
    }

    public function store(Request $request)
    {
        if (empty($request->carrito)) {
            return response()->json(['success' => false, 'message' => 'El carrito está vacío.']);
        }

        $corteActual = CorteCaja::where('user_id', Auth::id())
                                ->where('estado', 'abierta')
                                ->first();

        if (!$corteActual) {
            return response()->json([
                'success' => false, 
                'message' => 'No tienes un turno abierto. Por favor, ve al módulo de Flujo de Caja y realiza la apertura de turno antes de cobrar.'
            ]);
        }

        DB::beginTransaction();

        try {
            $sucursal_id = $this->sucursalSeleccionada($request);

            $venta = new Venta();
            $venta->folio = 'VNT-' . date('Ymd') . '-' . rand(1000, 9999);
            $venta->sucursal_id = $sucursal_id;
            $venta->user_id = Auth::id();
            $venta->corte_caja_id = $corteActual->id;
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

                    $stock->cantidad -= $item['cantidad'];
                    $stock->save();

                    MovimientoInventario::create([
                        'producto_id' => $item['producto_id'],
                        'sucursal_id' => $sucursal_id,
                        'usuario_id'  => Auth::id(),
                        'tipo'        => 'salida',
                        'cantidad'    => $item['cantidad'],
                        'motivo'      => 'Venta ' . $venta->folio,
                        'fecha'       => now(),
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