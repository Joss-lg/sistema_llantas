<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveContextoSucursal;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReportesController extends Controller
{
    use ResuelveContextoSucursal;

    private const MAX_DIAS_RANGO = 366;

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
        $this->validarFiltros($request);

        $esAdmin = $this->usuarioEsAdmin();
        $sucursales = $this->sucursalesDisponibles();
        $sucursalFiltro = $this->sucursalSeleccionada($request);

        // Un usuario que no es Administrador General SIEMPRE debe tener sucursal;
        // si no, el filtro quedaría vacío y vería los datos de todas.
        if (!$esAdmin && !$sucursalFiltro) {
            abort(403, 'Tu usuario no tiene una sucursal asignada. Pide al administrador que te la asigne para ver reportes.');
        }

        $periodo = $request->input('periodo', 'hoy');
        if (!in_array($periodo, ['hoy', 'semana', 'mes', 'personalizado'])) {
            $periodo = 'hoy';
        }

        [$inicio, $fin] = $this->resolverRangoFechas($periodo, $request);
        [$inicioAnterior, $finAnterior] = $this->resolverRangoAnterior($periodo, $inicio, $fin);

        // Consulta base de ventas: siempre respeta la sucursal del filtro
        $ventasEntre = function (Carbon $desde, Carbon $hasta) use ($sucursalFiltro) {
            $query = Venta::whereBetween('fecha', [$desde, $hasta]);
            if ($sucursalFiltro) {
                $query->where('sucursal_id', $sucursalFiltro);
            }

            return $query;
        };

        // ===== Ventas del periodo actual =====
        $queryVentas = $ventasEntre($inicio, $fin);
        $ingresosTotales = (clone $queryVentas)->sum('total');
        $ventasRealizadas = (clone $queryVentas)->count();

        // ===== Ventas del periodo anterior (para comparativas) =====
        $queryAnterior = $ventasEntre($inicioAnterior, $finAnterior);
        $ingresosAnterior = (clone $queryAnterior)->sum('total');
        $ventasAnterior = (clone $queryAnterior)->count();

        // ===== Clientes nuevos (tabla 'clientes' no tiene sucursal_id: es de todo el negocio) =====
        $clientesNuevos = Cliente::whereBetween('created_at', [$inicio, $fin])->count();
        $clientesAnterior = Cliente::whereBetween('created_at', [$inicioAnterior, $finAnterior])->count();

        // ===== Ventas por día (solo días con ventas: lo usa el PDF) =====
        $ventasPorDia = (clone $queryVentas)
            ->selectRaw('DATE(fecha) as dia, SUM(total) as total')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        // ===== Serie diaria completa, con 0 en los días sin ventas (la usa la gráfica) =====
        $totalesPorDia = $ventasPorDia->pluck('total', 'dia');
        $serieDiaria = collect();
        for ($dia = $inicio->copy()->startOfDay(); $dia->lte($fin); $dia->addDay()) {
            $clave = $dia->format('Y-m-d');
            $serieDiaria->push([
                'dia'   => $clave,
                'total' => (float) ($totalesPorDia[$clave] ?? 0),
            ]);
        }

        // ===== Ventas por sucursal (para la gráfica de barras) =====
        $ventasPorSucursal = $ventasEntre($inicio, $fin)
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
        $porcentajeIngresos = $this->calcularPorcentaje((float) $ingresosTotales, (float) $ingresosAnterior);
        $porcentajeVentas = $this->calcularPorcentaje((float) $ventasRealizadas, (float) $ventasAnterior);
        $porcentajeClientes = $this->calcularPorcentaje((float) $clientesNuevos, (float) $clientesAnterior);

        $etiquetaComparativa = match ($periodo) {
            'hoy' => 'vs. ayer',
            'semana' => 'vs. mismos días de la semana anterior',
            'mes' => 'vs. mismos días del mes anterior',
            default => 'vs. periodo anterior',
        };

        return [
            'esAdmin'              => $esAdmin,
            'sucursales'           => $sucursales,
            'sucursalFiltro'       => $sucursalFiltro,
            'periodo'              => $periodo,
            'fechaInicio'          => $inicio,
            'fechaFin'             => $fin,
            'fechaInicioAnterior'  => $inicioAnterior,
            'fechaFinAnterior'     => $finAnterior,
            'ingresosTotales'      => $ingresosTotales,
            'ventasRealizadas'     => $ventasRealizadas,
            'clientesNuevos'       => $clientesNuevos,
            'porcentajeIngresos'   => $porcentajeIngresos,
            'porcentajeVentas'     => $porcentajeVentas,
            'porcentajeClientes'   => $porcentajeClientes,
            'etiquetaComparativa'  => $etiquetaComparativa,
            'ventasPorDia'         => $ventasPorDia,
            'serieDiaria'          => $serieDiaria,
            'ventasPorSucursal'    => $ventasPorSucursal,
            'productosMasVendidos' => $productosMasVendidos,
        ];
    }

    /**
     * Revisa las fechas del filtro. Si algo está mal, regresa al reporte con un mensaje
     * (en lugar de un error 500).
     */
    private function validarFiltros(Request $request): void
    {
        $reglasHasta = ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'];
        if ($request->filled('fecha_desde')) {
            $reglasHasta[] = 'after_or_equal:fecha_desde';
        }

        $request->validate([
            'fecha_desde' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'fecha_hasta' => $reglasHasta,
        ], [
            'fecha_desde.date_format'     => 'La fecha inicial no tiene un formato válido.',
            'fecha_desde.before_or_equal' => 'La fecha inicial no puede ser futura.',
            'fecha_hasta.date_format'     => 'La fecha final no tiene un formato válido.',
            'fecha_hasta.before_or_equal' => 'La fecha final no puede ser futura.',
            'fecha_hasta.after_or_equal'  => 'La fecha final no puede ser anterior a la fecha inicial.',
        ]);

        // Máximo de días en un periodo personalizado
        if ($request->input('periodo') === 'personalizado' && $request->filled('fecha_desde')) {
            $desde = Carbon::createFromFormat('Y-m-d', $request->input('fecha_desde'))->startOfDay();
            $hasta = $request->filled('fecha_hasta')
                ? Carbon::createFromFormat('Y-m-d', $request->input('fecha_hasta'))->startOfDay()
                : Carbon::today();

            if ($desde->diffInDays($hasta) + 1 > self::MAX_DIAS_RANGO) {
                throw ValidationException::withMessages([
                    'fecha_hasta' => 'El rango máximo es de ' . self::MAX_DIAS_RANGO . ' días.',
                ]);
            }
        }
    }

    /**
     * Rango del periodo. El fin nunca pasa de hoy (los días futuros no tienen ventas
     * y harían injusta la comparación con el periodo anterior).
     */
    private function resolverRangoFechas(string $periodo, Request $request): array
    {
        $hoy = Carbon::today();

        switch ($periodo) {
            case 'semana':
                $inicio = $hoy->copy()->startOfWeek(Carbon::MONDAY);
                $fin = $hoy->copy()->endOfDay();
                break;

            case 'mes':
                $inicio = $hoy->copy()->startOfMonth();
                $fin = $hoy->copy()->endOfDay();
                break;

            case 'personalizado':
                $fin = $request->filled('fecha_hasta')
                    ? Carbon::createFromFormat('Y-m-d', $request->input('fecha_hasta'))->endOfDay()
                    : $hoy->copy()->endOfDay();
                $inicio = $request->filled('fecha_desde')
                    ? Carbon::createFromFormat('Y-m-d', $request->input('fecha_desde'))->startOfDay()
                    : $fin->copy()->startOfDay();
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
     * Rango "anterior" para las comparativas, con los mismos días transcurridos:
     * - hoy: ayer
     * - semana: los mismos días de la semana anterior
     * - mes: los mismos días del mes anterior
     * - personalizado: el mismo número de días justo antes del inicio
     */
    private function resolverRangoAnterior(string $periodo, Carbon $inicio, Carbon $fin): array
    {
        // Días transcurridos del periodo (número entero)
        $dias = (int) round($inicio->copy()->startOfDay()->diffInDays($fin->copy()->startOfDay())) + 1;

        switch ($periodo) {
            case 'semana':
                $inicioAnterior = $inicio->copy()->subWeek()->startOfDay();
                $finAnterior = $inicioAnterior->copy()->addDays($dias - 1)->endOfDay();
                break;

            case 'mes':
                $inicioAnterior = $inicio->copy()->subMonthNoOverflow()->startOfMonth();
                $finAnterior = $inicioAnterior->copy()->addDays($dias - 1)->endOfDay();

                // Si el mes anterior es más corto, no pasarse de su último día
                $ultimoDiaMesAnterior = $inicioAnterior->copy()->endOfMonth();
                if ($finAnterior->gt($ultimoDiaMesAnterior)) {
                    $finAnterior = $ultimoDiaMesAnterior;
                }
                break;

            default: // hoy y personalizado
                $inicioAnterior = $inicio->copy()->subDays($dias)->startOfDay();
                $finAnterior = $inicio->copy()->subDay()->endOfDay();
                break;
        }

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