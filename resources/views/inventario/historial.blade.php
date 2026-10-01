@extends('layouts.app')

@section('header_title', 'Historial de Movimientos')

@section('content')
<style>
    ::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }
    * { scrollbar-width: none !important; -ms-overflow-style: none !important; }

    @keyframes countUp {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .count-anim { animation: countUp 0.6s cubic-bezier(0.16,1,0.3,1) both; }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8"
     x-data="{ cargado: false }"
     x-init="setTimeout(() => cargado = true, 50)">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-5 transition-all duration-1000 ease-[cubic-bezier(0.16,1,0.3,1)] transform"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-8'">
        <div>
            <a href="{{ route('inventario.index') }}" class="group inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 dark:text-neutral-400 hover:text-[#818CF8] dark:hover:text-[#818CF8] transition-colors mb-2">
                <svg class="w-4 h-4 transition-transform duration-200 group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Volver a Inventario
            </a>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Historial de Movimientos</h1>
            <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1.5 font-medium">Registro completo de entradas y salidas del inventario.</p>
        </div>
    </div>

    {{-- Tarjetas de resumen --}}
    @php
        $pctEntradas = ($totalMovimientos > 0) ? round(($totalEntradas / $totalMovimientos) * 100) : 0;
        $pctSalidas  = ($totalMovimientos > 0) ? round(($totalSalidas  / $totalMovimientos) * 100) : 0;
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 transition-all duration-1000 ease-[cubic-bezier(0.16,1,0.3,1)] transform delay-100"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'">

        {{-- Entradas --}}
        <div class="bg-white dark:bg-[#0c0c0c] rounded-2xl border border-gray-200 dark:border-neutral-800/60 shadow-sm dark:shadow-2xl p-5 group hover:-translate-y-1 hover:shadow-md dark:hover:shadow-emerald-900/10 transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-emerald-900/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-900/30 px-2 py-0.5 rounded-full">{{ $pctEntradas }}%</span>
            </div>
            <p class="text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-1">Entradas</p>
            <p class="text-3xl font-black text-emerald-700 dark:text-emerald-400 count-anim">+{{ number_format($totalEntradas) }}</p>
            <p class="text-[11px] text-gray-400 dark:text-neutral-500 font-medium mt-1">piezas ingresadas</p>
            <div class="mt-4 h-1.5 bg-gray-100 dark:bg-neutral-800 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 dark:bg-emerald-400 rounded-full transition-all duration-1000"
                     :style="cargado ? 'width: {{ $pctEntradas }}%' : 'width: 0%'"></div>
            </div>
        </div>

        {{-- Salidas --}}
        <div class="bg-white dark:bg-[#0c0c0c] rounded-2xl border border-gray-200 dark:border-neutral-800/60 shadow-sm dark:shadow-2xl p-5 group hover:-translate-y-1 hover:shadow-md dark:hover:shadow-red-900/10 transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 flex items-center justify-center border border-red-100 dark:border-red-900/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                </div>
                <span class="text-xs font-black text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 px-2 py-0.5 rounded-full">{{ $pctSalidas }}%</span>
            </div>
            <p class="text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-1">Salidas</p>
            <p class="text-3xl font-black text-red-600 dark:text-red-400 count-anim">-{{ number_format($totalSalidas) }}</p>
            <p class="text-[11px] text-gray-400 dark:text-neutral-500 font-medium mt-1">piezas retiradas</p>
            <div class="mt-4 h-1.5 bg-gray-100 dark:bg-neutral-800 rounded-full overflow-hidden">
                <div class="h-full bg-red-500 dark:bg-red-400 rounded-full transition-all duration-1000"
                     :style="cargado ? 'width: {{ $pctSalidas }}%' : 'width: 0%'"></div>
            </div>
        </div>

        {{-- Total --}}
        <div class="bg-white dark:bg-[#0c0c0c] rounded-2xl border border-gray-200 dark:border-neutral-800/60 shadow-sm dark:shadow-2xl p-5 group hover:-translate-y-1 hover:shadow-md dark:hover:shadow-[#818CF8]/10 transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-[#818CF8]/10 text-[#818CF8] flex items-center justify-center border border-[#818CF8]/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <span class="text-xs font-black text-[#818CF8] bg-[#818CF8]/10 border border-[#818CF8]/20 px-2 py-0.5 rounded-full">Total</span>
            </div>
            <p class="text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-1">Movimientos</p>
            <p class="text-3xl font-black text-gray-800 dark:text-white count-anim">{{ number_format($totalMovimientos) }}</p>
            <p class="text-[11px] text-gray-400 dark:text-neutral-500 font-medium mt-1">registros filtrados</p>
            <div class="mt-4 h-1.5 bg-gray-100 dark:bg-neutral-800 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-emerald-400 to-red-400 rounded-full transition-all duration-1000"
                     :style="cargado ? 'width: 100%' : 'width: 0%'"></div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="transition-all duration-1000 ease-[cubic-bezier(0.16,1,0.3,1)] transform delay-200"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'">
        <form method="GET" action="{{ route('inventario.historial') }}"
              class="bg-white dark:bg-[#0c0c0c] rounded-2xl border border-gray-200 dark:border-neutral-800/60 shadow-sm dark:shadow-2xl p-5 space-y-4">

            <p class="text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.2em] mb-4">Filtrar resultados</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-2">Buscar producto</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-neutral-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                        </svg>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Marca, medida..."
                               class="bg-gray-50 dark:bg-[#141414] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-neutral-600 text-sm rounded-xl w-full pl-9 pr-3.5 py-2.5 outline-none focus:ring-2 focus:ring-[#818CF8]/40 focus:border-[#818CF8] transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-2">Tipo</label>
                    <select name="tipo" onchange="this.form.submit()"
                            class="bg-gray-50 dark:bg-[#141414] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white text-sm rounded-xl w-full px-3.5 py-2.5 outline-none focus:ring-2 focus:ring-[#818CF8]/40 focus:border-[#818CF8] transition-all cursor-pointer">
                        <option value="">Todos los tipos</option>
                        <option value="entrada" {{ request('tipo') == 'entrada' ? 'selected' : '' }}>✅ Entradas</option>
                        <option value="salida"  {{ request('tipo') == 'salida'  ? 'selected' : '' }}>🔻 Salidas</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-2">Motivo</label>
                    <select name="motivo" onchange="this.form.submit()"
                            class="bg-gray-50 dark:bg-[#141414] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white text-sm rounded-xl w-full px-3.5 py-2.5 outline-none focus:ring-2 focus:ring-[#818CF8]/40 focus:border-[#818CF8] transition-all cursor-pointer capitalize">
                        <option value="">Todos los motivos</option>
                        @foreach($motivosDisponibles as $mot)
                            <option value="{{ $mot }}" {{ request('motivo') == $mot ? 'selected' : '' }}>{{ ucfirst($mot) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @if($esAdmin)
                <div>
                    <label class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-2">Sucursal</label>
                    <select name="sucursal_id" onchange="this.form.submit()"
                            class="bg-gray-50 dark:bg-[#141414] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white text-sm rounded-xl w-full px-3.5 py-2.5 outline-none focus:ring-2 focus:ring-[#818CF8]/40 focus:border-[#818CF8] transition-all cursor-pointer">
                        <option value="">Todas las sucursales</option>
                        @foreach($sucursales as $suc)
                            <option value="{{ $suc->id }}" {{ request('sucursal_id') == $suc->id ? 'selected' : '' }}>{{ $suc->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-2">Desde</label>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" onchange="this.form.submit()"
                           class="bg-gray-50 dark:bg-[#141414] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white text-sm rounded-xl w-full px-3.5 py-2.5 outline-none focus:ring-2 focus:ring-[#818CF8]/40 focus:border-[#818CF8] transition-all [color-scheme:light] dark:[color-scheme:dark]">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-2">Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" onchange="this.form.submit()"
                           class="bg-gray-50 dark:bg-[#141414] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white text-sm rounded-xl w-full px-3.5 py-2.5 outline-none focus:ring-2 focus:ring-[#818CF8]/40 focus:border-[#818CF8] transition-all [color-scheme:light] dark:[color-scheme:dark]">
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1 border-t border-gray-100 dark:border-neutral-800/50">
                <button type="submit"
                        class="px-5 py-2.5 bg-[#818CF8] text-white text-sm font-bold rounded-xl hover:bg-[#6366F1] active:scale-95 transition-all shadow-[0_4px_14px_rgba(129,140,248,0.25)] hover:shadow-[0_6px_20px_rgba(129,140,248,0.4)]">
                    Aplicar filtros
                </button>
                @if(request()->hasAny(['tipo','motivo','q','fecha_desde','fecha_hasta','sucursal_id']))
                    <a href="{{ route('inventario.historial') }}"
                       class="px-5 py-2.5 bg-gray-100 dark:bg-[#1a1a1a] text-gray-600 dark:text-neutral-300 border border-gray-200 dark:border-neutral-800 rounded-xl text-sm font-semibold hover:bg-gray-200 dark:hover:bg-[#222] transition-all">
                        Limpiar filtros
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabla --}}
    <div class="bg-white dark:bg-[#0c0c0c] rounded-2xl border border-gray-200 dark:border-neutral-800/60 shadow-sm dark:shadow-2xl overflow-hidden transition-all duration-1000 ease-[cubic-bezier(0.16,1,0.3,1)] transform delay-300"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'">

        <div class="overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-gray-50/80 dark:bg-[#111111] border-b border-gray-200 dark:border-neutral-800/50">
                        <th class="px-6 py-5 text-[10px] font-black text-gray-500 dark:text-neutral-500 uppercase tracking-[0.2em]">Fecha</th>
                        <th class="px-6 py-5 text-[10px] font-black text-gray-500 dark:text-neutral-500 uppercase tracking-[0.2em]">Tipo</th>
                        <th class="px-6 py-5 text-[10px] font-black text-gray-500 dark:text-neutral-500 uppercase tracking-[0.2em]">Producto</th>
                        <th class="px-6 py-5 text-[10px] font-black text-gray-500 dark:text-neutral-500 uppercase tracking-[0.2em] text-center">Cantidad</th>
                        <th class="px-6 py-5 text-[10px] font-black text-gray-500 dark:text-neutral-500 uppercase tracking-[0.2em]">Motivo</th>
                        <th class="px-6 py-5 text-[10px] font-black text-gray-500 dark:text-neutral-500 uppercase tracking-[0.2em]">Sucursal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-neutral-800/30">
                    @forelse($movimientos as $mov)
                        @php $esEntrada = $mov->tipo === 'entrada'; @endphp
                        <tr class="group relative hover:bg-gray-50/60 dark:hover:bg-[#111] transition-all duration-200"
                            :class="cargado ? 'opacity-100' : 'opacity-0'"
                            style="transition-delay: {{ ($loop->index * 35) + 350 }}ms">

                            {{-- Borde lateral de color --}}
                            <td class="pl-0 pr-0 py-0 w-0">
                                <div class="absolute left-0 top-0 bottom-0 w-[3px] {{ $esEntrada ? 'bg-emerald-400' : 'bg-red-400' }} opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-r"></div>
                            </td>

                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-gray-800 dark:text-white">
                                    {{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}
                                </p>
                                <p class="text-xs font-medium text-gray-400 dark:text-neutral-500 mt-0.5">
                                    {{ \Carbon\Carbon::parse($mov->fecha)->format('H:i') }}
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                @if($esEntrada)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                        Entrada
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800/40">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                        Salida
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg {{ $esEntrada ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30' : 'bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400 border border-red-100 dark:border-red-800/30' }} flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-[#818CF8] transition-colors duration-200">
                                            {{ $mov->producto->marca ?? 'Producto eliminado' }}
                                        </p>
                                        @if($mov->producto && $mov->producto->medida)
                                            <p class="text-xs font-medium text-gray-400 dark:text-neutral-500 mt-0.5">{{ $mov->producto->medida }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm font-black min-w-[56px]
                                    {{ $esEntrada
                                        ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30'
                                        : 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border border-red-100 dark:border-red-800/30' }}">
                                    {{ $esEntrada ? '+' : '-' }}{{ $mov->cantidad }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-[11px] font-bold bg-gray-50 dark:bg-[#141414] text-gray-600 dark:text-neutral-400 border border-gray-200 dark:border-neutral-800 capitalize">
                                    {{ $mov->motivo ?? '—' }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-[#818CF8] opacity-60"></div>
                                    <span class="text-sm font-semibold text-gray-600 dark:text-neutral-400">
                                        {{ $mov->sucursal->nombre ?? 'N/D' }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-24 text-center">
                                <div class="flex flex-col items-center justify-center"
                                     :class="cargado ? 'opacity-100 scale-100' : 'opacity-0 scale-95'"
                                     style="transition: all 0.7s ease; transition-delay: 400ms;">
                                    <div class="w-16 h-16 bg-gray-50 dark:bg-[#141414] rounded-2xl border border-gray-100 dark:border-neutral-800 flex items-center justify-center mb-4 shadow-inner">
                                        <svg class="w-8 h-8 text-gray-300 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-black text-gray-900 dark:text-white">Sin movimientos</h3>
                                    <p class="text-xs font-semibold mt-1.5 text-gray-400 dark:text-neutral-500 max-w-xs">No hay registros con los filtros aplicados. Prueba limpiarlos.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movimientos->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-neutral-800/50 bg-gray-50/40 dark:bg-[#0a0a0a]">
                {{ $movimientos->links() }}
            </div>
        @endif
    </div>

</div>
@endsection