<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\StockSucursal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PuntoVentaController extends Controller
{
    public function index()
    {
        $empleado = DB::table('users')->where('id', Auth::id())->first();

        // Usamos '??' para evitar el error si la propiedad 'rol' no existe en $empleado
        $esAdmin = ($empleado && ($empleado->rol ?? null) === 'admin') || Auth::id() === 1;

        // Lo mismo para 'sucursal_id', con valor por defecto 1 si no existe la columna
        $sucursalDefecto = $empleado->sucursal_id ?? 1;

        $sucursales = DB::table('sucursales')->where('activa', 1)->get();
        
        $productos = Producto::where('estado', true)->get()->map(function ($producto) {
            $producto->stocks = StockSucursal::where('producto_id', $producto->id)
                ->pluck('cantidad', 'sucursal_id')
                ->toArray();
            return $producto;
        });

        return view('ventas.index', compact('productos', 'sucursales', 'esAdmin', 'sucursalDefecto'));
    }

    public function store(Request $request)
    {
        if (empty($request->carrito)) {
            return response()->json(['success' => false, 'message' => 'El carrito está vacío.']);
        }

        try {
            DB::beginTransaction();

            // CORRECCIÓN: Se cambió 'usuarios' por 'users'
            $empleado = DB::table('users')->where('id', Auth::id())->first();
            
            $sucursal_id = $request->sucursal_id ?: ($empleado ? $empleado->sucursal_id : 1);

            $venta = new Venta();
            $venta->folio = 'VNT-' . date('Ymd') . '-' . rand(1000, 9999);
            $venta->sucursal_id = $sucursal_id;
            
            $venta->user_id = Auth::id(); 
            
            $nombre_cliente = $request->cliente ?: 'Público General';
            $venta->nombre_cliente_temporal = $nombre_cliente;
            
            $venta->total = $request->total;
            $venta->pago_con = $request->pagoCon;
            $venta->cambio = $request->cambio;
            $venta->requiere_factura = $request->requiereFactura;
            $venta->fecha = now();
            $venta->save();

            foreach ($request->carrito as $item) {
                $detalle = new VentaDetalle();
                $detalle->venta_id = $venta->id;
                $detalle->producto_id = $item['producto_id'];
                $detalle->nombre_producto = $item['nombre'];
                $detalle->cantidad = $item['cantidad'];
                $detalle->precio_unitario = $item['precio_unitario'];
                $detalle->descuento = $item['descuento'] ?? 0;
                $detalle->subtotal = $item['subtotal'];
                $detalle->save();

                if ($item['tipo'] !== 'Servicio' && !empty($item['producto_id'])) {
                    $stock = StockSucursal::where('producto_id', $item['producto_id'])
                        ->where('sucursal_id', $sucursal_id)
                        ->first();

                    if (!$stock) {
                        return response()->json([
                            'success' => false, 
                            'message' => 'No existe registro de inventario para ' . $item['nombre'] . ' en la sucursal procesada.'
                        ]);
                    }

                    if ($stock->cantidad < $item['cantidad']) {
                        return response()->json([
                            'success' => false, 
                            'message' => 'Inventario insuficiente para ' . $item['nombre'] . '. Disponibles en esta sucursal: ' . $stock->cantidad
                        ]);
                    }

                    $stock->cantidad -= $item['cantidad'];
                    $stock->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'ticket_url' => route('ventas.ticket', $venta->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error de servidor: ' . $e->getMessage()]);
        }
    }

    public function historial(Request $request)
    {
        // CORRECCIÓN: Se cambió 'usuarios' por 'users'
        $empleado = DB::table('users')->where('id', Auth::id())->first();
        
        // CORRECCIÓN: Se agregó el safe operator ?? null igual que en tu index
        $esAdmin = ($empleado && ($empleado->rol ?? null) === 'admin') || Auth::id() === 1;
        
        $sucursalUsuario = $empleado ? $empleado->sucursal_id : 1;

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
        
        $sucursales = $esAdmin ? DB::table('sucursales')->where('activa', 1)->get() : [];

        return view('ventas.historial', compact('ventas', 'sucursales', 'esAdmin'));
    }

    public function ticket($id)
    {
        $venta = Venta::with(['detalles'])->findOrFail($id);
        return view('ventas.ticket', compact('venta'));
    }
}