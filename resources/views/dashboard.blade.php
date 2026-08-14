@extends('layouts.app')

@section('header_title', 'Resumen General')

@section('content')

<style>
    /* =========================================================
       TRANSICIÓN DE ENTRADA DE LA PÁGINA (Viene del Login)
       ========================================================= */
    body {
        opacity: 0;
        animation: page-fade-in 0.6s ease-out forwards;
    }
    
    @keyframes page-fade-in {
        from { opacity: 0; }
        to   { opacity: 1; }
    }

    /* =========================================================
       OCULTAR BARRAS DE DESPLAZAMIENTO
       ========================================================= */
    *::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
    * {
        -ms-overflow-style: none !important;  /* IE y Edge */
        scrollbar-width: none !important;  /* Firefox */
    }

    /* =========================================================
       ANIMACIÓN EN CASCADA (TARJETAS DESLIZANDO HACIA ARRIBA)
       ========================================================= */
    @media (prefers-reduced-motion: no-preference) {
        /* Clase principal para la entrada fluida desde abajo */
        .dash-rise { 
            opacity: 0;
            animation: dash-slide-up 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; 
        }

        @keyframes dash-slide-up {
            from { opacity: 0; transform: translateY(40px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Otras animaciones que ya tenías */
        .dash-icon-pop     { animation: icon-pop .6s cubic-bezier(.34,1.56,.64,1) both; }
        .dash-icon         { transition: transform .35s cubic-bezier(.34,1.56,.64,1); }
        .tilt-card:hover .dash-icon { transform: scale(1.12) rotate(-6deg); }
        .dash-row          { transition: background-color .25s ease, transform .25s ease; }
        .dash-row:hover    { transform: translateX(3px); }
        .dash-bar-fill     { animation: bar-grow 1.2s cubic-bezier(.16,1,.3,1) both; animation-delay: .5s; }
        .dash-dot          { animation: dash-pulse-dot 2s ease-in-out infinite; }
        .dash-empty-icon   { animation: empty-float 3.5s ease-in-out infinite; }
        .dash-link-arrow   { transition: transform .2s ease; display: inline-block; }
        .group:hover .dash-link-arrow { transform: translateX(3px); }

        /* Tarjeta con efecto Tilt */
        .tilt-card {
            transform-style: preserve-3d;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.35s ease, border-color 0.35s ease;
            will-change: transform, box-shadow;
        }
        .tilt-card:hover { 
            box-shadow: 0 20px 40px -10px rgba(129, 140, 248, 0.35); 
        }
        .tilt-spotlight {
            position: absolute; inset: 0; border-radius: inherit; pointer-events: none;
            opacity: 0; transition: opacity .4s ease;
            background: radial-gradient(320px circle at var(--mx,50%) var(--my,50%), rgba(129, 140, 248, .25), transparent 65%);
        }
        .tilt-card:hover .tilt-spotlight { opacity: 1; }
        .tilt-border {
            position: absolute; inset: 0; border-radius: inherit; pointer-events: none;
            opacity: 0; transition: opacity .4s ease; padding: 1px;
            background: radial-gradient(220px circle at var(--mx,50%) var(--my,50%), rgba(129, 140, 248, .9), transparent 70%);
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor; mask-composite: exclude;
        }
        .tilt-card:hover .tilt-border { opacity: 1; }
    }
    
    @keyframes icon-pop {
        0%   { opacity: 0; transform: scale(.3) rotate(-15deg); }
        60%  { opacity: 1; transform: scale(1.12) rotate(4deg); }
        100% { opacity: 1; transform: scale(1) rotate(0); }
    }
    @keyframes bar-grow  { from { width: 0%; } }
    @keyframes dash-pulse-dot { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: .4; transform: scale(.7); } }
    @keyframes empty-float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }

    .dash-counter { font-variant-numeric: tabular-nums; }
    .dash-dot-grid {
        background-image: radial-gradient(currentColor 1px, transparent 1px);
        background-size: 20px 20px;
    }

    /* =========================================================
       ESTILOS "GLOW" Y "SHEEN"
       ========================================================= */
    .btn-glow-base {
        transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        will-change: transform, box-shadow;
    }
    .btn-glow-base:hover { transform: translateY(-3px) scale(1.015); }
    .btn-glow-base:active { transform: translateY(0) scale(0.98); }
    
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
</style>

<div class="relative">

    {{-- ============ FONDO: textura de grano ============ --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden hidden dark:block">
        <div class="absolute inset-0 dash-dot-grid text-white/[0.025]"></div>
    </div>

    <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

        {{-- Encabezado y Botón Nuevo (Aparece primero: 100ms) --}}
        <div class="dash-rise flex flex-col sm:flex-row sm:items-center justify-between gap-4" style="animation-delay: 100ms;">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Buen día, {{ Auth::user()->name ?? 'Administrador' }}</h2>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">{{ now()->translatedFormat('l, j \d\e F \d\e Y') }}</p>
            </div>
            
            {{-- BOTÓN CON COLOR #818CF8 --}}
            <a href="{{ route('ventas.index') }}" 
               class="btn-glow-base btn-sheen group relative inline-flex items-center justify-center gap-2.5 px-6 py-3 bg-[#818CF8] hover:bg-[#6366F1] text-white rounded-2xl text-sm font-bold border border-[#818CF8] hover:border-[#6366F1] shadow-[0_10px_25px_-5px_rgba(129,140,248,0.5)] transition duration-300">
                <span class="absolute -inset-0.5 bg-white/20 rounded-2xl blur-md opacity-40 group-hover:opacity-75 transition duration-300 -z-10"></span>
                <svg class="w-4 h-4 text-white transition-transform duration-300 group-hover:rotate-90 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Nueva Venta</span>
            </a>
        </div>

        {{-- Métricas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            {{-- TARJETA PRINCIPAL (Aparece segundo: 200ms) --}}
            <div class="js-tilt tilt-card btn-glow-base btn-sheen group dash-rise relative overflow-hidden bg-[#818CF8] dark:bg-[#818CF8] dark:border dark:border-indigo-900/20 rounded-2xl p-6 text-white" style="animation-delay: 200ms;">
                <div class="tilt-spotlight"></div>
                <div class="tilt-border"></div>
                <div class="relative flex items-center justify-between mb-5">
                    <div class="dash-icon dash-icon-pop w-11 h-11 rounded-xl bg-white/20 text-white flex items-center justify-center shadow-lg shadow-indigo-900/20" style="animation-delay: 400ms;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-[10px] font-bold text-indigo-100 uppercase tracking-wide flex items-center gap-1.5">
                        <span class="dash-dot h-1.5 w-1.5 rounded-full bg-white"></span> Hoy
                    </span>
                </div>
                <p class="relative text-xs text-indigo-100 font-medium">Ventas de Hoy</p>
                <p class="relative dash-counter text-3xl font-bold mt-1 text-white" data-counter data-value="{{ $ventasHoy ?? 0 }}" data-prefix="$" data-decimals="2">$0.00</p>
            </div>

            {{-- TARJETA 2 (Aparece tercero: 300ms) --}}
            <div class="js-tilt tilt-card btn-glow-base dash-rise relative overflow-hidden bg-white dark:bg-white/[0.03] rounded-2xl p-6 border border-gray-200 dark:border-white/10" style="animation-delay: 300ms;">
                <div class="tilt-spotlight"></div>
                <div class="tilt-border"></div>
                <div class="relative flex items-center justify-between mb-5">
                    <div class="dash-icon dash-icon-pop w-11 h-11 rounded-xl bg-[#1A1A1A] dark:bg-white/10 text-white flex items-center justify-center" style="animation-delay: 500ms;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    </div>
                </div>
                <p class="relative text-xs text-gray-400 dark:text-gray-500 font-medium">Llantas Vendidas</p>
                <p class="relative text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <span class="dash-counter" data-counter data-value="{{ $llantasVendidas ?? 0 }}">0</span>
                    <span class="text-base font-medium text-gray-400 dark:text-gray-500">pzas</span>
                </p>
            </div>

            {{-- TARJETA 3 (Aparece cuarto: 400ms) --}}
            <div class="js-tilt tilt-card btn-glow-base dash-rise relative overflow-hidden bg-white dark:bg-white/[0.03] rounded-2xl p-6 border border-gray-200 dark:border-white/10" style="animation-delay: 400ms;">
                <div class="tilt-spotlight"></div>
                <div class="tilt-border"></div>
                <div class="relative flex items-center justify-between mb-5">
                    <div class="dash-icon dash-icon-pop w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-[#818CF8] flex items-center justify-center" style="animation-delay: 600ms;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    </div>
                    @if(($bajoStock ?? 0) > 0)
                        <span class="text-[10px] font-bold text-[#818CF8] bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-500/30 px-2 py-0.5 rounded-full">Atención</span>
                    @endif
                </div>
                <p class="relative text-xs text-gray-400 dark:text-gray-500 font-medium">Bajo Stock</p>
                <p class="relative text-3xl font-bold mt-1 {{ ($bajoStock ?? 0) > 0 ? 'text-[#818CF8]' : 'text-gray-900 dark:text-white' }}">
                    <span class="dash-counter" data-counter data-value="{{ $bajoStock ?? 0 }}">0</span>
                    <span class="text-base font-medium text-gray-400 dark:text-gray-500">productos</span>
                </p>
            </div>

            {{-- TARJETA 4 (Aparece quinto: 500ms) --}}
            <div class="js-tilt tilt-card btn-glow-base dash-rise relative overflow-hidden bg-white dark:bg-white/[0.03] rounded-2xl p-6 border border-gray-200 dark:border-white/10" style="animation-delay: 500ms;">
                <div class="tilt-spotlight"></div>
                <div class="tilt-border"></div>
                <div class="relative flex items-center justify-between mb-5">
                    <div class="dash-icon dash-icon-pop w-11 h-11 rounded-xl bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300 flex items-center justify-center" style="animation-delay: 700ms;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m9-1.13a4 4 0 100-5.4M9 14a4 4 0 100-8 4 4 0 000 8z"/></svg>
                    </div>
                </div>
                <p class="relative text-xs text-gray-400 dark:text-gray-500 font-medium">Clientes Nuevos</p>
                <p class="relative text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <span class="dash-counter" data-counter data-value="{{ $clientesNuevos ?? 0 }}">0</span>
                    <span class="text-base font-medium text-gray-400 dark:text-gray-500">hoy</span>
                </p>
            </div>
        </div>

        {{-- Últimas Ventas (Aparece al final: 600ms) --}}
        <div class="dash-rise bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden" style="animation-delay: 600ms;">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white">Últimas Ventas</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Transacciones recientes</p>
                </div>
                <a href="{{ route('ventas.index') }}" class="group text-xs font-semibold text-[#818CF8] hover:text-[#6366F1] transition inline-flex items-center gap-1">
                    Ver todas <span class="dash-link-arrow">→</span>
                </a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-white/10">
                @forelse($ultimasVentas ?? [] as $venta)
                    <div class="dash-row flex items-center justify-between px-6 py-4 hover:bg-gray-50/50 dark:hover:bg-white/[0.03]">
                        <div class="flex items-center gap-4">
                            <div class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-gray-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800 dark:text-white">#{{ $venta->folio }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $venta->nombre_cliente_temporal ?? 'Mostrador' }} · {{ $venta->created_at->format('H:i') ?? '' }}</p>
                            </div>
                        </div>
                        <p class="text-sm font-bold text-gray-800 dark:text-white">${{ number_format($venta->total, 2) }}</p>
                    </div>
                @empty
                    <div class="text-center py-14">
                        <div class="dash-empty-icon w-14 h-14 rounded-2xl bg-gray-100 dark:bg-white/5 text-gray-300 dark:text-gray-600 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <p class="text-sm text-gray-400 dark:text-gray-500">Sin ventas registradas hoy</p>
                        <a href="{{ route('ventas.index') }}" class="text-xs text-[#818CF8] font-semibold mt-1 inline-block hover:underline">Ir al punto de venta →</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Animación de conteo ascendente
        var counters = document.querySelectorAll('[data-counter]');
        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        counters.forEach(function (el) {
            var target = parseFloat(el.getAttribute('data-value')) || 0;
            var decimals = parseInt(el.getAttribute('data-decimals') || '0', 10);
            var prefix = el.getAttribute('data-prefix') || '';

            if (reduceMotion) {
                el.textContent = prefix + target.toFixed(decimals);
                return;
            }

            var duration = 1200;
            var startTime = null;

            function formatNumber(n) {
                return decimals > 0
                    ? n.toLocaleString('es-MX', { minimumFractionDigits: decimals, maximumFractionDigits: decimals })
                    : Math.round(n).toLocaleString('es-MX');
            }

            function step(timestamp) {
                if (!startTime) startTime = timestamp;
                var progress = Math.min((timestamp - startTime) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                var current = eased * target;
                el.textContent = prefix + formatNumber(current);
                if (progress < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        });

        // Spotlight que sigue al mouse
        var reduceMotion2 = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var isFinePointer = window.matchMedia('(pointer: fine)').matches;

        if (!reduceMotion2 && isFinePointer) {
            document.querySelectorAll('.js-tilt').forEach(function (card) {
                card.addEventListener('mousemove', function (e) {
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
                });

                card.addEventListener('mouseleave', function () {
                    card.style.transform = 'perspective(700px) rotateX(0) rotateY(0) translateY(0)';
                });
            });
        }
    });
</script>
@endsection