<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveContextoSucursal;
use App\Models\Cliente;
use App\Models\Sucursal;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportesController extends Controller
{
    use ResuelveContextoSucursal;

    public function index(Request $request)
    {
        $datos = $this->construirDatosReporte($request);

        return view('reportes.index', $datos);
    }

    public function exportarPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $datos = $this->construirDatosReporte($request);
        $datos['fechaGeneracion'] = now()->format('d/m/Y H:i');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.pdf', $datos);
        $pdf->setPaper('a4', 'portrait');

        $nombre = 'reporte_' . now()->format('Y-m-d_His') . '.pdf';
        return $pdf->download($nombre);
    }

    /**
     * Construye todo el set de datos del reporte (usado tanto por index() como por exportarPdf()
     * para que la pantalla y el PDF siempre muestren exactamente lo mismo).
     */
    private function construirDatosReporte(Request $request): array
    {
        $esAdmin = $this->usuarioEsAdmin();
        $sucursales = $this->sucursalesDisponibles();
        $sucursalFiltro = $this->sucursalSeleccionada($request);

        $periodo = $request->input('periodo', 'hoy');
        if (!in_array($periodo, ['hoy', 'semana', 'mes', 'personalizado'])) {
            $periodo = 'hoy';
        }

        [$inicio, $fin] = $this->resolverRangoFechas($periodo, $request);
        [$inicioAnterior, $finAnterior] = $this->resolverRangoAnterior($inicio, $fin);

        // ===== Ventas del periodo actual =====
        $queryVentas = Venta::whereBetween('fecha', [$inicio, $fin]);
        if ($sucursalFiltro) {
            $queryVentas->where('sucursal_id', $sucursalFiltro);
        }
        $ingresosTotales = (clone $queryVentas)->sum('total');
        $ventasRealizadas = (clone $queryVentas)->count();

        // ===== Ventas del periodo anterior (para comparativas) =====
        $queryAnterior = Venta::whereBetween('fecha', [$inicioAnterior, $finAnterior]);
        if ($sucursalFiltro) {
            $queryAnterior->where('sucursal_id', $sucursalFiltro);
        }
        $ingresosAnterior = (clone $queryAnterior)->sum('total');
        $ventasAnterior = (clone $queryAnterior)->count();

        // ===== Clientes nuevos (tabla 'clientes' no tiene sucursal_id: no se puede filtrar por sucursal) =====
        $clientesNuevos = Cliente::whereBetween('created_at', [$inicio, $fin])->count();
        $clientesAnterior = Cliente::whereBetween('created_at', [$inicioAnterior, $finAnterior])->count();

        // ===== Ventas por día (para la gráfica de línea) =====
        $ventasPorDia = (clone $queryVentas)
            ->selectRaw('DATE(fecha) as dia, SUM(total) as total')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        // ===== Ventas por sucursal (para la gráfica de barras) =====
        $ventasPorSucursalQuery = Venta::whereBetween('fecha', [$inicio, $fin]);
        if ($sucursalFiltro) {
            $ventasPorSucursalQuery->where('sucursal_id', $sucursalFiltro);
        }
        $ventasPorSucursal = $ventasPorSucursalQuery
            ->selectRaw('sucursal_id, SUM(total) as total')
            ->groupBy('sucursal_id')
            ->with('sucursal')
            ->get();

        // ===== Productos más vendidos =====
        $productosMasVendidos = VentaDetalle::whereHas('venta', function ($q) use ($inicio, $fin, $sucursalFiltro) {
            $q->whereBetween('fecha', [$inicio, $fin]);
            if ($sucursalFiltro) {
                $q->where('sucursal_id', $sucursalFiltro);
            }
        })
            ->selectRaw('nombre_producto, SUM(cantidad) as total_cantidad, SUM(subtotal) as total_ingresos')
            ->groupBy('nombre_producto')
            ->orderByDesc('total_cantidad')
            ->limit(5)
            ->get();

        // ===== Porcentajes de variación =====
        $porcentajeIngresos = $this->calcularPorcentaje($ingresosTotales, $ingresosAnterior);
        $porcentajeVentas = $this->calcularPorcentaje($ventasRealizadas, $ventasAnterior);
        $porcentajeClientes = $this->calcularPorcentaje($clientesNuevos, $clientesAnterior);

        $etiquetaComparativa = match ($periodo) {
            'hoy' => 'vs. ayer',
            'semana' => 'vs. semana anterior',
            'mes' => 'vs. mes anterior',
            default => 'vs. periodo anterior',
        };

        return [
            'esAdmin'              => $esAdmin,
            'sucursales'           => $sucursales,
            'sucursalFiltro'       => $sucursalFiltro,
            'periodo'              => $periodo,
            'fechaInicio'          => $inicio,
            'fechaFin'             => $fin,
            'ingresosTotales'      => $ingresosTotales,
            'ventasRealizadas'     => $ventasRealizadas,
            'clientesNuevos'       => $clientesNuevos,
            'porcentajeIngresos'   => $porcentajeIngresos,
            'porcentajeVentas'     => $porcentajeVentas,
            'porcentajeClientes'   => $porcentajeClientes,
            'etiquetaComparativa'  => $etiquetaComparativa,
            'ventasPorDia'         => $ventasPorDia,
            'ventasPorSucursal'    => $ventasPorSucursal,
            'productosMasVendidos' => $productosMasVendidos,
        ];
    }

    private function resolverRangoFechas(string $periodo, Request $request): array
    {
        $hoy = Carbon::today();

        switch ($periodo) {
            case 'semana':
                $inicio = $hoy->copy()->startOfWeek(Carbon::MONDAY);
                $fin = $hoy->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
                break;

            case 'mes':
                $inicio = $hoy->copy()->startOfMonth();
                $fin = $hoy->copy()->endOfMonth()->endOfDay();
                break;

            case 'personalizado':
                $inicio = $request->filled('fecha_desde')
                    ? Carbon::parse($request->fecha_desde)->startOfDay()
                    : $hoy->copy()->startOfDay();
                $fin = $request->filled('fecha_hasta')
                    ? Carbon::parse($request->fecha_hasta)->endOfDay()
                    : $hoy->copy()->endOfDay();
                break;

            case 'hoy':
            default:
                $inicio = $hoy->copy()->startOfDay();
                $fin = $hoy->copy()->endOfDay();
                break;
        }

        return [$inicio, $fin];
    }

    /**
     * Calcula el rango "anterior" con la misma duración, para las comparativas.
     */
    private function resolverRangoAnterior(Carbon $inicio, Carbon $fin): array
    {
        $duracionDias = $inicio->diffInDays($fin) + 1;
        $inicioAnterior = $inicio->copy()->subDays($duracionDias);
        $finAnterior = $inicio->copy()->subSecond();

        return [$inicioAnterior, $finAnterior];
    }

    private function calcularPorcentaje(float $actual, float $anterior): float
    {
        if ($anterior <= 0) {
            return $actual > 0 ? 100 : 0;
        }

        return round((($actual - $anterior) / $anterior) * 100, 1);
    }
}