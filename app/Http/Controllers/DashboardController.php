<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveContextoSucursal;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Services\InventarioQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ResuelveContextoSucursal;

    public function __construct(private InventarioQueryService $inventarioQuery)
    {
    }

    public function index()
    {
        $usuario = Auth::user();
        $esAdmin = $this->usuarioEsAdmin();

        // Administrador General: ve todas las sucursales (null = sin filtro).
        // Cualquier otro usuario: SOLO su sucursal. Si no tiene una, no ve datos de nadie.
        $sucursalId  = $esAdmin ? null : $this->sucursalDelUsuario();
        $sinSucursal = ! $esAdmin && ! $sucursalId;

        $sucursalNombre = $esAdmin
            ? 'Todas las sucursales'
            : optional($this->sucursalesDisponibles()->first())->nombre;

        $hoyInicio  = today()->startOfDay();
        $hoyFin     = today()->endOfDay();
        $ayerInicio = today()->subDay()->startOfDay();
        $ayerFin    = today()->subDay()->endOfDay();

        // Fechas en español sin cambiar el idioma de toda la aplicación
        $fechaHoy = ucfirst(now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY'));

        // Permisos: los enlaces solo salen si la persona puede entrar a esa pantalla
        $puede = [
            'inventario' => $usuario->tienePermiso('inventario.index'),
            'historial'  => $usuario->tienePermiso('ventas.historial'),
        ];

        // Sin permiso para ver los datos (o sin sucursal): solo el saludo, todo en cero
        $sinPermiso = ! $usuario->tienePermiso('dashboard.datos');

        if ($sinPermiso || $sinSucursal) {
            return view('dashboard', [
                'sinPermiso'      => $sinPermiso,
                'sinSucursal'     => ! $sinPermiso && $sinSucursal,
                'sucursalNombre'  => $sinSucursal ? null : $sucursalNombre,
                'fechaHoy'        => $fechaHoy,
                'puede'           => $puede,
                'ventasHoy'       => 0,
                'numVentasHoy'    => 0,
                'ventasAyer'      => 0,
                'cambioVsAyer'    => null,
                'llantasVendidas' => 0,
                'bajoStock'       => 0,
                'clientesNuevos'  => 0,
                'ultimasVentas'   => collect(),
                'ventasSemana'    => $this->semanaVacia(),
                'totalSemana'     => 0,
                'maxSemana'       => 0,
            ]);
        }

        $ventas = fn () => Venta::query()->when($sucursalId, fn ($q) => $q->where('sucursal_id', $sucursalId));

        // Ventas de hoy (monto y cantidad) y de ayer, todo por la fecha de la venta
        $hoy = $ventas()->whereBetween('fecha', [$hoyInicio, $hoyFin])
            ->selectRaw('COALESCE(SUM(total), 0) AS monto, COUNT(*) AS cuantas')
            ->first();
        $ventasHoy    = (float) $hoy->monto;
        $numVentasHoy = (int) $hoy->cuantas;

        $ventasAyer = (float) $ventas()->whereBetween('fecha', [$ayerInicio, $ayerFin])->sum('total');

        // Comparación con ayer: si ayer no hubo ventas no se puede sacar porcentaje
        $cambioVsAyer = $ventasAyer > 0
            ? (int) round((($ventasHoy - $ventasAyer) / $ventasAyer) * 100)
            : null;

        // Piezas vendidas hoy (los servicios no son piezas, no se cuentan)
        $llantasVendidas = (int) VentaDetalle::query()
            ->join('ventas', 'ventas.id', '=', 'venta_detalles.venta_id')
            ->join('productos', 'productos.id', '=', 'venta_detalles.producto_id')
            ->whereBetween('ventas.fecha', [$hoyInicio, $hoyFin])
            ->where('productos.tipo', '!=', 'Servicio')
            ->when($sucursalId, fn ($q) => $q->where('ventas.sucursal_id', $sucursalId))
            ->sum('venta_detalles.cantidad');

        // Bajo stock: la MISMA regla que Inventario (tiene piezas, pero menos que su mínimo)
        $stock  = 'COALESCE(stock_cantidad, 0)';
        $minimo = 'COALESCE(stock_minimo, 5)';
        $base   = $this->inventarioQuery->query(new Request(), $sucursalId, conStockMinimo: true);
        $bajoStock = (int) DB::query()
            ->fromSub($base, 'p')
            ->whereRaw("{$stock} > 0 AND {$stock} < {$minimo}")
            ->count();

        // Los clientes son de todo el negocio (no tienen sucursal)
        $clientesNuevos = Cliente::whereBetween('created_at', [$hoyInicio, $hoyFin])->count();

        // Últimas 6 ventas
        $ultimasVentas = $ventas()
            ->with('cliente:id,nombre')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->take(6)
            ->get();

        // Últimos 7 días: UNA sola consulta agrupada por día
        $inicioSemana = today()->subDays(6)->startOfDay();
        $porDia = $ventas()
            ->whereBetween('fecha', [$inicioSemana, $hoyFin])
            ->selectRaw('DATE(fecha) AS dia, SUM(total) AS monto')
            ->groupBy('dia')
            ->pluck('monto', 'dia');

        $ventasSemana = collect();
        for ($i = 6; $i >= 0; $i--) {
            $dia = today()->subDays($i);
            $ventasSemana->push([
                'label' => rtrim($dia->copy()->locale('es')->shortDayName, '.'),
                'fecha' => $dia->toDateString(),
                'total' => (float) ($porDia[$dia->toDateString()] ?? 0),
                'hoy'   => $i === 0,
            ]);
        }

        return view('dashboard', [
            'sinPermiso'      => false,
            'sinSucursal'     => false,
            'sucursalNombre'  => $sucursalNombre,
            'fechaHoy'        => $fechaHoy,
            'puede'           => $puede,
            'ventasHoy'       => $ventasHoy,
            'numVentasHoy'    => $numVentasHoy,
            'ventasAyer'      => $ventasAyer,
            'cambioVsAyer'    => $cambioVsAyer,
            'llantasVendidas' => $llantasVendidas,
            'bajoStock'       => $bajoStock,
            'clientesNuevos'  => $clientesNuevos,
            'ultimasVentas'   => $ultimasVentas,
            'ventasSemana'    => $ventasSemana,
            'totalSemana'     => (float) $ventasSemana->sum('total'),
            'maxSemana'       => (float) $ventasSemana->max('total'),
        ]);
    }

    /** Los 7 días en cero (para quien no tiene sucursal asignada). */
    private function semanaVacia()
    {
        return collect(range(6, 0))->map(fn ($i) => [
            'label' => rtrim(today()->subDays($i)->locale('es')->shortDayName, '.'),
            'fecha' => today()->subDays($i)->toDateString(),
            'total' => 0.0,
            'hoy'   => $i === 0,
        ]);
    }
}
