@extends('layouts.app')

@section('header_title', 'Resumen General')

@section('content')

<style>
    @media (prefers-reduced-motion: no-preference) {
        .dash-rise        { animation: dash-fade-up .7s cubic-bezier(.16,1,.3,1) both; }
        .dash-icon-pop     { animation: icon-pop .6s cubic-bezier(.34,1.56,.64,1) both; }
        .dash-glow         { animation: dash-glow-pulse 6s ease-in-out infinite; }
        .dash-mesh         { animation: mesh-drift 18s ease-in-out infinite; }
        .dash-mesh-2       { animation: mesh-drift 22s ease-in-out infinite reverse; animation-delay: -4s; }
        .dash-icon         { transition: transform .35s cubic-bezier(.34,1.56,.64,1); }
        .tilt-card:hover .dash-icon { transform: scale(1.12) rotate(-6deg); }
        .dash-btn          { transition: transform .25s cubic-bezier(.34,1.56,.64,1), box-shadow .25s ease; }
        .dash-btn:hover    { transform: translateY(-2px) scale(1.03); }
        .dash-btn:active   { transform: translateY(0) scale(.98); }
        .dash-row          { transition: background-color .25s ease, transform .25s ease; }
        .dash-row:hover    { transform: translateX(3px); }
        .dash-bar-fill     { animation: bar-grow 1.2s cubic-bezier(.16,1,.3,1) both; animation-delay: .5s; }
        .dash-dot          { animation: dash-pulse-dot 2s ease-in-out infinite; }
        .dash-empty-icon   { animation: empty-float 3.5s ease-in-out infinite; }
        .dash-link-arrow   { transition: transform .2s ease; display: inline-block; }
        .group:hover .dash-link-arrow { transform: translateX(3px); }

        /* Tarjeta con inclinación 3D + spotlight que sigue el mouse */
        .tilt-card {
            transform-style: preserve-3d;
            transition: transform .15s ease-out, box-shadow .35s ease, border-color .35s ease;
            will-change: transform;
        }
        .tilt-card:hover { box-shadow: 0 20px 40px -12px rgba(0,0,0,.35); }
        .tilt-spotlight {
            position: absolute; inset: 0; border-radius: inherit; pointer-events: none;
            opacity: 0; transition: opacity .4s ease;
            background: radial-gradient(320px circle at var(--mx,50%) var(--my,50%), rgba(211,32,48,.16), transparent 65%);
        }
        .tilt-card:hover .tilt-spotlight { opacity: 1; }
        .tilt-border {
            position: absolute; inset: 0; border-radius: inherit; pointer-events: none;
            opacity: 0; transition: opacity .4s ease; padding: 1px;
            background: radial-gradient(220px circle at var(--mx,50%) var(--my,50%), rgba(211,32,48,.9), transparent 70%);
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor; mask-composite: exclude;
        }
        .tilt-card:hover .tilt-border { opacity: 1; }
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
    @keyframes dash-glow-pulse { 0%, 100% { opacity: .12; } 50% { opacity: .32; } }
    @keyframes mesh-drift {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50%      { transform: translate(3%, -4%) scale(1.1); }
    }
    @keyframes bar-grow  { from { width: 0%; } }
    @keyframes dash-pulse-dot { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: .4; transform: scale(.7); } }
    @keyframes empty-float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }

    .dash-counter { font-variant-numeric: tabular-nums; }

    .dash-dot-grid {
        background-image: radial-gradient(currentColor 1px, transparent 1px);
        background-size: 20px 20px;
    }
</style>

