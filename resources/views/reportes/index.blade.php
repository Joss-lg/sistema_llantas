@extends('layouts.app')

@section('header_title', 'Reportes y Estadísticas')

@section('content')

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>

<!-- Flatpickr: selector de calendario para rango de fechas -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/es.js"></script>

<style>
    [x-cloak] { display: none !important; }

    /* =========================================================
       FLATPICKR — estilo de marca + soporte modo oscuro
       ========================================================= */
    .flatpickr-calendar {
        border-radius: 16px;
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.18);
        border: 1px solid #e5e7eb;
        font-family: inherit;
    }
    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange { background: #818CF8 !important; border-color: #818CF8 !important; }
    .flatpickr-day.inRange {
        background: rgba(129, 140, 248, 0.15) !important;
        border-color: rgba(129, 140, 248, 0.15) !important;
        box-shadow: -5px 0 0 rgba(129, 140, 248, 0.15), 5px 0 0 rgba(129, 140, 248, 0.15);
    }
    .flatpickr-day:hover { background: #e0e7ff; }
    html.dark .flatpickr-calendar { background: #151515; border-color: #2e2e2e; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.5); }
    html.dark .flatpickr-day { color: #d4d4d4; }
    html.dark .flatpickr-day.flatpickr-disabled,
    html.dark .flatpickr-day.prevMonthDay,
    html.dark .flatpickr-day.nextMonthDay { color: #525252; }
    html.dark .flatpickr-day:hover { background: #27272a; }
    html.dark .flatpickr-months,
    html.dark .flatpickr-weekdays,
    html.dark span.flatpickr-weekday { background: #151515; color: #e5e5e5; }
    html.dark .flatpickr-current-month .cur-month,
    html.dark .flatpickr-current-month input.cur-year { color: #e5e5e5; }
    html.dark .flatpickr-prev-month svg,
    html.dark .flatpickr-next-month svg { fill: #a3a3a3; }
</style>

<div class="max-w-7xl mx-auto space-y-6 transition-colors duration-500"
     x-data="{ cargado: false }"
     x-init="setTimeout(() => cargado = true, 100);">

    {{-- Barra de periodo + sucursal + exportar --}}
    <form id="filtros-reporte" method="GET" action="{{ route('reportes.index') }}"
          class="bg-white dark:bg-[#151515] rounded-2xl border border-gray-200 dark:border-neutral-800 p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shadow-sm transform transition-all duration-700 delay-200 hover:shadow-md"
          :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-5'">

        <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full md:w-auto">
            <span class="text-[11px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-wide">Periodo</span>
            <div class="flex items-center bg-gray-100 dark:bg-[#0A0A0A] rounded-xl p-1 w-full sm:w-auto overflow-x-auto border dark:border-neutral-800">
                @foreach(['hoy' => 'Hoy', 'semana' => 'Semana', 'mes' => 'Mes', 'personalizado' => 'Personalizado'] as $valor => $etiqueta)
                    <button type="submit" name="periodo" value="{{ $valor }}"
                        class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-300 w-full sm:w-auto whitespace-nowrap
                        {{ $periodo === $valor ? 'bg-[#818CF8] text-white shadow-md transform scale-105' : 'text-gray-600 dark:text-neutral-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-neutral-800' }}">
                        {{ $etiqueta }}
                    </button>
                @endforeach
            </div>

            @if($periodo === 'personalizado')
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <input type="text" id="rango_fechas_reporte" placeholder="Selecciona un rango" readonly
                            class="pl-3 pr-9 py-2 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-700 dark:text-neutral-300 focus:outline-none focus:ring-2 focus:ring-[#818CF8] cursor-pointer w-56">
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input type="hidden" name="fecha_desde" id="hidden_fecha_desde_reporte" value="{{ request('fecha_desde', $fechaInicio->format('Y-m-d')) }}">
                    <input type="hidden" name="fecha_hasta" id="hidden_fecha_hasta_reporte" value="{{ request('fecha_hasta', $fechaFin->format('Y-m-d')) }}">
                    <button type="submit" name="periodo" value="personalizado" class="px-3 py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-xl text-xs font-bold">Ir</button>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-end">
            @if($esAdmin)
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-wide hidden sm:block">Sucursal</span>
                    <select name="sucursal_id" onchange="document.getElementById('filtros-reporte').submit()"
                        class="px-3 py-2 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-700 dark:text-neutral-300 focus:outline-none focus:ring-2 focus:ring-[#818CF8] transition-colors cursor-pointer hover:border-gray-300 dark:hover:border-neutral-600">
                        <option value="" {{ !$sucursalFiltro ? 'selected' : '' }}>Todas las sucursales</option>
                        @foreach($sucursales as $suc)
                            <option value="{{ $suc->id }}" {{ (string) $sucursalFiltro === (string) $suc->id ? 'selected' : '' }}>{{ $suc->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Preserva el periodo actual al exportar --}}
            <input type="hidden" name="periodo_actual_marker" value="1">
            <a href="{{ route('reportes.exportar.pdf', request()->query()) }}"
               class="group inline-flex items-center gap-2 px-4 py-2 bg-[#818CF8] text-white rounded-xl text-sm font-semibold hover:bg-[#6366F1] hover:shadow-lg hover:shadow-indigo-500/30 transform hover:-translate-y-0.5 transition-all duration-300">
                <svg class="w-4 h-4 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Exportar
            </a>
        </div>
    </form>

    @if(!$esAdmin)
        <div class="bg-amber-50 dark:bg-amber-950/30 border-l-4 border-amber-400 dark:border-amber-600 rounded-r-xl px-5 py-3 text-xs text-amber-700 dark:text-amber-400 font-medium">
            Estás viendo únicamente los datos de tu sucursal asignada.
        </div>
    @endif

    {{-- Métricas (Tarjetas) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Ingresos Totales -->
        <div class="bg-white dark:bg-[#151515] rounded-2xl p-6 border border-gray-200 dark:border-neutral-800 shadow-sm hover:shadow-xl hover:border-emerald-200 dark:hover:border-emerald-900/50 transform hover:-translate-y-1 transition-all duration-500 delay-300"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            <div class="flex items-center justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-500 flex items-center justify-center transition-transform hover:rotate-12 duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-[10px] font-bold text-gray-400 dark:text-neutral-600 uppercase">{{ $etiquetaComparativa }}</span>
            </div>
            <p class="text-xs text-gray-400 dark:text-neutral-500 font-semibold uppercase tracking-wide">Ingresos Totales</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1 transition-colors">${{ number_format($ingresosTotales, 2) }}</p>
            <p class="text-xs {{ $porcentajeIngresos >= 0 ? 'text-emerald-500' : 'text-red-500' }} font-medium mt-2 flex items-center gap-1 group">
                <span class="transform group-hover:translate-x-1 transition-transform">{{ $porcentajeIngresos >= 0 ? '↑' : '↓' }}</span>
                {{ abs($porcentajeIngresos) }}% {{ $porcentajeIngresos >= 0 ? 'incremento' : 'decremento' }}
            </p>
        </div>

        <!-- Ventas Realizadas -->
        <div class="bg-white dark:bg-[#151515] rounded-2xl p-6 border border-gray-200 dark:border-neutral-800 shadow-sm hover:shadow-xl hover:border-gray-400 dark:hover:border-neutral-600 transform hover:-translate-y-1 transition-all duration-500 delay-400"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            <div class="flex items-center justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-[#1A1A1A] dark:bg-[#2A2A2A] text-white flex items-center justify-center transition-transform hover:scale-110 duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                </div>
                <span class="text-[10px] font-bold text-gray-400 dark:text-neutral-600 uppercase">{{ $etiquetaComparativa }}</span>
            </div>
            <p class="text-xs text-gray-400 dark:text-neutral-500 font-semibold uppercase tracking-wide">Ventas Realizadas</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1 transition-colors">{{ $ventasRealizadas }}</p>
            <p class="text-xs {{ $porcentajeVentas >= 0 ? 'text-emerald-500' : 'text-red-500' }} font-medium mt-2 flex items-center gap-1 group">
                <span class="transform group-hover:translate-x-1 transition-transform">{{ $porcentajeVentas >= 0 ? '↑' : '↓' }}</span>
                {{ abs($porcentajeVentas) }}% {{ $porcentajeVentas >= 0 ? 'incremento' : 'decremento' }}
            </p>
        </div>

        <!-- Nuevos Clientes -->
        <div class="bg-white dark:bg-[#151515] rounded-2xl p-6 border border-gray-200 dark:border-neutral-800 shadow-sm hover:shadow-xl hover:border-indigo-200 dark:hover:border-indigo-900/50 transform hover:-translate-y-1 transition-all duration-500 delay-500"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            <div class="flex items-center justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-[#818CF8] dark:text-[#818CF8] flex items-center justify-center transition-transform hover:-rotate-12 duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m9-1.13a4 4 0 100-5.4M9 14a4 4 0 100-8 4 4 0 000 8z"/></svg>
                </div>
                <span class="text-[10px] font-bold text-gray-400 dark:text-neutral-600 uppercase">{{ $etiquetaComparativa }}</span>
            </div>
            <p class="text-xs text-gray-400 dark:text-neutral-500 font-semibold uppercase tracking-wide">Nuevos Clientes</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1 transition-colors">{{ $clientesNuevos }}</p>
            <p class="text-xs {{ $porcentajeClientes >= 0 ? 'text-[#818CF8]' : 'text-red-500' }} font-medium mt-2 flex items-center gap-1 group">
                <span class="transform group-hover:translate-x-1 transition-transform">{{ $porcentajeClientes >= 0 ? '↑' : '↓' }}</span>
                {{ abs($porcentajeClientes) }}% tasa
            </p>
            @if($esAdmin)
                <p class="text-[10px] text-gray-400 dark:text-neutral-600 mt-1.5 italic">Total general (no separable por sucursal)</p>
            @endif
        </div>
    </div>

    {{-- Gráficas --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Ventas por Día -->
        <div class="bg-white dark:bg-[#151515] rounded-2xl border border-gray-200 dark:border-neutral-800 p-6 shadow-sm hover:shadow-lg transition-all duration-500 delay-600 transform"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Ventas por Día</h3>
            </div>
            <p class="text-xs text-gray-400 dark:text-neutral-500 mb-4">Tendencia de ingresos en el tiempo</p>

            @if($ventasPorDia->isEmpty())
                <div class="h-56 border-2 border-dashed border-gray-200 dark:border-neutral-800 rounded-xl flex flex-col items-center justify-center text-gray-300 dark:text-neutral-700">
                    <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 12l3-3 3 3 4-4M3 21h18"/></svg>
                    <p class="text-sm font-medium">Sin ventas en este periodo</p>
                </div>
            @else
                <div class="relative h-56 w-full"><canvas id="chartVentasPorDia"></canvas></div>
            @endif
        </div>

        <!-- Ventas por Sucursal -->
        <div class="bg-white dark:bg-[#151515] rounded-2xl border border-gray-200 dark:border-neutral-800 p-6 shadow-sm hover:shadow-lg transition-all duration-500 delay-700 transform"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Ventas por Sucursal</h3>
            </div>
            <p class="text-xs text-gray-400 dark:text-neutral-500 mb-4">Desempeño comparativo regional</p>

            @if($ventasPorSucursal->isEmpty())
                <div class="h-56 border-2 border-dashed border-gray-200 dark:border-neutral-800 rounded-xl flex flex-col items-center justify-center text-gray-300 dark:text-neutral-700">
                    <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6m4 6V9m4 10V5M3 21h18"/></svg>
                    <p class="text-sm font-medium">Sin registros</p>
                </div>
            @else
                <div class="relative h-56 w-full"><canvas id="chartVentasPorSucursal"></canvas></div>
            @endif
        </div>
    </div>

    {{-- Productos más vendidos --}}
    <div class="bg-white dark:bg-[#151515] rounded-2xl border border-gray-200 dark:border-neutral-800 overflow-hidden shadow-sm hover:shadow-lg transition-all duration-500 delay-1000 transform"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-neutral-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Productos más Vendidos</h3>
                <p class="text-xs text-gray-400 dark:text-neutral-500 mt-0.5">Artículos con mayor rotación en el periodo seleccionado</p>
            </div>
            <a href="{{ route('inventario.index') }}" class="group text-xs font-semibold text-[#818CF8] hover:text-[#6366F1] dark:hover:text-[#a5b4fc] transition flex items-center gap-1">
                Ver catálogo completo
                <span class="transform group-hover:translate-x-1 transition-transform">→</span>
            </a>
        </div>

        @if($productosMasVendidos->isEmpty())
            <div class="p-6">
                <div class="text-center py-12">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-[#0A0A0A] text-gray-300 dark:text-neutral-700 flex items-center justify-center mx-auto mb-3 shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <p class="text-sm text-gray-400 dark:text-neutral-600">Aún no hay productos vendidos en este periodo</p>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-black/30 text-gray-400 dark:text-neutral-500 text-[10px] uppercase tracking-widest border-b border-gray-100 dark:border-white/5">
                            <th class="px-6 py-4 font-bold">Producto</th>
                            <th class="px-6 py-4 font-bold text-center">Unidades Vendidas</th>
                            <th class="px-6 py-4 font-bold text-right">Ingresos Generados</th>
                        </tr>
                    </thead>
                    <tbody class="text-[13px] text-gray-700 dark:text-gray-300 divide-y divide-gray-50/50 dark:divide-white/5">
                        @foreach($productosMasVendidos as $producto)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $producto->nombre_producto }}</td>
                                <td class="px-6 py-4 text-center font-semibold text-[#818CF8]">{{ (int) $producto->total_cantidad }}</td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">${{ number_format($producto->total_ingresos, 2) }}</td>
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
        const gridColor = document.documentElement.classList.contains('dark') ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
        const textColor = document.documentElement.classList.contains('dark') ? '#a3a3a3' : '#6b7280';

        @if($ventasPorDia->isNotEmpty())
        new Chart(document.getElementById('chartVentasPorDia'), {
            type: 'line',
            data: {
                labels: {!! json_encode($ventasPorDia->pluck('dia')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))) !!},
                datasets: [{
                    label: 'Ingresos',
                    data: {!! json_encode($ventasPorDia->pluck('total')->map(fn($v) => (float) $v)) !!},
                    borderColor: '#818CF8',
                    backgroundColor: 'rgba(129, 140, 248, 0.15)',
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#818CF8',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 10 } } },
                    y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 10 } } }
                }
            }
        });
        @endif

        @if($ventasPorSucursal->isNotEmpty())
        new Chart(document.getElementById('chartVentasPorSucursal'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($ventasPorSucursal->map(fn($v) => $v->sucursal->nombre ?? 'N/A')) !!},
                datasets: [{
                    label: 'Ingresos',
                    data: {!! json_encode($ventasPorSucursal->pluck('total')->map(fn($v) => (float) $v)) !!},
                    backgroundColor: '#818CF8',
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: textColor, font: { size: 10 } } },
                    y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 10 } } }
                }
            }
        });
        @endif
    });
</script>
@endif

@if($periodo === 'personalizado')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var fechaDesdeActual = "{{ request('fecha_desde', $fechaInicio->format('Y-m-d')) }}";
        var fechaHastaActual = "{{ request('fecha_hasta', $fechaFin->format('Y-m-d')) }}";

        flatpickr("#rango_fechas_reporte", {
            mode: "range",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            locale: "es",
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