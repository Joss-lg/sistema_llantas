@extends('layouts.app')

@section('header_title', 'Historial de Ventas')

@section('content')

<style>
    /* =========================================================
       OCULTAR BARRAS DE DESPLAZAMIENTO (Opcional)
       ========================================================= */
    .no-scrollbar::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
    .no-scrollbar {
        -ms-overflow-style: none !important;  /* IE y Edge */
        scrollbar-width: none !important;  /* Firefox */
    }

    /* =========================================================
       ANIMACIONES Y EFECTOS VISUALES 3D
       ========================================================= */
    @media (prefers-reduced-motion: no-preference) {
        .dash-rise { 
            animation: dash-fade-up 0.65s cubic-bezier(0.16, 1, 0.3, 1) both; 
        }
        .dash-icon-pop { 
            animation: icon-pop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both; 
        }
        .dash-icon { 
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); 
        }
        .tilt-card:hover .dash-icon { 
            transform: scale(1.15) rotate(-6deg); 
        }
        .dash-row { 
            transition: background-color 0.2s ease, transform 0.2s ease; 
        }
        .dash-row:hover { 
            transform: translateX(4px); 
        }
        .dash-dot { 
            animation: dash-pulse-dot 2s ease-in-out infinite; 
        }
        .dash-empty-icon { 
            animation: empty-float 3.5s ease-in-out infinite; 
        }

        /* Tarjeta 3D interactiva con Spotlight */
        .tilt-card {
            transform-style: preserve-3d;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.35s ease, border-color 0.35s ease;
            will-change: transform, box-shadow;
        }
        .tilt-card:hover { 
            box-shadow: 0 20px 40px -10px rgba(129, 140, 248, 0.22); 
        }
        .tilt-spotlight {
            position: absolute; 
            inset: 0; 
            border-radius: inherit; 
            pointer-events: none;
            opacity: 0; 
            transition: opacity 0.4s ease;
            background: radial-gradient(320px circle at var(--mx, 50%) var(--my, 50%), rgba(129, 140, 248, 0.15), transparent 65%);
        }
        .tilt-card:hover .tilt-spotlight { 
            opacity: 1; 
        }
        .tilt-border {
            position: absolute; 
            inset: 0; 
            border-radius: inherit; 
            pointer-events: none;
            opacity: 0; 
            transition: opacity 0.4s ease; 
            padding: 1px;
            background: radial-gradient(220px circle at var(--mx, 50%) var(--my, 50%), rgba(129, 140, 248, 0.7), transparent 70%);
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor; 
            mask-composite: exclude;
        }
        .tilt-card:hover .tilt-border { 
            opacity: 1; 
        }
    }
    
    @keyframes dash-fade-up {
        from { opacity: 0; transform: translateY(16px); filter: blur(3px); }
        to   { opacity: 1; transform: translateY(0);    filter: blur(0); }
    }
    @keyframes icon-pop {
        0%   { opacity: 0; transform: scale(.3) rotate(-15deg); }
        60%  { opacity: 1; transform: scale(1.12) rotate(4deg); }
        100% { opacity: 1; transform: scale(1) rotate(0); }
    }
    @keyframes dash-pulse-dot { 
        0%, 100% { opacity: 1; transform: scale(1); } 
        50% { opacity: .4; transform: scale(.7); } 
    }
    @keyframes empty-float { 
        0%, 100% { transform: translateY(0); } 
        50% { transform: translateY(-6px); } 
    }

    .dash-counter { 
        font-variant-numeric: tabular-nums; 
    }
    .dash-dot-grid {
        background-image: radial-gradient(currentColor 1px, transparent 1px);
        background-size: 20px 20px;
    }

    /* =========================================================
       BOTONES Y EFECTO SHEEN (BARRIDO DE LUZ)
       ========================================================= */
    .btn-glow-base {
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        will-change: transform, box-shadow;
    }
    .btn-glow-base:hover {
        transform: translateY(-2px) scale(1.01);
    }
    .btn-glow-base:active {
        transform: translateY(0) scale(0.98);
    }
    .btn-sheen {
        position: relative;
        overflow: hidden;
    }
    .btn-sheen::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -60%;
        width: 50%;
        height: 200%;
        background: linear-gradient(60deg, transparent, rgba(255, 255, 255, 0.25), transparent);
        transform: rotate(30deg);
        transition: opacity 0.3s;
        opacity: 0;
        pointer-events: none;
    }
    .btn-sheen:hover::after {
        opacity: 1;
        animation: btn-sheen-slide 0.85s ease-in-out forwards;
    }
    @keyframes btn-sheen-slide {
        0%   { left: -60%; }
        100% { left: 140%; }
    }

    .color-scheme-dark { color-scheme: light; }
    html.dark .color-scheme-dark { color-scheme: dark; }
