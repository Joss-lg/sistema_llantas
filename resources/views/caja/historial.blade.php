@extends('layouts.app')

@section('header_title', 'Historial de Cajas')

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

@php
    // Un turno abierto por más de estas horas se marca como posible turno olvidado
    $umbralHorasAbierta = 12;
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    @if(session('success'))
        <div class="px-5 py-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-xl flex items-center gap-2 text-sm font-medium">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <p class="text-sm text-gray-500 dark:text-gray-400">Seguimiento de aperturas, cierres y montos de cada turno de caja.</p>

        @if($totalCajasAbiertas > 0)
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-900/40 rounded-xl shrink-0">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400">
                    {{ $totalCajasAbiertas }} {{ $totalCajasAbiertas === 1 ? 'caja abierta ahora' : 'cajas abiertas ahora' }}
                </span>
            </div>
        @endif
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('caja.historial') }}"
          class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5 sm:p-6 space-y-5">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Estado</label>
                <select name="estado" onchange="this.form.submit()" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-white/[0.03] border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white text-sm font-medium rounded-xl outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                    <option value="">Todos</option>
                    <option value="abierta" {{ request('estado') == 'abierta' ? 'selected' : '' }}>Abierta</option>
                    <option value="cerrada" {{ request('estado') == 'cerrada' ? 'selected' : '' }}>Cerrada</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Sucursal</label>
                <select name="sucursal_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-white/[0.03] border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white text-sm font-medium rounded-xl outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                    <option value="">Todas</option>
                    @foreach($sucursales as $suc)
                        <option value="{{ $suc->id }}" {{ request('sucursal_id') == $suc->id ? 'selected' : '' }}>{{ $suc->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Cajero</label>
                <select name="user_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-white/[0.03] border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white text-sm font-medium rounded-xl outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                    <option value="">Todos</option>
                    @foreach($cajeros as $cajero)
                        <option value="{{ $cajero->id }}" {{ request('user_id') == $cajero->id ? 'selected' : '' }}>{{ $cajero->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="rango_fechas_caja" class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Rango de Fechas</label>
                <div class="relative">
                    <input type="text" id="rango_fechas_caja" placeholder="Selecciona un rango" readonly
                        class="w-full pl-3 pr-9 py-2.5 bg-gray-50 dark:bg-white/[0.03] border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white text-sm font-medium rounded-xl outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 cursor-pointer">
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <input type="hidden" name="fecha_desde" id="hidden_fecha_desde_caja" value="{{ request('fecha_desde') }}">
                <input type="hidden" name="fecha_hasta" id="hidden_fecha_hasta_caja" value="{{ request('fecha_hasta') }}">
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-4 border-t border-gray-100 dark:border-white/10">
            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm transition-colors">Aplicar Filtros</button>
            @if(request()->hasAny(['sucursal_id', 'estado', 'user_id', 'fecha_desde', 'fecha_hasta']))
                <a href="{{ route('caja.historial') }}" class="inline-flex items-center text-xs font-bold text-red-600 dark:text-red-400 hover:underline uppercase tracking-wide">
                    Restablecer vista
                </a>
            @endif
        </div>
    </form>

    {{-- Tabla --}}
    <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/[0.03] text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-white/10">
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Cajero</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Turno</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap text-right">Saldo Inicial</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap text-right">Ventas</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap text-right">Saldo Final</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Diferencia</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700 dark:text-gray-300 divide-y divide-gray-100 dark:divide-white/10">
                    @forelse($cortes as $corte)
                        @php
                            $estaAbierta   = $corte->estado === 'abierta';
                            $horasAbierta  = ($estaAbierta && $corte->fecha_apertura)
                                ? (int) floor(\Carbon\Carbon::parse($corte->fecha_apertura)->diffInHours(now(), true))
                                : 0;
                            $turnoOlvidado = $estaAbierta && $horasAbierta >= $umbralHorasAbierta;
                            $ventasOtra    = (int) ($corte->ventas_otra_sucursal ?? 0);
                            $numVentas     = (int) ($corte->total_transacciones ?? 0);
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors {{ $turnoOlvidado ? 'bg-amber-50/60 dark:bg-amber-900/10' : ($estaAbierta ? 'bg-emerald-50/60 dark:bg-emerald-900/10' : '') }}">

                            {{-- Cajero + rol + sucursal --}}
                            <td class="px-5 py-4">
                                <p class="font-bold text-gray-900 dark:text-white">{{ $corte->user->name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $corte->user->role->nombre ?? 'Sin rol' }} · {{ $corte->sucursal->nombre ?? 'N/A' }}
                                </p>
                                @if($ventasOtra > 0)
                                    <span class="mt-1.5 flex w-fit items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-400 whitespace-nowrap"
                                          title="Estas ventas se cobraron en este turno pero se hicieron en una sucursal distinta a la del corte.">
                                        {{ $ventasOtra }} {{ $ventasOtra === 1 ? 'venta' : 'ventas' }} en otra sucursal
                                    </span>
                                @endif
                            </td>

                            {{-- Apertura y cierre en una sola celda --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <p>
                                    <span class="inline-block w-16 text-xs text-gray-400 dark:text-gray-500">Apertura</span>
                                    {{ $corte->fecha_apertura ? \Carbon\Carbon::parse($corte->fecha_apertura)->format('d/m/Y h:i A') : 'N/A' }}
                                </p>
                                <p class="mt-0.5">
                                    <span class="inline-block w-16 text-xs text-gray-400 dark:text-gray-500">Cierre</span>
                                    @if($corte->fecha_cierre)
                                        {{ \Carbon\Carbon::parse($corte->fecha_cierre)->format('d/m/Y h:i A') }}
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500 italic">En curso</span>
                                    @endif
                                </p>
                                @if($turnoOlvidado)
                                    <span class="mt-1.5 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300"
                                          title="Este turno lleva muchas horas abierto. Revisa si se olvidó cerrar la caja.">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Abierta hace
                                        @if($horasAbierta >= 24)
                                            {{ intdiv($horasAbierta, 24) }} {{ intdiv($horasAbierta, 24) === 1 ? 'día' : 'días' }}
                                        @else
                                            {{ $horasAbierta }} h
                                        @endif
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right font-semibold text-gray-600 dark:text-gray-300 tabular-nums whitespace-nowrap">${{ number_format($corte->saldo_inicial, 2) }}</td>

                            {{-- Ventas: total + detalle --}}
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <p class="font-bold text-indigo-600 dark:text-indigo-400 tabular-nums">${{ number_format($corte->total_ventas ?? 0, 2) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 tabular-nums">
                                    Efectivo ${{ number_format($corte->total_ventas_efectivo ?? 0, 2) }} · {{ $numVentas }} {{ $numVentas === 1 ? 'venta' : 'ventas' }}
                                </p>
                            </td>

                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                @if($corte->estado === 'cerrada')
                                    <span class="font-extrabold text-gray-900 dark:text-white tabular-nums">${{ number_format($corte->saldo_final, 2) }}</span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500 italic">Pendiente</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($corte->estado === 'cerrada' && !is_null($corte->diferencia))
                                    @if((float) $corte->diferencia > 0)
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-400 tabular-nums" title="Sobrante">+${{ number_format($corte->diferencia, 2) }}</span>
                                    @elseif((float) $corte->diferencia < 0)
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 tabular-nums" title="Faltante">-${{ number_format(abs($corte->diferencia), 2) }}</span>
                                    @else
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">Cuadra</span>
                                    @endif
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-center">
                                @if($estaAbierta)
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 inline-flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Abierta
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 dark:bg-neutral-800 text-gray-600 dark:text-neutral-400 inline-flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-neutral-600"></span>
                                        Cerrada
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500 font-medium">
                                No se encontraron cortes de caja con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($cortes, 'links'))
            <div class="px-6 py-4 border-t border-gray-100 dark:border-white/10">
                {{ $cortes->links() }}
            </div>
        @endif
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var defaultDates = [@js(request('fecha_desde')), @js(request('fecha_hasta'))].filter(Boolean);

        flatpickr("#rango_fechas_caja", {
            mode: "range",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            locale: "es",
            defaultDate: defaultDates,
            onChange: function (selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    document.getElementById('hidden_fecha_desde_caja').value = instance.formatDate(selectedDates[0], "Y-m-d");
                    document.getElementById('hidden_fecha_hasta_caja').value = instance.formatDate(selectedDates[1], "Y-m-d");
                } else if (selectedDates.length === 0) {
                    // Si se borra el rango, también se borran las fechas que viajan al filtrar
                    document.getElementById('hidden_fecha_desde_caja').value = '';
                    document.getElementById('hidden_fecha_hasta_caja').value = '';
                }
            }
        });
    });
</script>

@endsection
