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
        
        // Admin: la sucursal elegida en el selector (o la suya). Empleado: siempre la suya.
        $sucursalDefecto = $this->sucursalParaOperar($request) ?? ($sucursales->first()->id ?? null);

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

        // La vista usa $sucursalSeleccionada para saber qué sucursal mostrar marcada
        $sucursalSeleccionada = $sucursalDefecto;

        return view('ventas.index', compact('productos', 'sucursales', 'esAdmin', 'sucursalDefecto', 'sucursalSeleccionada'));
    }

    public function store(Request $request)
    {
        if (empty($request->carrito) || !is_array($request->carrito)) {
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

        // Método de pago: la vista lo manda en 'pagoCon' ('Efectivo', 'Tarjeta' o 'Transferencia')
        $metodosValidos = ['Efectivo', 'Tarjeta', 'Transferencia'];
        $metodoPago = ucfirst(strtolower(trim((string) $request->pagoCon)));

        if (!in_array($metodoPago, $metodosValidos, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Método de pago no válido. Usa Efectivo, Tarjeta o Transferencia.'
            ]);
        }

        // Sucursal de la venta: admin = la elegida; empleado = SIEMPRE la suya
        $sucursal_id = $this->sucursalParaOperar($request);

        if (!$sucursal_id) {
            return response()->json([
                'success' => false,
                'message' => 'Selecciona la sucursal desde la que vas a vender.'
            ]);
        }

        // ============================================================
        // PRECIOS CALCULADOS EN EL SERVIDOR
        // Del navegador solo tomamos QUÉ producto y CUÁNTOS.
        // El precio, el nombre y el tipo salen de la base de datos,
        // así nadie puede cambiar el precio desde el navegador.
        // ============================================================
        $partidas = [];
        $subtotalVenta = 0;

        foreach ($request->carrito as $item) {
            $productoId = $item['producto_id'] ?? null;
            $cantidad = $item['cantidad'] ?? null;

            if (!is_numeric($cantidad) || (int) $cantidad != $cantidad || (int) $cantidad < 1) {
                return response()->json(['success' => false, 'message' => 'Hay una cantidad no válida en el carrito.']);
            }

            $producto = Producto::where('id', $productoId)->where('estado', true)->first();

            if (!$producto) {
                return response()->json(['success' => false, 'message' => 'Un producto del carrito ya no existe o está desactivado. Recarga la página.']);
            }

            $cantidad = (int) $cantidad;
            $precio = (float) $producto->precio_publico;
            $subtotal = round($precio * $cantidad, 2);
            $subtotalVenta += $subtotal;

            $partidas[] = [
                'producto'  => $producto,
                'nombre'    => $producto->tipo === 'Servicio'
                                ? ($producto->descripcion ?: $producto->marca)
                                : trim($producto->marca . ' ' . $producto->medida),
                'cantidad'  => $cantidad,
                'precio'    => $precio,
                'subtotal'  => $subtotal,
            ];
        }

        $requiereFactura = (bool) $request->requiereFactura;
        $iva = $requiereFactura ? round($subtotalVenta * 0.16, 2) : 0; // mismo 16% que muestra la vista
        $totalVenta = round($subtotalVenta + $iva, 2);

        DB::beginTransaction();

        try {
            $venta = new Venta();
            $venta->folio = $this->generarFolio();
            $venta->sucursal_id = $sucursal_id;
            $venta->user_id = Auth::id();
            $venta->corte_caja_id = $corteActual->id;
            $venta->nombre_cliente_temporal = $request->cliente ?: 'Público General';
            $venta->total = $totalVenta;
            $venta->metodo_pago = $metodoPago;
            // pago_con = dinero que entregó el cliente. Si no viene (o es menor), es el total exacto.
            $montoRecibido = is_numeric($request->montoRecibido) ? (float) $request->montoRecibido : $totalVenta;
            $venta->pago_con = max($montoRecibido, $totalVenta);
            $venta->cambio = round($venta->pago_con - $totalVenta, 2);
            $venta->requiere_factura = $requiereFactura;
            $venta->fecha = now();
            $venta->save();

            foreach ($partidas as $p) {
                $detalle = new VentaDetalle();
                $detalle->venta_id = $venta->id;
                $detalle->producto_id = $p['producto']->id;
                $detalle->nombre_producto = mb_substr($p['nombre'], 0, 150);
                $detalle->cantidad = $p['cantidad'];
                $detalle->precio_unitario = $p['precio'];
                $detalle->descuento = 0;
                $detalle->subtotal = $p['subtotal'];
                $detalle->save();

                // Los servicios no descuentan inventario
                if ($p['producto']->tipo === 'Servicio') {
                    continue;
                }

                $stock = StockSucursal::where('producto_id', $p['producto']->id)
                    ->where('sucursal_id', $sucursal_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stock) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'No existe registro de inventario para ' . $p['nombre'] . ' en la sucursal procesada.'
                    ]);
                }

                if ($stock->cantidad < $p['cantidad']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Inventario insuficiente para ' . $p['nombre'] . '. Disponibles en esta sucursal: ' . $stock->cantidad
                    ]);
                }

                $stock->cantidad -= $p['cantidad'];
                $stock->save();

                MovimientoInventario::create([
                    'producto_id' => $p['producto']->id,
                    'sucursal_id' => $sucursal_id,
                    'usuario_id'  => Auth::id(),
                    'tipo'        => 'salida',
                    'cantidad'    => $p['cantidad'],
                    'motivo'      => 'Venta ' . $venta->folio,
                    'fecha'       => now(),
                ]);
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

    /**
     * Genera un folio único, ej. VNT-20260929-4821 (revisa que no exista ya).
     */
    private function generarFolio(): string
    {
        do {
            $folio = 'VNT-' . date('Ymd') . '-' . random_int(1000, 9999);
        } while (Venta::where('folio', $folio)->exists());

        return $folio;
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