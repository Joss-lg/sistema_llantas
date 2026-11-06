<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveContextoSucursal;
use App\Models\Cliente;
use App\Models\CorteCaja;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\StockSucursal;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PuntoVentaController extends Controller
{
    use ResuelveContextoSucursal;

    /**
     * Solo el Administrador General y el Administrador pueden vender a precio de mayoreo.
     */
    private function usuarioPuedeMayoreo(): bool
    {
        return Auth::user()->tieneRol('Administrador General', 'Administrador');
    }

    public function index(Request $request)
    {
        $esAdmin = $this->usuarioEsAdmin();
        $sucursales = $this->sucursalesDisponibles();
        $puedeMayoreo = $this->usuarioPuedeMayoreo();

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

        $productos = $query->with('stock')->get()->map(function ($producto) use ($productosNuevosHoy, $puedeMayoreo) {
            $producto->stocks = $producto->stock->pluck('cantidad', 'sucursal_id')->toArray();
            $producto->stock_cantidad = (int) ($producto->stock_cantidad ?? 0);
            // Marca si el producto tuvo entrada de inventario hoy
            $producto->es_nuevo = in_array($producto->id, $productosNuevosHoy);

            // El costo nunca viaja al navegador; el mayoreo solo si el usuario puede usarlo
            $producto->makeHidden($puedeMayoreo ? ['costo'] : ['costo', 'precio_mayoreo']);

            return $producto;
        });

        // Clientes para el selector de la venta
        $clientes = Cliente::orderBy('nombre')->get(['id', 'nombre', 'rfc', 'razon_social']);

        // La vista usa $sucursalSeleccionada para saber qué sucursal mostrar marcada
        $sucursalSeleccionada = $sucursalDefecto;

        return view('ventas.index', compact(
            'productos', 'sucursales', 'esAdmin', 'sucursalDefecto', 'sucursalSeleccionada', 'clientes', 'puedeMayoreo'
        ));
    }

    public function store(Request $request)
    {
        if (empty($request->carrito) || !is_array($request->carrito)) {
            return response()->json(['success' => false, 'message' => 'El carrito está vacío.']);
        }

        $mensajeSinTurno = 'No tienes un turno abierto. Por favor, ve al módulo de Flujo de Caja y realiza la apertura de turno antes de cobrar.';

        // Revisión rápida. La revisión definitiva (con bloqueo) se hace dentro de la transacción.
        $hayTurno = CorteCaja::where('user_id', Auth::id())
                             ->where('estado', 'abierta')
                             ->exists();

        if (!$hayTurno) {
            return response()->json(['success' => false, 'message' => $mensajeSinTurno]);
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

        // Tipo de precio: 'menudeo' (precio público) o 'mayoreo'
        $tipoPrecio = $request->input('tipo_precio', 'menudeo');

        if (!in_array($tipoPrecio, ['menudeo', 'mayoreo'], true)) {
            return response()->json(['success' => false, 'message' => 'Tipo de precio no válido.']);
        }

        if ($tipoPrecio === 'mayoreo' && !$this->usuarioPuedeMayoreo()) {
            return response()->json([
                'success' => false,
                'message' => 'Tu usuario no tiene permiso para vender a precio de mayoreo.'
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

        // Cliente: opcional. Si no se elige, la venta es de Público General.
        $cliente = null;

        if ($request->filled('cliente_id')) {
            $cliente = Cliente::find($request->cliente_id);

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cliente seleccionado ya no existe. Recarga la página.'
                ]);
            }
        }

        // Factura: necesita un cliente registrado que tenga RFC
        $requiereFactura = $request->boolean('requiereFactura');

        if ($requiereFactura && (!$cliente || trim((string) $cliente->rfc) === '')) {
            return response()->json([
                'success' => false,
                'message' => 'Para facturar debes elegir un cliente que tenga RFC registrado.'
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

            // Precio público; si es mayoreo y el producto tiene precio de mayoreo, se usa ese
            $precio = (float) $producto->precio_publico;
            if ($tipoPrecio === 'mayoreo' && (float) $producto->precio_mayoreo > 0) {
                $precio = (float) $producto->precio_mayoreo;
            }

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

        $iva = $requiereFactura ? round($subtotalVenta * 0.16, 2) : 0; // mismo 16% que muestra la vista
        $totalVenta = round($subtotalVenta + $iva, 2);

        // pago_con = dinero que entregó el cliente. Si no viene (o es menor), es el total exacto.
        $montoRecibido = is_numeric($request->montoRecibido) ? (float) $request->montoRecibido : $totalVenta;
        $pagoCon = max($montoRecibido, $totalVenta);

        try {
            $ticketUrl = DB::transaction(function () use (
                $partidas, $sucursal_id, $metodoPago, $tipoPrecio, $cliente,
                $requiereFactura, $totalVenta, $pagoCon, $mensajeSinTurno
            ) {
                // Se bloquea el turno: así la venta no puede entrar mientras se cierra la caja
                $corteActual = CorteCaja::where('user_id', Auth::id())
                                        ->where('estado', 'abierta')
                                        ->lockForUpdate()
                                        ->first();

                if (!$corteActual) {
                    throw new \DomainException($mensajeSinTurno);
                }

                $venta = new Venta();
                $venta->folio = $this->generarFolio();
                $venta->sucursal_id = $sucursal_id;
                $venta->user_id = Auth::id();
                $venta->corte_caja_id = $corteActual->id;
                $venta->cliente_id = $cliente ? $cliente->id : null;
                $venta->nombre_cliente_temporal = $cliente ? mb_substr($cliente->nombre, 0, 100) : 'Público General';
                $venta->tipo_precio = $tipoPrecio;
                $venta->total = $totalVenta;
                $venta->metodo_pago = $metodoPago;
                $venta->pago_con = $pagoCon;
                $venta->cambio = round($pagoCon - $totalVenta, 2);
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
                        throw new \DomainException('No existe registro de inventario para ' . $p['nombre'] . ' en la sucursal procesada.');
                    }

                    if ($stock->cantidad < $p['cantidad']) {
                        throw new \DomainException('Inventario insuficiente para ' . $p['nombre'] . '. Disponibles en esta sucursal: ' . $stock->cantidad);
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

                return route('ventas.ticket', $venta->id);
            });

            return response()->json([
                'success' => true,
                'ticket_url' => $ticketUrl
            ]);

        } catch (\DomainException $e) {
            // Mensajes pensados para el cajero (sin turno, sin stock, etc.)
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Throwable $e) {
            // Error inesperado: el detalle técnico va al log, el cajero ve un mensaje sencillo
            Log::error('Error al cobrar una venta', [
                'user_id'  => Auth::id(),
                'sucursal' => $sucursal_id,
                'error'    => $e->getMessage(),
                'lugar'    => $e->getFile() . ':' . $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo completar la venta. Intenta de nuevo; si sigue fallando, avisa a un administrador.'
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

    /**
     * Ticket de una venta.
     * El Administrador General puede ver cualquiera; los demás solo los de su sucursal.
     * Si la venta es de otra sucursal, responde 404 (igual que si no existiera).
     */
    public function ticket($id)
    {
        $query = Venta::with(['detalles', 'sucursal', 'user', 'cliente']);

        if (!$this->usuarioEsAdmin()) {
            $query->where('sucursal_id', $this->sucursalDelUsuario());
        }

        $venta = $query->findOrFail($id);

        return view('ventas.ticket', compact('venta'));
    }
}