<div class="relative">

    {{-- ============ FONDO: malla de gradientes + textura de grano (solo modo oscuro) ============ --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden hidden dark:block">
        <div class="dash-mesh absolute -top-32 -left-20 h-[420px] w-[420px] rounded-full bg-[#D32030] opacity-10 blur-[110px]"></div>
        <div class="dash-mesh-2 absolute top-1/3 -right-20 h-[380px] w-[380px] rounded-full bg-[#D32030] opacity-[0.07] blur-[110px]"></div>
        <div class="absolute inset-0 dash-dot-grid text-white/[0.025]"></div>
    </div>

    <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

        {{-- Encabezado --}}
        <div class="dash-rise flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Buen día, {{ Auth::user()->name ?? 'Administrador' }}</h2>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">{{ now()->translatedFormat('l, j \d\e F \d\e Y') }}</p>
            </div>
            <a href="{{ route('ventas.index') }}" class="dash-btn relative inline-flex items-center gap-2 px-5 py-2.5 bg-[#D32030] text-white rounded-xl text-sm font-semibold shadow-lg shadow-[#D32030]/20 hover:shadow-[#D32030]/50 overflow-hidden group">
                <span class="absolute inset-0 -translate-x-full bg-white/15 transition-transform duration-500 group-hover:translate-x-0"></span>
                <svg class="relative w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="relative">Nueva Venta</span>
            </a>
        </div>

        {{-- Métricas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

            <div class="js-tilt tilt-card dash-rise relative overflow-hidden bg-[#0F0F0F] dark:bg-white/[0.03] dark:border dark:border-white/10 rounded-2xl p-6 text-white" style="animation-delay:.05s">
                <div class="tilt-spotlight"></div>
                <div class="tilt-border"></div>
                <div class="dash-glow pointer-events-none absolute -top-10 -right-10 h-32 w-32 rounded-full bg-[#D32030] blur-3xl"></div>
                <div class="relative flex items-center justify-between mb-5">
                    <div class="dash-icon dash-icon-pop w-11 h-11 rounded-xl bg-[#D32030] flex items-center justify-center shadow-lg shadow-[#D32030]/30" style="animation-delay:.15s">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wide flex items-center gap-1.5">
                        <span class="dash-dot h-1.5 w-1.5 rounded-full bg-[#D32030]"></span> Hoy
                    </span>
                </div>
                <p class="relative text-xs text-gray-500 font-medium">Ventas de Hoy</p>
                <p class="relative dash-counter text-3xl font-bold mt-1" data-counter data-value="{{ $ventasHoy ?? 0 }}" data-prefix="$" data-decimals="2">$0.00</p>
            </div>

            <div class="js-tilt tilt-card dash-rise relative overflow-hidden bg-white dark:bg-white/[0.03] rounded-2xl p-6 border border-gray-200 dark:border-white/10" style="animation-delay:.12s">
                <div class="tilt-spotlight"></div>
                <div class="tilt-border"></div>
                <div class="relative flex items-center justify-between mb-5">
                    <div class="dash-icon dash-icon-pop w-11 h-11 rounded-xl bg-[#1A1A1A] dark:bg-white/10 text-white flex items-center justify-center" style="animation-delay:.22s">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    </div>
                </div>
                <p class="relative text-xs text-gray-400 dark:text-gray-500 font-medium">Llantas Vendidas</p>
                <p class="relative text-3xl font-bold text-gray-900 dark:text-white mt-1">
                    <span class="dash-counter" data-counter data-value="{{ $llantasVendidas ?? 0 }}">0</span>
                    <span class="text-base font-medium text-gray-400 dark:text-gray-500">pzas</span>
                </p>
            </div>

            <div class="js-tilt tilt-card dash-rise relative overflow-hidden bg-white dark:bg-white/[0.03] rounded-2xl p-6 border border-gray-200 dark:border-white/10" style="animation-delay:.19s">
                <div class="tilt-spotlight"></div>
                <div class="tilt-border"></div>
                <div class="relative flex items-center justify-between mb-5">
                    <div class="dash-icon dash-icon-pop w-11 h-11 rounded-xl bg-red-50 dark:bg-[#D32030]/10 text-[#D32030] flex items-center justify-center" style="animation-delay:.29s">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    </div>
                    @if(($bajoStock ?? 0) > 0)
                        <span class="text-[10px] font-bold text-[#D32030] bg-red-50 dark:bg-[#D32030]/10 border border-red-200 dark:border-[#D32030]/30 px-2 py-0.5 rounded-full">Atención</span>
                    @endif
                </div>
                <p class="relative text-xs text-gray-400 dark:text-gray-500 font-medium">Bajo Stock</p>
                <p class="relative text-3xl font-bold mt-1 {{ ($bajoStock ?? 0) > 0 ? 'text-[#D32030]' : 'text-gray-900 dark:text-white' }}">
                    <span class="dash-counter" data-counter data-value="{{ $bajoStock ?? 0 }}">0</span>
                    <span class="text-base font-medium text-gray-400 dark:text-gray-500">productos</span>
                </p>
            </div>

            <div class="js-tilt tilt-card dash-rise relative overflow-hidden bg-white dark:bg-white/[0.03] rounded-2xl p-6 border border-gray-200 dark:border-white/10" style="animation-delay:.26s">
                <div class="tilt-spotlight"></div>
                <div class="tilt-border"></div>
                <div class="relative flex items-center justify-between mb-5">
                    <div class="dash-icon dash-icon-pop w-11 h-11 rounded-xl bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300 flex items-center justify-center" style="animation-delay:.36s">
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

        {{-- Últimas Ventas --}}
        <div class="dash-rise bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden" style="animation-delay:.34s">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white">Últimas Ventas</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Transacciones recientes</p>
                </div>
                <a href="{{ route('ventas.index') }}" class="group text-xs font-semibold text-[#D32030] hover:text-[#B91C2C] transition inline-flex items-center gap-1">
                    Ver todas <span class="dash-link-arrow">→</span>
                </a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-white/10">
                @forelse($ultimasVentas ?? [] as $venta)
                    <div class="dash-row flex items-center justify-between px-6 py-4 hover:bg-gray-50/50 dark:hover:bg-white/[0.03]">
                        <div class="flex items-center gap-4">
                            <div class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-gray-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
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
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <p class="text-sm text-gray-400 dark:text-gray-500">Sin ventas registradas hoy</p>
                        <a href="{{ route('ventas.index') }}" class="text-xs text-[#D32030] font-semibold mt-1 inline-block hover:underline">Ir al punto de venta →</a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Rendimiento por Sucursal --}}
        <div class="dash-rise bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden" style="animation-delay:.4s">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white">Rendimiento por Sucursal</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Comparativo de ventas del día</p>
                </div>
                <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-white/5 px-3 py-1 rounded-full uppercase tracking-wide">Hoy</span>
            </div>
            <div class="p-6 overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                            <th class="pb-4 text-left">Sucursal</th>
                            <th class="pb-4 text-left">Ventas</th>
                            <th class="pb-4 text-left">Llantas</th>
                            <th class="pb-4 text-left">Rendimiento</th>
                            <th class="pb-4 text-left">vs. Ayer</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                        @forelse($rendimientoSucursales ?? [] as $sucursal)
                            <tr class="dash-row hover:bg-gray-50/50 dark:hover:bg-white/[0.03]">
                                <td class="py-4 pr-4">
                                    <div class="flex items-center gap-3">
                                        <div class="dash-dot w-2 h-2 rounded-full bg-[#D32030] shrink-0"></div>
                                        <span class="text-sm font-semibold text-gray-800 dark:text-white">{{ $sucursal->nombre }}</span>
                                    </div>
                                </td>
                                <td class="py-4 pr-4 text-sm font-bold text-gray-800 dark:text-white">${{ number_format($sucursal->ventas, 2) }}</td>
                                <td class="py-4 pr-4 text-sm text-gray-500 dark:text-gray-400">{{ $sucursal->llantas }} pzas</td>
                                <td class="py-4 pr-4">
                                    @php $maxV = collect($rendimientoSucursales)->max('ventas') ?: 1; $pct = round(($sucursal->ventas / $maxV) * 100); @endphp
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1 h-1.5 bg-gray-100 dark:bg-white/10 rounded-full overflow-hidden">
                                            <div class="dash-bar-fill h-full rounded-full bg-gradient-to-r from-[#D32030] to-[#FF4D5E]" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-400 dark:text-gray-500 w-8">{{ $pct }}%</span>
                                    </div>
                                </td>
                                <td class="py-4">
                                    @if($sucursal->variacion >= 0)
                                        <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">+{{ $sucursal->variacion }}%</span>
                                    @else
                                        <span class="text-sm font-semibold text-[#D32030]">{{ $sucursal->variacion }}%</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center">
                                    <p class="text-sm text-gray-400 dark:text-gray-500">Se mostrarán cuando haya ventas registradas</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Animación de conteo ascendente para las métricas (0 → valor real)
    document.addEventListener('DOMContentLoaded', function () {
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

        // Spotlight que sigue al mouse + inclinación 3D sutil en las tarjetas
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
                    var rotateY = ((x - midX) / midX) * 4;   // máx 4°
                    var rotateX = -((y - midY) / midY) * 4;  // máx 4°

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