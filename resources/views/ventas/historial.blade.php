@extends('layouts.app')

@section('header_title', 'Historial de Ventas')

@section('content')

<style>
    @keyframes rowIn {
        from { opacity: 0; transform: translateX(-6px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes pulseRing {
        0% { transform: scale(1); opacity: 0.8; }
        100% { transform: scale(2.5); opacity: 0; }
    }
    @keyframes popIn {
        0% { opacity: 0; transform: scale(0.85); }
        60% { opacity: 1; transform: scale(1.04); }
        100% { opacity: 1; transform: scale(1); }
    }
    @keyframes shineSweep {
        0% { transform: translateX(-120%) skewX(-20deg); }
        100% { transform: translateX(220%) skewX(-20deg); }
    }
    @keyframes iconBreathe {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.12); }
    }

    .row-anim { animation: rowIn 0.35s ease-out both; opacity: 0; }
    .pulse-dot::before {
        content: ''; position: absolute; inset: 0; border-radius: 999px;
        background: currentColor; opacity: 0.5;
        animation: pulseRing 1.8s ease-out infinite;
    }
    .stat-number { animation: popIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both; }

    /* Botón principal con brillo diagonal en hover */
    .btn-shine { position: relative; overflow: hidden; }
    .btn-shine::after {
        content: '';
        position: absolute;
        top: 0; left: 0;
        width: 40%; height: 100%;
        background: linear-gradient(115deg, transparent, rgba(255,255,255,0.35), transparent);
        transform: translateX(-120%) skewX(-20deg);
        pointer-events: none;
    }
    .btn-shine:hover::after { animation: shineSweep 0.9s ease forwards; }

    .icon-breathe-hover:hover svg { animation: iconBreathe 1s ease-in-out infinite; }

    @media (prefers-reduced-motion: reduce) {
        .row-anim, .pulse-dot::before, .stat-number, .btn-shine::after, .icon-breathe-hover:hover svg {
            animation: none !important; opacity: 1 !important;
        }
    }

    /* ── Scrollbar personalizado (apunta directo a html/body, no solo "*") ── */
    html::-webkit-scrollbar,
    body::-webkit-scrollbar,
    *::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    html::-webkit-scrollbar-track,
    body::-webkit-scrollbar-track,
    *::-webkit-scrollbar-track {
        background: transparent;
    }

    html::-webkit-scrollbar-thumb,
    body::-webkit-scrollbar-thumb,
    *::-webkit-scrollbar-thumb {
        background-color: #d1d5db;
        border-radius: 9999px;
        border: 2px solid transparent;
        background-clip: padding-box;
        transition: background-color 0.2s ease;
    }

    html::-webkit-scrollbar-thumb:hover,
    body::-webkit-scrollbar-thumb:hover,
    *::-webkit-scrollbar-thumb:hover {
        background-color: #D32030;
    }

    html::-webkit-scrollbar-button,
    body::-webkit-scrollbar-button,
    *::-webkit-scrollbar-button {
        display: none;
        width: 0;
        height: 0;
    }

    html.dark ::-webkit-scrollbar-thumb { background-color: #3f3f46; }
    html.dark ::-webkit-scrollbar-thumb:hover { background-color: #D32030; }

    html, body, * {
        scrollbar-width: thin;
        scrollbar-color: #d1d5db transparent;
    }
    html.dark, html.dark body, html.dark * {
        scrollbar-color: #3f3f46 transparent;
    }
</style>

<!-- Contenedor principal con el estado de carga y contexto relativo para el fondo -->
<div class="max-w-7xl mx-auto transition-colors duration-300 relative px-4 py-6 sm:px-0 sm:py-0" x-data="{ cargado: false }" x-init="setTimeout(() => cargado = true, 50)">

    {{-- Fondo animado tipo Aurora --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0 opacity-20 dark:opacity-10 transition-opacity duration-500" aria-hidden="true">
        <div class="absolute -top-[160px] -left-[80px] w-[420px] h-[420px] rounded-full bg-[#D32030] blur-[90px] animate-[pulse_16s_ease-in-out_infinite]"></div>
        <div class="absolute top-[40px] -right-[140px] w-[420px] h-[420px] rounded-full bg-blue-500 blur-[90px] animate-[pulse_20s_ease-in-out_infinite]" style="animation-delay: -6s;"></div>
    </div>

    <!-- Contenido principal que va por encima del fondo (z-10) -->
    <div class="relative z-10 space-y-6">

        {{-- ── Encabezado ── --}}
        <div class="flex items-center justify-between flex-wrap gap-3 transform transition-all duration-700 ease-out"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'">
            <div>
                <p class="text-[11px] font-bold tracking-[3px] text-[#D32030] dark:text-red-500 uppercase">Llantas Económicas · Chalco</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white tracking-tight transition-colors">Historial de Ventas</h1>
            </div>
        </div>

        {{-- ── Estadísticas rápidas (Animación en Cascada) ── --}}
        @php
            $sumaPagina = $ventas->sum('total');
            $piezasPagina = $ventas->sum(fn($v) => $v->detalles->sum('cantidad'));
        @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Stat 1 -->
            <div class="bg-[#0F0F0F] dark:bg-[#0A0A0A] border border-transparent dark:border-neutral-800 rounded-2xl p-6 text-white shadow-lg transform transition-all duration-500 ease-out hover:-translate-y-1 hover:shadow-xl hover:shadow-red-500/10 delay-100"
                 :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
                <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-neutral-500">Ventas encontradas</p>
                <p class="text-3xl font-bold mt-2 stat-number" style="animation-delay: 0.35s">{{ $ventas->total() }}</p>
            </div>

            <!-- Stat 2 -->
            <div class="bg-white dark:bg-[#151515] rounded-2xl p-6 border border-gray-200 dark:border-neutral-800 shadow-sm transform transition-all duration-500 ease-out hover:-translate-y-1 hover:shadow-xl dark:hover:shadow-black/40 delay-150"
                 :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
                <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-neutral-500">En esta página</p>
                <p class="text-3xl font-bold mt-2 text-gray-900 dark:text-white transition-colors stat-number" style="animation-delay: 0.4s">{{ $ventas->count() }}</p>
            </div>

            <!-- Stat 3 -->
            <div class="bg-white dark:bg-[#151515] rounded-2xl p-6 border border-gray-200 dark:border-neutral-800 shadow-sm transform transition-all duration-500 ease-out hover:-translate-y-1 hover:shadow-xl dark:hover:shadow-black/40 delay-200"
                 :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
                <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-neutral-500">Piezas (pág.)</p>
                <p class="text-3xl font-bold mt-2 text-gray-900 dark:text-white transition-colors stat-number" style="animation-delay: 0.45s">{{ $piezasPagina }}</p>
            </div>

            <!-- Stat 4 -->
            <div class="bg-emerald-600 dark:bg-emerald-600 rounded-2xl p-6 text-white shadow-md transform transition-all duration-500 ease-out hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-500/20 delay-300"
                 :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
                <p class="text-[11px] font-bold uppercase tracking-widest text-emerald-100 dark:text-emerald-200">Total (pág.)</p>
                <p class="text-3xl font-bold mt-2 stat-number" style="animation-delay: 0.5s">${{ number_format($sumaPagina, 2) }}</p>
            </div>
        </div>

        {{-- ── Filtros ── --}}
        <div class="bg-white dark:bg-[#151515] rounded-2xl shadow-sm border border-gray-200 dark:border-neutral-800 p-5 sm:p-6 transform transition-all duration-700 ease-out delay-400"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            <div class="flex items-center gap-2 mb-5 group/filterhead">
                <svg class="w-4 h-4 text-[#D32030] dark:text-red-500 transition-transform duration-500 group-hover/filterhead:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                <p class="text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-neutral-400 transition-colors">Filtrar resultados</p>
            </div>

            <form method="GET" action="{{ route('ventas.historial') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 items-end">
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-neutral-400 uppercase tracking-wider mb-2">Folio del Ticket</label>
                    <input type="text" name="folio" value="{{ request('folio') }}" placeholder="Ej. VNT-2026..."
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-neutral-600 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#D32030] focus:border-transparent focus:scale-[1.01] hover:border-gray-300 dark:hover:border-neutral-700 shadow-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-neutral-400 uppercase tracking-wider mb-2">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}"
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#D32030] focus:border-transparent focus:scale-[1.01] hover:border-gray-300 dark:hover:border-neutral-700 shadow-sm color-scheme-dark">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-neutral-400 uppercase tracking-wider mb-2">Fecha Fin</label>
                    <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}"
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#D32030] focus:border-transparent focus:scale-[1.01] hover:border-gray-300 dark:hover:border-neutral-700 shadow-sm color-scheme-dark">
                </div>

                @if($esAdmin)
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-neutral-400 uppercase tracking-wider mb-2">Sucursal</label>
                    <div class="relative">
                        <select name="sucursal_id" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#D32030] focus:border-transparent focus:scale-[1.01] hover:border-gray-300 dark:hover:border-neutral-700 shadow-sm appearance-none cursor-pointer">
                            <option value="">Todas las sucursales</option>
                            @foreach($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}" {{ request('sucursal_id') == $sucursal->id ? 'selected' : '' }}>
                                    {{ $sucursal->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                    </div>
                </div>
                @endif

                <div class="flex gap-3 lg:col-span-4 justify-end mt-2">
                    <a href="{{ route('ventas.historial') }}" class="px-5 py-2.5 bg-white dark:bg-[#151515] border border-gray-200 dark:border-neutral-800 text-gray-600 dark:text-gray-300 font-semibold text-sm rounded-xl transition-all duration-200 hover:bg-gray-50 dark:hover:bg-neutral-800 hover:-translate-y-0.5 shadow-sm">
                        Limpiar
                    </a>
                    <button type="submit" class="btn-shine icon-breathe-hover group inline-flex items-center gap-2 px-6 py-2.5 bg-[#D32030] text-white font-semibold text-sm rounded-xl shadow-lg shadow-red-500/20 transition-all duration-200 hover:bg-[#B91C2C] hover:shadow-xl hover:shadow-red-500/30 hover:-translate-y-0.5 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                        Buscar Ventas
                    </button>
                </div>
            </form>
        </div>

        {{-- ── Tabla de resultados ── --}}
        <div class="bg-white dark:bg-[#151515] rounded-2xl shadow-sm border border-gray-200 dark:border-neutral-800 overflow-hidden transform transition-all duration-700 ease-out delay-500" 
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-[#0A0A0A] border-b border-gray-100 dark:border-neutral-800 transition-colors">
                            <th class="p-4 text-[11px] font-bold text-gray-500 dark:text-neutral-500 uppercase tracking-wider">Folio / Fecha</th>
                            <th class="p-4 text-[11px] font-bold text-gray-500 dark:text-neutral-500 uppercase tracking-wider">Cliente</th>
                            <th class="p-4 text-[11px] font-bold text-gray-500 dark:text-neutral-500 uppercase tracking-wider">Sucursal / Cajero</th>
                            <th class="p-4 text-[11px] font-bold text-gray-500 dark:text-neutral-500 uppercase tracking-wider">Artículos</th>
                            <th class="p-4 text-right text-[11px] font-bold text-gray-500 dark:text-neutral-500 uppercase tracking-wider">Total</th>
                            <th class="p-4 text-center text-[11px] font-bold text-gray-500 dark:text-neutral-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                        @forelse($ventas as $venta)
                        @php
                            $delayFila = $loop->index * 0.05; // Cascadas dinámicas
                        @endphp
                        <tr class="group hover:bg-gray-50/70 dark:hover:bg-neutral-800/40 transition-colors duration-200 relative row-anim" style="animation-delay: {{ $delayFila + 0.6 }}s">
                            
                            {{-- Borde lateral rojo en hover --}}
                            <span class="absolute left-0 top-0 h-full w-0.5 bg-[#D32030] scale-y-0 group-hover:scale-y-100 origin-center transition-transform duration-200"></span>

                            <td class="p-4">
                                <div class="font-bold text-gray-900 dark:text-gray-100 transition-colors group-hover:translate-x-0.5">{{ $venta->folio }}</div>
                                <div class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5 transition-colors">{{ \Carbon\Carbon::parse($venta->fecha)->format('d M, Y - H:i') }}</div>
                            </td>
                            <td class="p-4">
                                <div class="text-sm text-gray-800 dark:text-gray-200 font-medium transition-colors">{{ $venta->nombre_cliente_temporal ?: 'Público General' }}</div>
                                @if($venta->requiere_factura)
                                    <span class="inline-flex items-center gap-1.5 mt-1.5 text-[10px] bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-900/50 font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                                        <span class="pulse-dot relative w-1.5 h-1.5 rounded-full bg-blue-600 dark:bg-blue-500"></span>
                                        Req. Factura
                                    </span>
                                @endif
                            </td>
                            <td class="p-4">
                                <div class="text-sm font-medium text-gray-800 dark:text-gray-200 transition-colors">Sucursal {{ $venta->sucursal_id }}</div>
                                <div class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5 transition-colors">Cajero ID: {{ $venta->user_id }}</div>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300 transition-colors">{{ $venta->detalles->sum('cantidad') }}</span>
                                    <span class="text-xs text-gray-400 dark:text-neutral-500">pzas</span>
                                </div>
                            </td>
                            <td class="p-4 text-right">
                                <div class="text-lg font-bold text-emerald-600 dark:text-emerald-500 transition-colors group-hover:scale-105 origin-right">${{ number_format($venta->total, 2) }}</div>
                            </td>
                            <td class="p-4 text-center">
                                <button onclick="window.open('{{ route('ventas.ticket', $venta->id) }}', 'Ticket', 'width=400,height=600')"
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-[#0A0A0A] text-gray-600 dark:text-neutral-400 transition-all duration-200 hover:bg-[#D32030] dark:hover:bg-red-600 hover:text-white hover:shadow-md hover:shadow-red-500/30 active:scale-90 group/print" title="Reimprimir Ticket">
                                    <svg class="w-4 h-4 transition-transform duration-300 group-hover/print:-rotate-12 group-hover/print:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-16 text-center">
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-red-50 dark:bg-red-900/10 mb-4 ring-4 ring-red-50 dark:ring-red-900/5 transition-colors">
                                    <svg class="w-7 h-7 text-[#D32030] dark:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <p class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide transition-colors">Sin resultados</p>
                                <p class="text-gray-500 dark:text-neutral-500 text-sm mt-1 transition-colors">No se encontraron ventas con los filtros actuales.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($ventas->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-neutral-800 bg-gray-50/50 dark:bg-[#151515] transition-colors [&_a]:transition-all [&_a]:duration-200 [&_a]:rounded-lg [&_a:hover]:scale-110 [&_a:hover]:shadow-sm">
                {{ $ventas->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    /* CSS adicional si el navegador usa dark mode para forzar el selector de fecha a verse oscuro */
    .color-scheme-dark { color-scheme: light; }
    html.dark .color-scheme-dark { color-scheme: dark; }
</style>
@endsection