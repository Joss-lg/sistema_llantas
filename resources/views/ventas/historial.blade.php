@extends('layouts.app')

@section('header_title', 'Historial de Ventas')

@section('content')

<!-- Flatpickr (calendario): viene dentro del proyecto, cargado desde el diseño principal -->

<style>
    /* =========================================================
       FLATPICKR — colores del sistema + modo oscuro
       ========================================================= */
    .flatpickr-calendar {
        border-radius: 16px;
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.18);
        border: 1px solid #e5e7eb;
        font-family: inherit;
    }
    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange { background: #4F46E5 !important; border-color: #4F46E5 !important; }
    .flatpickr-day.inRange {
        background: rgba(79, 70, 229, 0.12) !important;
        border-color: rgba(79, 70, 229, 0.12) !important;
        box-shadow: -5px 0 0 rgba(79, 70, 229, 0.12), 5px 0 0 rgba(79, 70, 229, 0.12);
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

<div class="max-w-7xl mx-auto space-y-6">

    {{-- Encabezado --}}
    <p class="text-sm text-gray-500 dark:text-gray-400">Consulta las ventas por folio, fecha o sucursal y reimprime sus tickets.</p>

    {{-- TARJETAS: los números son de TODO el filtro, no solo de la página --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Ventas encontradas</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($ventas->total()) }}</p>
        </div>

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Piezas vendidas</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($piezasFiltradas) }}</p>
        </div>

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total vendido</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 tabular-nums">${{ number_format($totalFiltrado, 2) }}</p>
        </div>
    </div>
    <p class="-mt-3 text-xs text-gray-400 dark:text-gray-500">Las tres tarjetas cuentan todas las ventas del filtro, no solo las de esta página.</p>

    {{-- FILTROS --}}
    <form method="GET" action="{{ route('ventas.historial') }}"
          class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5 sm:p-6">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">

            {{-- Folio --}}
            <div>
                <label for="filter_folio" class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Folio del Ticket</label>
                <input type="text" id="filter_folio" name="folio" value="{{ request('folio') }}" placeholder="Ej. VNT-2026..."
                       class="w-full px-3 py-2.5 bg-gray-50 dark:bg-white/[0.03] border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white text-sm font-medium rounded-xl outline-none placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
            </div>

            {{-- Rango de fechas (calendario) --}}
            <div class="{{ (isset($esAdmin) && $esAdmin) ? 'lg:col-span-2' : 'lg:col-span-3' }}">
                <label for="rango_fechas_ventas" class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Rango de Fechas</label>
                <div class="relative">
                    <input type="text" id="rango_fechas_ventas" placeholder="Selecciona un rango de fechas" readonly
                           class="w-full pl-3 pr-9 py-2.5 bg-gray-50 dark:bg-white/[0.03] border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white text-sm font-medium rounded-xl outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 cursor-pointer">
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <input type="hidden" name="fecha_inicio" id="hidden_fecha_inicio_ventas" value="{{ request('fecha_inicio') }}">
                <input type="hidden" name="fecha_fin" id="hidden_fecha_fin_ventas" value="{{ request('fecha_fin') }}">
            </div>

            {{-- Sucursal (solo Administrador General) --}}
            @if(isset($esAdmin) && $esAdmin)
            <div>
                <label for="filter_sucursal" class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Sucursal</label>
                <select id="filter_sucursal" name="sucursal_id"
                        class="w-full px-3 py-2.5 bg-gray-50 dark:bg-white/[0.03] border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white text-sm font-medium rounded-xl outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                    <option value="">Todas las sucursales</option>
                    @foreach($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}" {{ request('sucursal_id') == $sucursal->id ? 'selected' : '' }}>{{ $sucursal->nombre }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>

        <div class="flex flex-col sm:flex-row justify-end items-center gap-3 mt-5 pt-4 border-t border-gray-100 dark:border-white/10">
            <a href="{{ route('ventas.historial') }}"
               class="w-full sm:w-auto text-center px-5 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                Limpiar
            </a>
            <button type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                Buscar Ventas
            </button>
        </div>
    </form>

    {{-- TABLA DE RESULTADOS --}}
    <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/[0.03] text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-white/10">
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Folio / Fecha</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Cliente</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Sucursal / Cajero</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Pago</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap text-right">Artículos</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap text-right">Total</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap text-right">Ticket</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700 dark:text-gray-300 divide-y divide-gray-100 dark:divide-white/10">
                    @forelse($ventas as $venta)
                        @php
                            $metodo = $venta->metodo_pago ?? 'Efectivo';
                            $clasePago = match ($metodo) {
                                'Tarjeta'       => 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800/50',
                                'Transferencia' => 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-400 border-purple-200 dark:border-purple-800/50',
                                default         => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/50',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors">

                            {{-- Folio / Fecha --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="font-bold text-gray-900 dark:text-white">{{ $venta->folio }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y H:i') }}</p>
                            </td>

                            {{-- Cliente --}}
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-800 dark:text-gray-200">{{ $venta->nombre_cliente_temporal ?: 'Público General' }}</p>
                                @if($venta->requiere_factura)
                                    <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wide bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50">
                                        Req. Factura
                                    </span>
                                @endif
                            </td>

                            {{-- Sucursal / Cajero --}}
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-800 dark:text-gray-200">{{ $venta->sucursal->nombre ?? 'Sucursal ' . $venta->sucursal_id }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $venta->user->name ?? 'Cajero ID: ' . $venta->user_id }}</p>
                            </td>

                            {{-- Forma de pago --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center border px-3 py-0.5 rounded-full text-xs font-bold {{ $clasePago }}">{{ $metodo }}</span>
                            </td>

                            {{-- Artículos --}}
                            <td class="px-5 py-4 text-right whitespace-nowrap tabular-nums">
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $venta->detalles ? $venta->detalles->sum('cantidad') : 0 }}</span>
                                <span class="text-xs text-gray-400 dark:text-gray-500">pzas</span>
                            </td>

                            {{-- Total --}}
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <span class="text-base font-extrabold text-emerald-600 dark:text-emerald-400 tabular-nums">${{ number_format($venta->total, 2) }}</span>
                            </td>

                            {{-- Reimprimir ticket --}}
                            <td class="px-5 py-4 text-right">
                                <button type="button" onclick="imprimirTicket('{{ route('ventas.ticket', $venta->id) }}')"
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-full text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-colors"
                                        title="Reimprimir Ticket">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 mb-4">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="text-base font-bold text-gray-800 dark:text-gray-200">Sin resultados</p>
                                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 max-w-sm mx-auto">
                                    No se encontraron ventas con los filtros seleccionados. Intenta modificar los criterios de búsqueda.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if(method_exists($ventas, 'hasPages') && $ventas->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-white/10">
                {{ $ventas->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var defaultDates = [@js(request('fecha_inicio')), @js(request('fecha_fin'))].filter(Boolean);

        flatpickr("#rango_fechas_ventas", {
            mode: "range",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            locale: "es",
            defaultDate: defaultDates,
            onChange: function (selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    document.getElementById('hidden_fecha_inicio_ventas').value = instance.formatDate(selectedDates[0], "Y-m-d");
                    document.getElementById('hidden_fecha_fin_ventas').value = instance.formatDate(selectedDates[1], "Y-m-d");
                } else if (selectedDates.length === 0) {
                    // Si se borra el rango, también se borran las fechas que viajan al filtrar
                    document.getElementById('hidden_fecha_inicio_ventas').value = '';
                    document.getElementById('hidden_fecha_fin_ventas').value = '';
                }
            }
        });
    });
</script>
@endsection
