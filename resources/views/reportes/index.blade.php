@extends('layouts.app')

@section('header_title', 'Reportes')

@section('content')

{{-- Gráficas: Chart.js viene dentro del proyecto (no se baja de internet) --}}
@vite('resources/js/reportes.js')

{{-- Flatpickr (calendario del rango de fechas): viene dentro del proyecto, cargado desde el diseño principal --}}

<style>
    /* Calendario con los colores del sistema (y modo oscuro) */
    .flatpickr-calendar { border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.18); font-family: inherit; }
    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange { background: #4F46E5 !important; border-color: #4F46E5 !important; }
    .flatpickr-day.inRange {
        background: rgba(79, 70, 229, 0.12) !important;
        border-color: rgba(79, 70, 229, 0.12) !important;
        box-shadow: -5px 0 0 rgba(79, 70, 229, 0.12), 5px 0 0 rgba(79, 70, 229, 0.12);
    }
    html.dark .flatpickr-calendar { background: #15161A; border-color: rgba(255,255,255,0.1); }
    html.dark .flatpickr-day { color: #d4d4d4; }
    html.dark .flatpickr-day.flatpickr-disabled,
    html.dark .flatpickr-day.prevMonthDay,
    html.dark .flatpickr-day.nextMonthDay { color: #525252; }
    html.dark .flatpickr-months,
    html.dark .flatpickr-weekdays,
    html.dark span.flatpickr-weekday { background: #15161A; color: #e5e5e5; }
    html.dark .flatpickr-current-month .cur-month,
    html.dark .flatpickr-current-month input.cur-year { color: #e5e5e5; }
    html.dark .flatpickr-prev-month svg,
    html.dark .flatpickr-next-month svg { fill: #a3a3a3; }
</style>

@php
    $dinero = fn ($n) => '$' . number_format((float) $n, 2);

    // Cómo se muestra el cambio contra el periodo anterior
    $variacion = function (float $p): array {
        if ($p > 0) {
            return ['text-emerald-600 dark:text-emerald-400', '↑ ' . rtrim(rtrim(number_format($p, 1), '0'), '.') . '% más'];
        }
        if ($p < 0) {
            return ['text-red-600 dark:text-red-400', '↓ ' . rtrim(rtrim(number_format(abs($p), 1), '0'), '.') . '% menos'];
        }

        return ['text-gray-500 dark:text-gray-400', 'Sin cambio'];
    };

    $tarjetas = [
        [
            'titulo'   => 'Ingresos totales',
            'valor'    => $dinero($ingresosTotales),
            'pct'      => $porcentajeIngresos,
            'burbuja'  => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
            'icono'    => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'nota'     => null,
        ],
        [
            'titulo'   => 'Ventas realizadas',
            'valor'    => number_format($ventasRealizadas),
            'pct'      => $porcentajeVentas,
            'burbuja'  => 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400',
            'icono'    => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
            'nota'     => null,
        ],
        [
            'titulo'   => 'Clientes nuevos',
            'valor'    => number_format($clientesNuevos),
            'pct'      => $porcentajeClientes,
            'burbuja'  => 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400',
            'icono'    => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
            'nota'     => 'De todo el negocio, no se separa por sucursal.',
        ],
    ];
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    {{-- Filtros: periodo, sucursal y exportar --}}
    <form id="filtros-reporte" method="GET" action="{{ route('reportes.index') }}"
          class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

        {{-- Conserva el periodo al cambiar de sucursal. Va ANTES de los botones de periodo: si se pulsa uno, el botón gana. --}}
        <input type="hidden" name="periodo" value="{{ $periodo }}">

        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Periodo</span>

            <div class="flex items-center bg-gray-100 dark:bg-white/5 rounded-xl p-1 overflow-x-auto">
                @foreach(['hoy' => 'Hoy', 'semana' => 'Semana', 'mes' => 'Mes', 'personalizado' => 'Personalizado'] as $valor => $etiqueta)
                    <button type="submit" name="periodo" value="{{ $valor }}"
                            aria-pressed="{{ $periodo === $valor ? 'true' : 'false' }}"
                            class="px-4 py-1.5 rounded-lg text-sm font-semibold whitespace-nowrap transition-colors
                                   {{ $periodo === $valor
                                        ? 'bg-indigo-600 text-white'
                                        : 'text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/10' }}">
                        {{ $etiqueta }}
                    </button>
                @endforeach
            </div>

            @if($periodo === 'personalizado')
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <input type="text" id="rango_fechas_reporte" placeholder="Elige un rango" readonly
                               class="w-56 pl-3 pr-9 py-2 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <input type="hidden" name="fecha_desde" id="hidden_fecha_desde_reporte" value="{{ request('fecha_desde', $fechaInicio->format('Y-m-d')) }}">
                    <input type="hidden" name="fecha_hasta" id="hidden_fecha_hasta_reporte" value="{{ request('fecha_hasta', $fechaFin->format('Y-m-d')) }}">
                    <button type="submit" name="periodo" value="personalizado"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors">
                        Aplicar
                    </button>
                </div>
            @endif
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            @if($esAdmin)
                <div class="flex items-center gap-2">
                    <label for="filtro_sucursal" class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Sucursal</label>
                    <select id="filtro_sucursal" name="sucursal_id" onchange="document.getElementById('filtros-reporte').submit()"
                            class="px-3 py-2 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                        <option value="" @selected(! $sucursalFiltro)>Todas las sucursales</option>
                        @foreach($sucursales as $suc)
                            <option value="{{ $suc->id }}" @selected((string) $sucursalFiltro === (string) $suc->id)>{{ $suc->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <a href="{{ route('reportes.exportar.pdf', request()->query()) }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Exportar PDF
            </a>
        </div>
    </form>

    {{-- Errores de fechas --}}
    @if($errors->any())
        <div class="flex items-start gap-3 p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-xl text-sm font-medium">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="font-bold">Revisa las fechas del reporte:</p>
                <ul class="mt-1 space-y-0.5 font-medium">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Aviso: usuario de una sola sucursal --}}
    @if(! $esAdmin)
        <div class="flex items-start gap-3 p-4 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 text-amber-700 dark:text-amber-400 rounded-xl text-sm font-medium">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Estás viendo únicamente los datos de tu sucursal asignada.</span>
        </div>
    @endif

    {{-- Rango que se muestra y contra qué se compara --}}
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Periodo:
        <span class="font-semibold text-gray-800 dark:text-gray-100 tabular-nums">{{ $fechaInicio->format('d/m/Y') }} al {{ $fechaFin->format('d/m/Y') }}</span>
        <span class="mx-1 text-gray-300 dark:text-gray-600">·</span>
        Comparado con:
        <span class="font-semibold text-gray-800 dark:text-gray-100 tabular-nums">{{ $fechaInicioAnterior->format('d/m/Y') }} al {{ $fechaFinAnterior->format('d/m/Y') }}</span>
    </p>

    {{-- TARJETAS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($tarjetas as $t)
            @php [$claseCambio, $textoCambio] = $variacion((float) $t['pct']); @endphp
            <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl {{ $t['burbuja'] }} flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $t['icono'] }}"/></svg>
                    </span>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $t['titulo'] }}</p>
                </div>
                <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ $t['valor'] }}</p>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                    <span class="font-semibold {{ $claseCambio }}">{{ $textoCambio }}</span>
                    {{ $etiquetaComparativa }}
                </p>
                @if($t['nota'])
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ $t['nota'] }}</p>
                @endif
            </div>
        @endforeach
    </div>

    {{-- GRÁFICAS --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-6">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Ventas por día</h3>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Ingresos de cada día del periodo</p>

            @if($ventasPorDia->isEmpty())
                <div class="mt-4 h-56 border border-dashed border-gray-200 dark:border-white/10 rounded-xl flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                    <svg class="w-9 h-9 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 12l3-3 3 3 4-4M3 21h18"/></svg>
                    <p class="text-sm font-medium">Sin ventas en este periodo</p>
                </div>
            @else
                <div class="mt-4 relative h-56 w-full"><canvas id="chartVentasPorDia"></canvas></div>
            @endif
        </div>

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-6">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Ventas por sucursal</h3>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Ingresos de cada sucursal en el periodo</p>

            @if($ventasPorSucursal->isEmpty())
                <div class="mt-4 h-56 border border-dashed border-gray-200 dark:border-white/10 rounded-xl flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                    <svg class="w-9 h-9 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6m4 6V9m4 10V5M3 21h18"/></svg>
                    <p class="text-sm font-medium">Sin ventas en este periodo</p>
                </div>
            @else
                <div class="mt-4 relative h-56 w-full"><canvas id="chartVentasPorSucursal"></canvas></div>
            @endif
        </div>
    </div>

    {{-- PRODUCTOS MÁS VENDIDOS --}}
    <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Productos más vendidos</h3>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Los 5 con más piezas vendidas en el periodo</p>
            </div>
            @if(auth()->user()->tienePermiso('inventario.index'))
                <a href="{{ route('inventario.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Ver inventario</a>
            @endif
        </div>

        @if($productosMasVendidos->isEmpty())
            <div class="py-12 text-center">
                <span class="mx-auto w-11 h-11 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
                <p class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Todavía no hay productos vendidos</p>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">en este periodo.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-white/[0.03] text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            <th class="px-6 py-3 font-bold w-12">#</th>
                            <th class="px-6 py-3 font-bold">Producto</th>
                            <th class="px-6 py-3 font-bold text-center">Piezas vendidas</th>
                            <th class="px-6 py-3 font-bold text-right">Ingresos</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700 dark:text-gray-300 divide-y divide-gray-100 dark:divide-white/5">
                        @foreach($productosMasVendidos as $producto)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-6 py-3.5 text-gray-400 dark:text-gray-500 tabular-nums">{{ $loop->iteration }}</td>
                                <td class="px-6 py-3.5 font-semibold text-gray-900 dark:text-white">{{ $producto->nombre_producto }}</td>
                                <td class="px-6 py-3.5 text-center font-semibold text-indigo-600 dark:text-indigo-400 tabular-nums">{{ number_format((int) $producto->total_cantidad) }}</td>
                                <td class="px-6 py-3.5 text-right font-bold text-gray-900 dark:text-white tabular-nums">{{ $dinero($producto->total_ingresos) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

@if($ventasPorDia->isNotEmpty() || $ventasPorSucursal->isNotEmpty())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var oscuro    = document.documentElement.classList.contains('dark');
        var gridColor = oscuro ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
        var textColor = oscuro ? '#a3a3a3' : '#6b7280';
        var dinero    = function (v) {
            return '$' + Number(v).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };
        var opciones = function (conRejillaX) {
            return {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (c) { return ' ' + dinero(c.parsed.y); } } }
                },
                scales: {
                    x: { grid: { display: conRejillaX, color: gridColor }, ticks: { color: textColor, font: { size: 10 } } },
                    y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, font: { size: 10 }, callback: function (v) { return '$' + Number(v).toLocaleString('es-MX'); } } }
                }
            };
        };

        @if($ventasPorDia->isNotEmpty())
        new Chart(document.getElementById('chartVentasPorDia'), {
            type: 'line',
            data: {
                labels: @json($serieDiaria->pluck('dia')->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d/m'))->values()),
                datasets: [{
                    label: 'Ingresos',
                    data: @json($serieDiaria->pluck('total')->map(fn ($v) => (float) $v)->values()),
                    borderColor: '#6366F1',
                    backgroundColor: 'rgba(99, 102, 241, 0.12)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#6366F1',
                    pointRadius: 3
                }]
            },
            options: opciones(true)
        });
        @endif

        @if($ventasPorSucursal->isNotEmpty())
        new Chart(document.getElementById('chartVentasPorSucursal'), {
            type: 'bar',
            data: {
                labels: @json($ventasPorSucursal->map(fn ($v) => $v->sucursal->nombre ?? 'Sin sucursal')->values()),
                datasets: [{
                    label: 'Ingresos',
                    data: @json($ventasPorSucursal->pluck('total')->map(fn ($v) => (float) $v)->values()),
                    backgroundColor: '#6366F1',
                    borderRadius: 8
                }]
            },
            options: opciones(false)
        });
        @endif
    });
</script>
@endif

@if($periodo === 'personalizado')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var fechaDesdeActual = @json(request('fecha_desde', $fechaInicio->format('Y-m-d')));
        var fechaHastaActual = @json(request('fecha_hasta', $fechaFin->format('Y-m-d')));

        flatpickr("#rango_fechas_reporte", {
            mode: "range",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            locale: "es",
            maxDate: "today",
            defaultDate: [fechaDesdeActual, fechaHastaActual],
            onChange: function (selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    document.getElementById('hidden_fecha_desde_reporte').value = instance.formatDate(selectedDates[0], "Y-m-d");
                    document.getElementById('hidden_fecha_hasta_reporte').value = instance.formatDate(selectedDates[1], "Y-m-d");
                }
            }
        });
    });
</script>
@endif

@endsection
