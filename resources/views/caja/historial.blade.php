@extends('layouts.app')

@section('header_title', 'Historial de Cajas')

@section('content')

<!-- Flatpickr: selector de calendario para rango de fechas -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/es.js"></script>

<style>
    *::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }
    * { -ms-overflow-style: none !important; scrollbar-width: none !important; }
    .row-anim { opacity: 0; animation: fadeInUp 0.5s ease-out forwards; }
    @keyframes fadeInUp { 0% { transform: translateY(15px); opacity: 0; } 100% { transform: translateY(0); opacity: 1; } }

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

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-100 dark:border-emerald-900/50 text-emerald-800 dark:text-emerald-400 px-5 py-4 rounded-2xl flex items-center gap-3">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-sm font-semibold">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Encabezado --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Historial de Cajas</h2>
            <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1.5 font-medium">Seguimiento de aperturas, cierres y montos de cada turno de caja.</p>
        </div>

        @if($totalCajasAbiertas > 0)
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-900/40 rounded-xl">
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
          class="bg-white/60 dark:bg-[#111111]/60 backdrop-blur-2xl p-6 sm:p-8 rounded-3xl border border-gray-100/80 dark:border-white/5 shadow-sm space-y-5">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-widest mb-2">Estado</label>
                <select name="estado" onchange="this.form.submit()" class="w-full px-3 py-2.5 bg-gray-50/50 dark:bg-black/50 border border-gray-200/80 dark:border-white/10 text-gray-900 dark:text-white text-[13px] font-medium rounded-xl outline-none focus:ring-2 focus:ring-[#818CF8]/30">
                    <option value="">Todos</option>
                    <option value="abierta" {{ request('estado') == 'abierta' ? 'selected' : '' }}>Abierta</option>
                    <option value="cerrada" {{ request('estado') == 'cerrada' ? 'selected' : '' }}>Cerrada</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-widest mb-2">Sucursal</label>
                <select name="sucursal_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 bg-gray-50/50 dark:bg-black/50 border border-gray-200/80 dark:border-white/10 text-gray-900 dark:text-white text-[13px] font-medium rounded-xl outline-none focus:ring-2 focus:ring-[#818CF8]/30">
                    <option value="">Todas</option>
                    @foreach($sucursales as $suc)
                        <option value="{{ $suc->id }}" {{ request('sucursal_id') == $suc->id ? 'selected' : '' }}>{{ $suc->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-widest mb-2">Cajero</label>
                <select name="user_id" onchange="this.form.submit()" class="w-full px-3 py-2.5 bg-gray-50/50 dark:bg-black/50 border border-gray-200/80 dark:border-white/10 text-gray-900 dark:text-white text-[13px] font-medium rounded-xl outline-none focus:ring-2 focus:ring-[#818CF8]/30">
                    <option value="">Todos</option>
                    @foreach($cajeros as $cajero)
                        <option value="{{ $cajero->id }}" {{ request('user_id') == $cajero->id ? 'selected' : '' }}>{{ $cajero->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="rango_fechas_caja" class="block text-[10px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-widest mb-2">Rango de Fechas</label>
                <div class="relative">
                    <input type="text" id="rango_fechas_caja" placeholder="Selecciona un rango" readonly
                        class="w-full pl-3 pr-9 py-2.5 bg-gray-50/50 dark:bg-black/50 border border-gray-200/80 dark:border-white/10 text-gray-900 dark:text-white text-[13px] font-medium rounded-xl outline-none focus:ring-2 focus:ring-[#818CF8]/30 cursor-pointer">
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <input type="hidden" name="fecha_desde" id="hidden_fecha_desde_caja" value="{{ request('fecha_desde') }}">
                <input type="hidden" name="fecha_hasta" id="hidden_fecha_hasta_caja" value="{{ request('fecha_hasta') }}">
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-4 border-t border-gray-100/50 dark:border-white/5">
            <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold rounded-xl text-xs">Aplicar Filtros</button>
            @if(request()->hasAny(['sucursal_id', 'estado', 'user_id', 'fecha_desde', 'fecha_hasta']))
                <a href="{{ route('caja.historial') }}" class="inline-flex items-center text-xs font-bold text-red-500 uppercase tracking-wide">
                    Restablecer vista
                </a>
            @endif
        </div>
    </form>

    {{-- Tabla --}}
    <div class="bg-white/80 dark:bg-[#111111]/80 backdrop-blur-2xl rounded-3xl shadow-sm border border-gray-100/80 dark:border-white/5 flex flex-col">
        <div class="overflow-x-auto overflow-y-hidden w-full rounded-t-3xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-black/30 text-gray-400 dark:text-neutral-500 text-[10px] uppercase tracking-widest border-b border-gray-100 dark:border-white/5">
                        <th class="px-6 py-5 font-bold whitespace-nowrap">Cajero</th>
                        <th class="px-6 py-5 font-bold whitespace-nowrap">Sucursal</th>
                        <th class="px-6 py-5 font-bold whitespace-nowrap">Apertura</th>
                        <th class="px-6 py-5 font-bold whitespace-nowrap">Cierre</th>
                        <th class="px-6 py-5 font-bold whitespace-nowrap">Saldo Inicial</th>
                        <th class="px-6 py-5 font-bold whitespace-nowrap">Ventas (Efectivo)</th>
                        <th class="px-6 py-5 font-bold whitespace-nowrap">Ventas (Total)</th>
                        <th class="px-6 py-5 font-bold whitespace-nowrap">Saldo Final</th>
                        <th class="px-6 py-5 font-bold text-center whitespace-nowrap">Transacciones</th>
                        <th class="px-6 py-5 font-bold text-center whitespace-nowrap">Estado</th>
                    </tr>
                </thead>
                <tbody class="text-[13px] text-gray-700 dark:text-gray-300 divide-y divide-gray-50/50 dark:divide-white/5">
                    @forelse($cortes as $corte)
                        <tr class="row-anim hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors {{ $corte->estado === 'abierta' ? 'bg-emerald-50/50 dark:bg-emerald-900/10' : '' }}">
                            <td class="px-6 py-4">
                                <p class="font-bold text-gray-900 dark:text-white text-sm">{{ $corte->user->name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-400 font-medium">{{ $corte->user->role->nombre ?? 'Sin rol' }}</p>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-600 dark:text-neutral-300">{{ $corte->sucursal->nombre ?? 'N/A' }}</td>
                            <td class="px-6 py-4 font-medium text-gray-600 dark:text-neutral-300 whitespace-nowrap">
                                {{ $corte->fecha_apertura ? \Carbon\Carbon::parse($corte->fecha_apertura)->format('d/m/Y h:i A') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-600 dark:text-neutral-300 whitespace-nowrap">
                                @if($corte->fecha_cierre)
                                    {{ \Carbon\Carbon::parse($corte->fecha_cierre)->format('d/m/Y h:i A') }}
                                @else
                                    <span class="text-gray-400 dark:text-neutral-600 italic">En curso</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-500">${{ number_format($corte->saldo_inicial, 2) }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-500">${{ number_format($corte->total_ventas_efectivo ?? 0, 2) }}</td>
                            <td class="px-6 py-4 font-bold text-[#818CF8]">${{ number_format($corte->total_ventas ?? 0, 2) }}</td>
                            <td class="px-6 py-4">
                                @if($corte->estado === 'cerrada')
                                    <span class="font-extrabold text-gray-900 dark:text-white">${{ number_format($corte->saldo_final, 2) }}</span>
                                @else
                                    <span class="text-gray-400 dark:text-neutral-600 italic">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-gray-700 dark:text-neutral-300">{{ $corte->total_transacciones ?? 0 }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($corte->estado === 'abierta')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 inline-flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Abierta
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 dark:bg-neutral-800 text-gray-500 dark:text-neutral-400 inline-flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-neutral-600"></span>
                                        Cerrada
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-gray-400 font-medium">
                                No se encontraron cortes de caja con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($cortes, 'links'))
            <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5">
                {{ $cortes->links() }}
            </div>
        @endif
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var fechaDesdeActual = "{{ request('fecha_desde') }}";
        var fechaHastaActual = "{{ request('fecha_hasta') }}";
        var defaultDates = [fechaDesdeActual, fechaHastaActual].filter(Boolean);

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
                }
            }
        });
    });
</script>

@endsection