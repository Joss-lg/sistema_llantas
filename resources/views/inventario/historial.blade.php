@extends('layouts.app')

@section('header_title', 'Historial de Movimientos')

@section('content')

<!-- Flatpickr: selector de calendario para rango de fechas -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/es.js"></script>

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
    <div>
        <a href="{{ route('inventario.index') }}"
           class="inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Volver a Inventario
        </a>
        <p class="text-sm text-gray-500 dark:text-gray-400">Registro de las entradas y salidas de inventario, con el motivo y quién las hizo.</p>
    </div>

    {{-- TARJETAS: los números son de TODO el filtro, no solo de la página --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Entradas</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 tabular-nums">+{{ number_format($totalEntradas) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">piezas ingresadas (incluye traspasos recibidos)</p>
        </div>

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Salidas</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-red-600 dark:text-red-400 tabular-nums">-{{ number_format($totalSalidas) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">piezas retiradas (incluye traspasos enviados)</p>
        </div>

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Movimientos</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($totalMovimientos) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">registros del filtro</p>
        </div>
    </div>
    <p class="-mt-3 text-xs text-gray-400 dark:text-gray-500">Las tres tarjetas cuentan todos los movimientos del filtro, no solo los de esta página.</p>

    {{-- FILTROS --}}
    <form method="GET" action="{{ route('inventario.historial') }}"
          class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5 sm:p-6">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">

            {{-- Producto --}}
            <div>
                <label for="filter_q" class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Buscar producto</label>
                <input type="text" id="filter_q" name="q" value="{{ request('q') }}" placeholder="Marca, medida..."
                       class="w-full px-3 py-2.5 bg-gray-50 dark:bg-white/[0.03] border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white text-sm font-medium rounded-xl outline-none placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
            </div>

            {{-- Tipo --}}
            <div>
                <label for="filter_tipo" class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Tipo</label>
                <select id="filter_tipo" name="tipo"
                        class="w-full px-3 py-2.5 bg-gray-50 dark:bg-white/[0.03] border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white text-sm font-medium rounded-xl outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                    <option value="">Todos los tipos</option>
                    <option value="entrada" {{ request('tipo') == 'entrada' ? 'selected' : '' }}>Entradas</option>
                    <option value="salida" {{ request('tipo') == 'salida' ? 'selected' : '' }}>Salidas</option>
                </select>
            </div>

            {{-- Motivo --}}
            <div>
                <label for="filter_motivo" class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Motivo</label>
                <select id="filter_motivo" name="motivo"
                        class="w-full px-3 py-2.5 bg-gray-50 dark:bg-white/[0.03] border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white text-sm font-medium rounded-xl outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                    <option value="">Todos los motivos</option>
                    @foreach($motivosDisponibles as $mot)
                        <option value="{{ $mot }}" {{ request('motivo') == $mot ? 'selected' : '' }}>{{ ucfirst($mot) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Sucursal (solo Administrador General) --}}
            @if($esAdmin)
            <div>
                <label for="filter_sucursal" class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Sucursal</label>
                <select id="filter_sucursal" name="sucursal_id"
                        class="w-full px-3 py-2.5 bg-gray-50 dark:bg-white/[0.03] border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white text-sm font-medium rounded-xl outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                    <option value="">Todas las sucursales</option>
                    @foreach($sucursales as $suc)
                        <option value="{{ $suc->id }}" {{ request('sucursal_id') == $suc->id ? 'selected' : '' }}>{{ $suc->nombre }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Rango de fechas (calendario) --}}
            <div class="sm:col-span-2">
                <label for="rango_fechas_movimientos" class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Rango de Fechas</label>
                <div class="relative">
                    <input type="text" id="rango_fechas_movimientos" placeholder="Selecciona un rango de fechas" readonly
                           class="w-full pl-3 pr-9 py-2.5 bg-gray-50 dark:bg-white/[0.03] border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white text-sm font-medium rounded-xl outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 cursor-pointer">
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <input type="hidden" name="fecha_desde" id="hidden_fecha_desde_mov" value="{{ request('fecha_desde') }}">
                <input type="hidden" name="fecha_hasta" id="hidden_fecha_hasta_mov" value="{{ request('fecha_hasta') }}">
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-end items-center gap-3 mt-5 pt-4 border-t border-gray-100 dark:border-white/10">
            <a href="{{ route('inventario.historial') }}"
               class="w-full sm:w-auto text-center px-5 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                Limpiar
            </a>
            <button type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                Buscar Movimientos
            </button>
        </div>
    </form>

    {{-- TABLA DE RESULTADOS --}}
    <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/[0.03] text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-white/10">
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Fecha</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Tipo</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Producto</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap text-right">Cantidad</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Motivo</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Sucursal</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Registró</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700 dark:text-gray-300 divide-y divide-gray-100 dark:divide-white/10">
                    @forelse($movimientos as $mov)
                        @php $esEntrada = $mov->tipo === 'entrada'; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors">

                            {{-- Fecha --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="font-bold text-gray-900 dark:text-white">{{ $mov->fecha?->format('d/m/Y') }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $mov->fecha?->format('H:i') }}</p>
                            </td>

                            {{-- Tipo --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($esEntrada)
                                    <span class="inline-flex items-center border px-3 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/50">Entrada</span>
                                @else
                                    <span class="inline-flex items-center border px-3 py-0.5 rounded-full text-xs font-bold bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800/50">Salida</span>
                                @endif
                            </td>

                            {{-- Producto --}}
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $mov->producto->marca ?? 'Producto eliminado' }}</p>
                                @if($mov->producto && $mov->producto->medida)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $mov->producto->medida }}</p>
                                @endif
                            </td>

                            {{-- Cantidad --}}
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <span class="text-base font-extrabold tabular-nums {{ $esEntrada ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">{{ $esEntrada ? '+' : '-' }}{{ number_format($mov->cantidad) }}</span>
                                <span class="text-xs text-gray-400 dark:text-gray-500">pzas</span>
                            </td>

                            {{-- Motivo y observaciones --}}
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-800 dark:text-gray-200">{{ $mov->motivo ? ucfirst($mov->motivo) : '—' }}</p>
                                @if($mov->observaciones)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs truncate" title="{{ $mov->observaciones }}">{{ $mov->observaciones }}</p>
                                @endif
                            </td>

                            {{-- Sucursal --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="font-medium text-gray-800 dark:text-gray-200">{{ $mov->sucursal->nombre ?? 'N/D' }}</p>
                            </td>

                            {{-- Quién lo registró --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="text-gray-600 dark:text-gray-400">{{ $mov->usuario->name ?? 'N/D' }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 mb-4">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </div>
                                <p class="text-base font-bold text-gray-800 dark:text-gray-200">Sin movimientos</p>
                                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 max-w-sm mx-auto">
                                    No hay registros con los filtros aplicados. Prueba limpiarlos.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if($movimientos->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-white/10">
                {{ $movimientos->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var defaultDates = [@js(request('fecha_desde')), @js(request('fecha_hasta'))].filter(Boolean);

        flatpickr("#rango_fechas_movimientos", {
            mode: "range",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            locale: "es",
            defaultDate: defaultDates,
            onChange: function (selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    document.getElementById('hidden_fecha_desde_mov').value = instance.formatDate(selectedDates[0], "Y-m-d");
                    document.getElementById('hidden_fecha_hasta_mov').value = instance.formatDate(selectedDates[1], "Y-m-d");
                } else if (selectedDates.length === 0) {
                    // Si se borra el rango, también se borran las fechas que viajan al filtrar
                    document.getElementById('hidden_fecha_desde_mov').value = '';
                    document.getElementById('hidden_fecha_hasta_mov').value = '';
                }
            }
        });
    });
</script>
@endsection