</style>

<div class="relative min-h-screen pb-12">

    {{-- Fondo de Red de Puntos en Modo Oscuro --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden hidden dark:block" aria-hidden="true">
        <div class="absolute inset-0 dash-dot-grid text-white/[0.025]"></div>
    </div>

    <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6">

        {{-- Encabezado --}}
        <div class="dash-rise flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-[#818CF8] dark:bg-[#818CF8]"></span>
                    <p class="text-[11px] font-bold tracking-[2.5px] text-[#818CF8] dark:text-[#818CF8] uppercase">
                        Llantas Económicas · Chalco
                    </p>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mt-1">
                    Historial de Ventas
                </h1>
            </div>

            {{-- Indicador de estado rápido --}}
            <div class="flex items-center gap-2 self-start sm:self-auto bg-white dark:bg-[#121212] px-3.5 py-1.5 rounded-full border border-gray-200 dark:border-neutral-800 shadow-sm text-xs font-semibold text-gray-600 dark:text-neutral-300">
                <svg class="w-4 h-4 text-[#818CF8] dark:text-[#818CF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Consulta en tiempo real</span>
            </div>
        </div>

        {{-- CÁLCULOS DE MÉTRICAS --}}
        @php
            $sumaPagina = $ventas->sum('total');
            $piezasPagina = $ventas->sum(fn($v) => $v->detalles ? $v->detalles->sum('cantidad') : 0);
        @endphp

        {{-- TARJETAS DE ESTADÍSTICAS (KPIs) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            
            {{-- Stat 1: Ventas Encontradas --}}
            <div class="js-tilt tilt-card btn-glow-base dash-rise relative overflow-hidden bg-white dark:bg-[#0A1F3F] rounded-2xl p-5 border border-gray-200 dark:border-[#818CF8]/40 shadow-sm" style="animation-delay:.05s">
                <div class="tilt-spotlight"></div>
                <div class="tilt-border"></div>
                <div class="relative flex items-center justify-between mb-3">
                    <div class="dash-icon dash-icon-pop w-10 h-10 rounded-xl bg-[#818CF8]/10 dark:bg-[#818CF8]/30 text-[#818CF8] dark:text-[#818CF8] flex items-center justify-center shadow-inner" style="animation-delay:.15s">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-extrabold text-[#818CF8] dark:text-[#818CF8] uppercase tracking-widest bg-[#818CF8]/10 dark:bg-[#818CF8]/40 px-2.5 py-0.5 rounded-full border border-[#818CF8]/30 dark:border-[#818CF8]/20">
                        Filtro
                    </span>
                </div>
                <p class="relative text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-[#818CF8]/80">Ventas Encontradas</p>
                <p class="relative text-3xl font-extrabold mt-1 text-gray-900 dark:text-white tracking-tight">
                    <span class="dash-counter" data-counter data-value="{{ $ventas->total() }}">0</span>
                </p>
            </div>

            {{-- Stat 2: En esta página --}}
            <div class="js-tilt tilt-card btn-glow-base dash-rise relative overflow-hidden bg-white dark:bg-[#151515] rounded-2xl p-5 border border-gray-200 dark:border-neutral-800 shadow-sm" style="animation-delay:.12s">
                <div class="tilt-spotlight"></div>
                <div class="tilt-border"></div>
                <div class="relative flex items-center justify-between mb-3">
                    <div class="dash-icon dash-icon-pop w-10 h-10 rounded-xl bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 flex items-center justify-center" style="animation-delay:.22s">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <p class="relative text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-neutral-400">En esta página</p>
                <p class="relative text-3xl font-extrabold mt-1 text-gray-900 dark:text-white tracking-tight">
                    <span class="dash-counter" data-counter data-value="{{ $ventas->count() }}">0</span>
                    <span class="text-xs font-normal text-gray-400 dark:text-neutral-500 ml-1">reg.</span>
                </p>
            </div>

            {{-- Stat 3: Piezas --}}
            <div class="js-tilt tilt-card btn-glow-base dash-rise relative overflow-hidden bg-white dark:bg-[#151515] rounded-2xl p-5 border border-gray-200 dark:border-neutral-800 shadow-sm" style="animation-delay:.19s">
                <div class="tilt-spotlight"></div>
                <div class="tilt-border"></div>
                <div class="relative flex items-center justify-between mb-3">
                    <div class="dash-icon dash-icon-pop w-10 h-10 rounded-xl bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 flex items-center justify-center" style="animation-delay:.29s">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
                <p class="relative text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-neutral-400">Piezas (pág.)</p>
                <p class="relative text-3xl font-extrabold mt-1 text-gray-900 dark:text-white tracking-tight">
                    <span class="dash-counter" data-counter data-value="{{ $piezasPagina }}">0</span>
                    <span class="text-xs font-normal text-gray-400 dark:text-neutral-500 ml-1">pzas</span>
                </p>
            </div>

            {{-- Stat 4: Total ($) --}}
            <div class="js-tilt tilt-card btn-glow-base dash-rise relative overflow-hidden bg-emerald-700 dark:bg-emerald-900/80 rounded-2xl p-5 border border-emerald-600 dark:border-emerald-700 text-white shadow-md" style="animation-delay:.26s">
                <div class="tilt-spotlight"></div>
                <div class="tilt-border"></div>
                <div class="relative flex items-center justify-between mb-3">
                    <div class="dash-icon dash-icon-pop w-10 h-10 rounded-xl bg-white/20 text-white flex items-center justify-center backdrop-blur-sm" style="animation-delay:.36s">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="relative text-[11px] font-bold uppercase tracking-wider text-emerald-100 dark:text-emerald-200">Total (pág.)</p>
                <p class="relative text-3xl font-extrabold mt-1 text-white tracking-tight">
                    <span class="dash-counter" data-counter data-value="{{ $sumaPagina }}" data-prefix="$" data-decimals="2">$0.00</span>
                </p>
            </div>
        </div>

        {{-- FORMULARIO DE FILTROS --}}
        <div class="dash-rise bg-white dark:bg-[#151515] rounded-2xl shadow-sm border border-gray-200 dark:border-neutral-800 p-5 sm:p-6" style="animation-delay:.32s">
            <div class="flex items-center gap-2 mb-5 group/filterhead">
                <div class="p-1.5 rounded-lg bg-[#818CF8]/10 dark:bg-[#818CF8]/20 text-[#818CF8] dark:text-[#818CF8]">
                    <svg class="w-4 h-4 transition-transform duration-500 group-hover/filterhead:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </div>
                <p class="text-xs font-bold uppercase tracking-widest text-gray-600 dark:text-neutral-300">
                    Filtrar resultados
                </p>
            </div>

            <form method="GET" action="{{ route('ventas.historial') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 items-end">
                
                {{-- Folio --}}
                <div>
                    <label for="filter_folio" class="block text-xs font-bold text-gray-600 dark:text-neutral-400 uppercase tracking-wider mb-2">
                        Folio del Ticket
                    </label>
                    <input type="text" id="filter_folio" name="folio" value="{{ request('folio') }}" placeholder="Ej. VNT-2026..."
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-neutral-600 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#818CF8] focus:border-transparent hover:border-gray-300 dark:hover:border-neutral-700 shadow-sm">
                </div>

                {{-- Fecha Inicio --}}
                <div>
                    <label for="filter_fecha_inicio" class="block text-xs font-bold text-gray-600 dark:text-neutral-400 uppercase tracking-wider mb-2">
                        Fecha Inicio
                    </label>
                    <input type="date" id="filter_fecha_inicio" name="fecha_inicio" value="{{ request('fecha_inicio') }}"
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#818CF8] focus:border-transparent hover:border-gray-300 dark:hover:border-neutral-700 shadow-sm color-scheme-dark">
                </div>

                {{-- Fecha Fin --}}
                <div>
                    <label for="filter_fecha_fin" class="block text-xs font-bold text-gray-600 dark:text-neutral-400 uppercase tracking-wider mb-2">
                        Fecha Fin
                    </label>
                    <input type="date" id="filter_fecha_fin" name="fecha_fin" value="{{ request('fecha_fin') }}"
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#818CF8] focus:border-transparent hover:border-gray-300 dark:hover:border-neutral-700 shadow-sm color-scheme-dark">
                </div>

                {{-- Sucursal (Sólo Administradores) --}}
                @if(isset($esAdmin) && $esAdmin)
                <div>
                    <label for="filter_sucursal" class="block text-xs font-bold text-gray-600 dark:text-neutral-400 uppercase tracking-wider mb-2">
                        Sucursal
                    </label>
                    <div class="relative">
                        <select id="filter_sucursal" name="sucursal_id" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#818CF8] focus:border-transparent hover:border-gray-300 dark:hover:border-neutral-700 shadow-sm appearance-none cursor-pointer pr-10">
                            <option value="">Todas las sucursales</option>
                            @foreach($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}" {{ request('sucursal_id') == $sucursal->id ? 'selected' : '' }}>
                                    {{ $sucursal->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
                        </svg>
                    </div>
                </div>
                @endif

                {{-- Acciones del Filtro --}}
                <div class="flex gap-3 lg:col-span-4 justify-end mt-2">
                    <a href="{{ route('ventas.historial') }}" class="btn-glow-base px-5 py-2.5 bg-white dark:bg-[#151515] border border-gray-200 dark:border-neutral-800 text-gray-600 dark:text-gray-300 font-semibold text-sm rounded-xl shadow-sm hover:bg-gray-50 dark:hover:bg-neutral-800">
                        Limpiar
                    </a>
                    
                    <button type="submit" class="btn-glow-base btn-sheen group inline-flex items-center gap-2 px-6 py-2.5 bg-[#818CF8] hover:bg-[#6366F1] dark:bg-[#818CF8] dark:hover:bg-[#6366F1] text-white font-semibold text-sm rounded-xl shadow-md transition duration-300">
                        <svg class="w-4 h-4 text-white transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                        </svg>
                        <span>Buscar Ventas</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- TABLA DE RESULTADOS --}}
        <div class="dash-rise bg-white dark:bg-[#151515] rounded-2xl shadow-sm border border-gray-200 dark:border-neutral-800 overflow-hidden" style="animation-delay:.38s">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/70 dark:bg-[#0A0A0A] border-b border-gray-100 dark:border-neutral-800">
                            <th class="p-4 text-[11px] font-bold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Folio / Fecha</th>
                            <th class="p-4 text-[11px] font-bold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Cliente</th>
                            <th class="p-4 text-[11px] font-bold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Sucursal / Cajero</th>
                            <th class="p-4 text-[11px] font-bold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Artículos</th>
                            <th class="p-4 text-right text-[11px] font-bold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Total</th>
                            <th class="p-4 text-center text-[11px] font-bold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-neutral-800/60">
                        @forelse($ventas as $venta)
                        <tr class="dash-row group hover:bg-gray-50/80 dark:hover:bg-white/[0.02] relative">
                            
                            {{-- Folio / Fecha --}}
                            <td class="p-4">
                                <div class="font-bold text-gray-900 dark:text-gray-100 tracking-tight">
                                    {{ $venta->folio }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>{{ \Carbon\Carbon::parse($venta->fecha)->format('d M, Y - H:i') }}</span>
                                </div>
                            </td>

                            {{-- Cliente --}}
                            <td class="p-4">
                                <div class="text-sm text-gray-800 dark:text-gray-200 font-medium">
                                    {{ $venta->nombre_cliente_temporal ?: 'Público General' }}
                                </div>
                                @if($venta->requiere_factura)
                                    <span class="inline-flex items-center gap-1.5 mt-1 text-[10px] bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50 font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                                        <span class="dash-dot relative w-1.5 h-1.5 rounded-full bg-blue-600 dark:bg-blue-400"></span>
                                        Req. Factura
                                    </span>
                                @endif
                            </td>

                            {{-- Sucursal / Cajero --}}
                            <td class="p-4">
                                <div class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ $venta->sucursal->nombre ?? 'Sucursal ' . $venta->sucursal_id }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">
                                    {{ $venta->user->name ?? 'Cajero ID: ' . $venta->user_id }}
                                </div>
                            </td>

                            {{-- Artículos --}}
                            <td class="p-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                        {{ $venta->detalles ? $venta->detalles->sum('cantidad') : 0 }}
                                    </span>
                                    <span class="text-xs text-gray-400 dark:text-neutral-500">pzas</span>
                                </div>
                            </td>

                            {{-- Total --}}
                            <td class="p-4 text-right">
                                <div class="text-base sm:text-lg font-extrabold text-emerald-600 dark:text-emerald-400">
                                    ${{ number_format($venta->total, 2) }}
                                </div>
                            </td>

                            {{-- Acciones --}}
                            <td class="p-4 text-center">
                                <button onclick="window.open('{{ route('ventas.ticket', $venta->id) }}', 'Ticket', 'width=400,height=600')"
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-[#0A0A0A] text-gray-600 dark:text-neutral-400 transition-all duration-200 hover:bg-[#D32030] dark:hover:bg-red-600 hover:text-white hover:shadow-md hover:shadow-red-500/20 active:scale-95 group/print"
                                        title="Reimprimir Ticket">
                                    <svg class="w-4 h-4 transition-transform duration-300 group-hover/print:-rotate-12 group-hover/print:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-16 text-center">
                                <div class="dash-empty-icon inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-[#818CF8]/10 dark:bg-[#818CF8]/20 text-[#818CF8] dark:text-[#818CF8] mb-4 shadow-inner">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <p class="text-base font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide">
                                    Sin resultados
                                </p>
                                <p class="text-gray-500 dark:text-neutral-400 text-sm mt-1 max-w-sm mx-auto">
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
            <div class="p-4 border-t border-gray-100 dark:border-neutral-800 bg-gray-50/50 dark:bg-[#151515]">
                {{ $ventas->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Scripts de Animación Vanilla JS --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // -------------------------------------------------------------
        // 1. Animación de Conteo Ascendente (Metrics Counter)
        // -------------------------------------------------------------
        var counters = document.querySelectorAll('[data-counter]');
        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        counters.forEach(function (el) {
            var target = parseFloat(el.getAttribute('data-value')) || 0;
            var decimals = parseInt(el.getAttribute('data-decimals') || '0', 10);
            var prefix = el.getAttribute('data-prefix') || '';

            if (reduceMotion) {
                el.textContent = prefix + formatNumber(target, decimals);
                return;
            }

            var duration = 1200;
            var startTime = null;

            function formatNumber(n, dec) {
                return dec > 0
                    ? n.toLocaleString('es-MX', { minimumFractionDigits: dec, maximumFractionDigits: dec })
                    : Math.round(n).toLocaleString('es-MX');
            }

            function step(timestamp) {
                if (!startTime) startTime = timestamp;
                var progress = Math.min((timestamp - startTime) / duration, 1);
                // Easing cubic suave
                var eased = 1 - Math.pow(1 - progress, 3);
                var current = eased * target;
                el.textContent = prefix + formatNumber(current, decimals);

                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            }
            requestAnimationFrame(step);
        });

        // -------------------------------------------------------------
        // 2. Interactive Spotlight & Perspective 3D
        // -------------------------------------------------------------
        var isFinePointer = window.matchMedia('(pointer: fine)').matches;

        if (!reduceMotion && isFinePointer) {
            document.querySelectorAll('.js-tilt').forEach(function (card) {
                var ticking = false;

                card.addEventListener('mousemove', function (e) {
                    if (!ticking) {
                        requestAnimationFrame(function () {
                            var rect = card.getBoundingClientRect();
                            var x = e.clientX - rect.left;
                            var y = e.clientY - rect.top;

                            card.style.setProperty('--mx', x + 'px');
                            card.style.setProperty('--my', y + 'px');

                            var midX = rect.width / 2;
                            var midY = rect.height / 2;
                            var rotateY = ((x - midX) / midX) * 4;
                            var rotateX = -((y - midY) / midY) * 4;

                            card.style.transform = 'perspective(700px) rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg) translateY(-4px)';
                            ticking = false;
                        });
                        ticking = true;
                    }
                });

                card.addEventListener('mouseleave', function () {
                    card.style.transform = 'perspective(700px) rotateX(0) rotateY(0) translateY(0)';
                });
            });
        }
    });
</script>
@endsection