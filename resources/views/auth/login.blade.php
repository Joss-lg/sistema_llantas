{{--
  Requisito importante: para que el modo oscuro funcione con las clases "dark:",
  tu tailwind.config.js debe tener:  darkMode: 'class'
--}}
@extends('layouts.guest')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">

<style>
    @media (prefers-reduced-motion: no-preference) {
        .anim-rise        { animation: fade-in-up .8s cubic-bezier(.16,1,.3,1) both; }
        .anim-kenburns    { animation: kenburns 22s ease-in-out infinite; }
        .anim-glow        { animation: glow-pulse 7s ease-in-out infinite; }
        .anim-glow-slow   { animation: glow-pulse 9s ease-in-out infinite; animation-delay: 2s; }
        .particle         { animation: float-up 9s linear infinite; }
        .anim-drawline    { animation: draw-line .6s ease-out both; }
        .anim-beam        { animation: beam-sweep 3.5s ease-in-out infinite; }
        .anim-card-in     { animation: card-in .9s cubic-bezier(.16,1,.3,1) both; }
        .anim-pulse-dot   { animation: pulse-dot 2s ease-in-out infinite; }
        
        /* Animaciones del Switch de Bombilla */
        .anim-toggle-in   { animation: toggle-in .7s cubic-bezier(.16,1,.3,1) both; animation-delay: .15s; }
        .anim-swing       { animation: swing 0.9s cubic-bezier(0.4, 0, 0.2, 1); transform-origin: top center; }
        .anim-flicker     { animation: bulb-flicker 0.4s ease-out; }

        .anim-field-focus { transition: transform .2s ease, box-shadow .2s ease; }
        .parallax-layer   { transition: transform .3s ease-out; }
        .btn-submit       { transition: transform .25s cubic-bezier(.34,1.56,.64,1), box-shadow .25s ease; }
        .btn-submit:hover { transform: translateY(-2px) scale(1.01); }
        .card-hover       { transition: box-shadow .4s ease, border-color .4s ease; }
    }
    
    @keyframes fade-in-up  {
        from { opacity: 0; transform: translateY(16px) scale(.98); filter: blur(4px); }
        to   { opacity: 1; transform: translateY(0) scale(1);      filter: blur(0); }
    }
    @keyframes card-in {
        from { opacity: 0; transform: translateY(20px) scale(.96); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes toggle-in {
        from { opacity: 0; transform: translateY(-15px) scale(.85); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes kenburns    { 0%, 100% { transform: scale(1) translate(0, 0); } 50% { transform: scale(1.09) translate(-1.2%, -1.2%); } }
    @keyframes glow-pulse  { 0%, 100% { opacity: .18; } 50% { opacity: .42; } }
    @keyframes float-up    {
        0%   { transform: translateY(0) translateX(0);       opacity: 0; }
        10%  { opacity: .8; }
        90%  { opacity: .8; }
        100% { transform: translateY(-420px) translateX(14px); opacity: 0; }
    }
    @keyframes draw-line   { from { transform: scaleX(0); } to { transform: scaleX(1); } }
    @keyframes beam-sweep  { 0% { transform: translateY(-100%); opacity: 0; } 15% { opacity: 1; } 85% { opacity: 1; } 100% { transform: translateY(100%); opacity: 0; } }
    @keyframes pulse-dot   { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: .5; transform: scale(.75); } }

    /* Balanceo por inercia al soltar el hilo */
    @keyframes swing {
        0%   { transform: rotate(0deg); }
        20%  { transform: rotate(10deg); }
        40%  { transform: rotate(-6deg); }
        60%  { transform: rotate(3deg); }
        80%  { transform: rotate(-1deg); }
        100% { transform: rotate(0deg); }
    }
    /* Parpadeo eléctrico realista */
    @keyframes bulb-flicker {
        0%   { opacity: 0.1; filter: brightness(0.5); }
        10%  { opacity: 1; filter: brightness(1.5); }
        25%  { opacity: 0.3; filter: brightness(0.8); }
        45%  { opacity: 1; filter: brightness(1.6); }
        65%  { opacity: 0.5; filter: brightness(0.9); }
        100% { opacity: 1; filter: brightness(1); }
    }

    .font-display { font-family: 'Oswald', sans-serif; }
    .font-body    { font-family: 'Inter', sans-serif; }
    .font-mono    { font-family: 'JetBrains Mono', monospace; }

    .dot-grid {
        background-image: radial-gradient(currentColor 1px, transparent 1px);
        background-size: 22px 22px;
    }

    .anim-field-focus:focus-within {
        transform: translateY(-1px);
    }
    
    /* Clase para cerrar los párpados de las llantas */
    .eyelid-closed { transform: translateY(0) !important; }
</style>

<div id="llantas-app" class="relative min-h-screen w-full lg:grid lg:grid-cols-[1.05fr_1fr] bg-[#F4F3EF] dark:bg-[#0B0C0E] font-body transition-colors duration-500">

    {{-- ============ INTERRUPTOR BOMBILLA ============ --}}
    <button type="button" id="theme-toggle"
        aria-label="Cambiar entre modo claro y oscuro"
        class="anim-toggle-in fixed right-5 top-5 sm:right-6 sm:top-6 z-50 flex h-[52px] w-[52px] items-center justify-center rounded-full transition-transform duration-300 hover:scale-105 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#818CF8] focus-visible:ring-offset-2 dark:focus-visible:ring-offset-[#0B0C0E] shadow-xl dark:shadow-black/50 group cursor-pointer select-none">

        {{-- Fondo circular del switch --}}
        <div class="absolute inset-0 rounded-full transition-colors duration-500 overflow-hidden border border-black/10 dark:border-white/10 bg-[#DDF2FF] dark:bg-[#23272F] shadow-inner">
            <div class="absolute inset-0 bg-[#FFD100] blur-xl opacity-30 dark:opacity-0 transition-opacity duration-700"></div>
        </div>

        {{-- Base e icono de bombilla --}}
        <div id="bulb-icon" class="relative z-10 flex flex-col items-center mt-[-6px]">
            <div class="h-1 w-3 bg-[#575C66] rounded-t-sm z-10 border-b border-[#40444B] transition-colors duration-500"></div>
            <div class="h-[3px] w-4 bg-[#575C66] z-10 rounded-sm mb-[1px] transition-colors duration-500"></div>
            <div class="h-[3px] w-4 bg-[#575C66] z-10 rounded-sm transition-colors duration-500"></div>

            <div class="relative rotate-180 -mt-[7px]">
                <svg class="h-7 w-7 text-[#FFC107] dark:text-[#7A808C] transition-all duration-300 drop-shadow-[0_0_8px_rgba(255,193,7,0.7)] dark:drop-shadow-none" viewBox="0 0 64 64" fill="currentColor">
                    <path d="M 32 4 C 18 4, 10 16, 15 29 C 18 36, 22 41, 22 50 L 42 50 C 42 41, 46 36, 49 29 C 54 16, 46 4, 32 4 Z" />
                </svg>
                <svg class="absolute inset-0 h-7 w-7 pointer-events-none" viewBox="0 0 64 64">
                    <path d="M 28 50 L 28 35 M 36 50 L 36 35" stroke="#B45309" class="dark:stroke-[#4B5563] transition-colors duration-[1500ms]" stroke-width="2"/>
                    <path d="M 26 35 L 38 35" stroke="#B45309" class="dark:stroke-[#4B5563] transition-colors duration-[1500ms]" stroke-width="1.5"/>
                    <path d="M 28 35 C 28 15, 36 15, 36 35" fill="none" stroke="#B45309" class="dark:stroke-[#4B5563] transition-colors duration-[1500ms]" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        {{-- EL HILO --}}
        <div id="pull-string-container" class="absolute -top-[120px] right-[10px] flex flex-col items-center h-[134px] transition-transform origin-top z-20">
            <div class="w-[2px] flex-1 bg-white/90 dark:bg-[#9CA3AF] transition-colors duration-500 shadow-sm"></div>
            <div class="w-[10px] h-[14px] bg-white shadow-md rounded-[3px] transition-colors duration-500 border border-black/10"></div>
        </div>

        {{-- Insignia Sol / Luna --}}
        <div class="absolute -bottom-1 -left-1.5 flex h-[26px] w-[26px] items-center justify-center rounded-full border-2 border-[#F4F3EF] dark:border-[#0B0C0E] bg-white transition-colors duration-500 shadow-md">
            <svg class="h-[14px] w-[14px] text-gray-400 dark:opacity-0 transition-opacity duration-300 absolute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>
            </svg>
            <svg class="h-[14px] w-[14px] text-[#5A6070] opacity-0 dark:opacity-100 transition-opacity duration-300 absolute" fill="currentColor" viewBox="0 0 24 24">
                <path d="M20.9 14.6c-.2-.3-.6-.4-.9-.3-1 .4-2 .6-3 .6-4.4 0-8-3.6-8-8 0-1.1.2-2.1.6-3 .1-.3 0-.7-.3-.9-.3-.2-.6-.2-.9 0C4.9 4.9 3 8.2 3 12c0 5 4 9 9 9 3.8 0 7.1-2.4 8.4-5.8.1-.3 0-.6-.5-.6Z"/>
            </svg>
        </div>
    </button>
    {{-- ========================================================================= --}}

    {{-- PANEL IZQUIERDO --}}
    <div id="photo-panel" class="relative overflow-hidden px-8 py-12 sm:px-14 sm:py-16 lg:min-h-screen flex flex-col justify-end">
        <div id="photo-layer" class="parallax-layer absolute inset-0 h-[108%] w-[108%] -left-[4%] -top-[4%]">
            <img src="{{ asset('img/fondo-local.webp') }}" alt="" class="h-full w-full object-cover anim-kenburns">
        </div>
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-[#0B0C0E] via-[#0B0C0E]/55 to-[#0B0C0E]/5"></div>
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-[#0B0C0E]/50 via-transparent to-[#0B0C0E]/30"></div>
        <div class="pointer-events-none absolute inset-0 shadow-[inset_0_0_140px_60px_rgba(11,12,14,0.55)]"></div>

        <svg class="pointer-events-none absolute inset-0 h-full w-full mix-blend-overlay opacity-[0.35]">
            <filter id="grain"><feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2" stitchTiles="stitch"/></filter>
            <rect width="100%" height="100%" filter="url(#grain)"/>
        </svg>

        <div class="pointer-events-none absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-[#818CF8] blur-3xl anim-glow"></div>
        <div class="pointer-events-none absolute -top-20 -right-10 h-72 w-72 rounded-full bg-[#818CF8] blur-3xl anim-glow-slow"></div>

        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <span class="particle absolute left-[8%] bottom-0 h-1 w-1 rounded-full bg-[#818CF8]" style="animation-delay:0s; animation-duration:8s;"></span>
            <span class="particle absolute left-[22%] bottom-0 h-[3px] w-[3px] rounded-full bg-white/70" style="animation-delay:2.2s; animation-duration:11s;"></span>
            <span class="particle absolute left-[41%] bottom-0 h-1 w-1 rounded-full bg-[#818CF8]/80" style="animation-delay:4.1s; animation-duration:9.5s;"></span>
            <span class="particle absolute left-[58%] bottom-0 h-[3px] w-[3px] rounded-full bg-white/60" style="animation-delay:1.3s; animation-duration:10s;"></span>
            <span class="particle absolute left-[74%] bottom-0 h-1 w-1 rounded-full bg-[#818CF8]/70" style="animation-delay:5.5s; animation-duration:8.5s;"></span>
            <span class="particle absolute left-[88%] bottom-0 h-[3px] w-[3px] rounded-full bg-white/70" style="animation-delay:3s; animation-duration:12s;"></span>
        </div>

        <div class="relative z-10 anim-rise mb-6 inline-flex w-fit items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1.5 backdrop-blur-sm">
            <span class="relative flex h-2 w-2">
                <span class="anim-pulse-dot absolute inline-flex h-full w-full rounded-full bg-[#818CF8]"></span>
            </span>
            <span class="font-mono text-[10px] uppercase tracking-widest text-white/70">Panel en línea</span>
        </div>

        <div class="relative z-10 anim-rise" style="animation-delay:.08s">
            <h1 class="font-display text-4xl sm:text-5xl font-semibold uppercase tracking-tight text-white leading-none">
                Inventario<br class="hidden sm:block"> &amp; Ventas
            </h1>
            <p class="mt-3 max-w-sm font-body text-sm text-white/70">
                Control de stock, precios y movimientos entre sucursales, en un mismo lugar.
            </p>
        </div>
    </div>

    {{-- Divisor Desktop --}}
    <div class="hidden lg:block absolute left-[calc(51.2%)] top-0 h-full w-px overflow-hidden">
        <div class="anim-beam absolute left-0 h-1/3 w-full bg-gradient-to-b from-transparent via-[#818CF8]/70 to-transparent"></div>
    </div>

    {{-- PANEL DERECHO --}}
    <div class="relative flex min-h-screen flex-col justify-center overflow-hidden px-6 py-12 sm:px-12 lg:px-20">
        <div class="pointer-events-none absolute inset-0 dot-grid text-[#16171A]/[0.05] dark:text-white/[0.05]"></div>
        <div class="pointer-events-none absolute top-0 right-0 h-80 w-80 rounded-full bg-[#818CF8]/10 dark:bg-[#818CF8]/[0.08] blur-3xl"></div>
        
        <div class="card-hover anim-card-in relative z-10 mx-auto w-full max-w-sm rounded-2xl border border-black/5 dark:border-white/10 bg-white/70 dark:bg-white/[0.03] p-8 shadow-xl shadow-black/5 dark:shadow-black/30 backdrop-blur-xl mt-12">
            
            {{-- ============ LLANTAS ANIMADAS (ESTILO CARICATURA AMIGABLE) ============ --}}
            <div class="absolute -top-[45px] left-1/2 -translate-x-1/2 flex gap-4 z-20">
                
                <div class="eye relative h-[72px] w-[72px] rounded-full bg-[#18191C] border-[4px] border-[#0A0B0E] shadow-xl overflow-hidden flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full opacity-50" style="background: repeating-conic-gradient(transparent 0 10deg, #000 10deg 20deg);"></div>
                    <div class="absolute inset-1.5 rounded-full bg-[#16171A] shadow-[inset_0_4px_10px_rgba(0,0,0,0.8)]"></div>

                    <div class="pupil absolute h-[44px] w-[44px] rounded-full overflow-hidden transition-transform duration-75 ease-out z-10 flex items-center justify-center border border-gray-400 dark:border-gray-500 shadow-[0_4px_10px_rgba(0,0,0,0.6)]">
                        <div class="absolute inset-0" style="background: repeating-conic-gradient(#d1d5db 0 36deg, #9ca3af 36deg 72deg);"></div>
                        
                        <div class="relative h-[16px] w-[16px] bg-[#111] rounded-full z-10 shadow-[inset_0_2px_4px_rgba(0,0,0,1)]"></div>
                        
                        <div class="absolute top-[6px] right-[8px] h-3 w-3 rounded-full bg-white opacity-95 z-20 shadow-[0_0_2px_rgba(255,255,255,0.5)]"></div>
                    </div>

                    <div class="eyelid absolute inset-0 bg-[#0B0C0E] dark:bg-[#16171A] -translate-y-full transition-transform duration-300 ease-in-out z-20 border-b-[3px] border-[#818CF8]/60 shadow-[0_4px_12px_rgba(0,0,0,0.5)]"></div>
                </div>

                <div class="eye relative h-[72px] w-[72px] rounded-full bg-[#18191C] border-[4px] border-[#0A0B0E] shadow-xl overflow-hidden flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full opacity-50" style="background: repeating-conic-gradient(transparent 0 10deg, #000 10deg 20deg);"></div>
                    <div class="absolute inset-1.5 rounded-full bg-[#16171A] shadow-[inset_0_4px_10px_rgba(0,0,0,0.8)]"></div>

                    <div class="pupil absolute h-[44px] w-[44px] rounded-full overflow-hidden transition-transform duration-75 ease-out z-10 flex items-center justify-center border border-gray-400 dark:border-gray-500 shadow-[0_4px_10px_rgba(0,0,0,0.6)]">
                        <div class="absolute inset-0" style="background: repeating-conic-gradient(#d1d5db 0 36deg, #9ca3af 36deg 72deg);"></div>
                        
                        <div class="relative h-[16px] w-[16px] bg-[#111] rounded-full z-10 shadow-[inset_0_2px_4px_rgba(0,0,0,1)]"></div>
                        
                        <div class="absolute top-[6px] right-[8px] h-3 w-3 rounded-full bg-white opacity-95 z-20 shadow-[0_0_2px_rgba(255,255,255,0.5)]"></div>
                    </div>

                    <div class="eyelid absolute inset-0 bg-[#0B0C0E] dark:bg-[#16171A] -translate-y-full transition-transform duration-300 ease-in-out z-20 border-b-[3px] border-[#818CF8]/60 shadow-[0_4px_12px_rgba(0,0,0,0.5)]"></div>
                </div>

            </div>
            {{-- ============================================================= --}}

            <div class="anim-rise text-center mt-4" style="animation-delay:.05s">
                <span class="anim-drawline block mx-auto h-1 w-10 rounded-full bg-[#818CF8]" style="animation-delay:.3s"></span>
                <h2 class="mt-4 font-display text-3xl font-semibold uppercase tracking-tight text-[#16171A] dark:text-[#ECEBE7]">
                    Iniciar sesión
                </h2>
                <p class="mt-2 text-sm text-[#6B6E76] dark:text-[#8A8F98]">
                    Ingresa tus credenciales para acceder al panel.
                </p>
            </div>

            <form class="mt-8 space-y-5" method="POST" action="{{ route('login') }}">
                @csrf
                <div class="anim-rise" style="animation-delay:.12s">
                    <label for="email" class="block text-sm font-medium text-[#16171A] dark:text-[#ECEBE7]">Correo electrónico</label>
                    <div class="anim-field-focus relative mt-1.5">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-[#9A9EA6] transition-colors duration-200 peer-focus:text-[#818CF8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2.5"/><path d="M2 6.5 12 13l10-6.5"/>
                        </svg>
                        <input id="email" name="email" type="email" required placeholder="tucorreo@empresa.com" class="peer block w-full rounded-lg border bg-white dark:bg-[#15161A] pl-10 pr-3.5 py-2.5 text-sm text-[#16171A] dark:text-[#ECEBE7] border-[#DEDCD6] dark:border-[#2A2C31] focus:ring-[#818CF8] focus:border-transparent">
                    </div>
                </div>

                <div class="anim-rise" style="animation-delay:.19s">
                    <label for="password" class="block text-sm font-medium text-[#16171A] dark:text-[#ECEBE7]">Contraseña</label>
                    <div class="anim-field-focus relative mt-1.5">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-[#9A9EA6] transition-colors duration-200 peer-focus:text-[#818CF8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                        </svg>
                        <input id="password" name="password" type="password" required placeholder="••••••••" class="peer block w-full rounded-lg border bg-white dark:bg-[#15161A] pl-10 pr-11 py-2.5 text-sm text-[#16171A] dark:text-[#ECEBE7] border-[#DEDCD6] dark:border-[#2A2C31] focus:ring-[#818CF8] focus:border-transparent">
                        <button type="button" id="toggle-password" tabindex="-1" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-[#9A9EA6] hover:text-[#818CF8] transition-colors">
                            <svg id="pw-eye-open" class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg id="pw-eye-closed" class="hidden h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 4.2A10.4 10.4 0 0 1 12 4c7 0 11 7 11 7a13.2 13.2 0 0 1-3.1 3.7M6.1 6.1C3.6 7.9 2 12 2 12s4 7 11 7c1.2 0 2.3-.2 3.4-.6"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="anim-rise flex items-center" style="animation-delay:.26s">
                    <label for="remember" class="flex cursor-pointer items-center gap-2 select-none">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-[#DEDCD6] dark:border-[#2A2C31] text-[#818CF8] focus:ring-[#818CF8] cursor-pointer">
                        <span class="text-sm text-[#6B6E76] dark:text-[#9A9EA6]">Recordarme</span>
                    </label>
                </div>

                <div class="anim-rise" style="animation-delay:.33s">
                    <button type="submit" class="btn-submit group relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-lg bg-[#16171A] dark:bg-[#818CF8] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:shadow-indigo-500/25">
                        <span class="absolute inset-0 -translate-x-full bg-[#818CF8] dark:bg-[#16171A] transition-transform duration-300 group-hover:translate-x-0"></span>
                        <span class="relative">Ingresar al sistema</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        var root = document.documentElement;
        var toggle = document.getElementById('theme-toggle');
        var stored = localStorage.getItem('theme');
        var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        // Tema claro/oscuro
        function applyTheme(mode) {
            if (mode === 'dark') {
                root.classList.add('dark');
            } else {
                root.classList.remove('dark');
            }
        }
        applyTheme(stored || (prefersDark ? 'dark' : 'light'));

        // === LÓGICA DE LA BOMBILLA ===
        var pullContainer = document.getElementById('pull-string-container');
        var bulbIcon = document.getElementById('bulb-icon');
        var isPulled = false;

        function pullDown(e) {
            if (e.type === 'mousedown' && e.button !== 0) return;
            isPulled = true;
            pullContainer.style.transform = 'translateY(28px)';
            pullContainer.style.transition = 'transform 0.1s cubic-bezier(0.4, 0, 1, 1)';
        }

        function releasePull() {
            if (!isPulled) return;
            isPulled = false;
            
            pullContainer.style.transform = 'translateY(0)';
            pullContainer.style.transition = 'transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)';

            bulbIcon.classList.remove('anim-swing');
            void bulbIcon.offsetWidth; 
            bulbIcon.classList.add('anim-swing');

            var isDark = root.classList.contains('dark');
            var next = isDark ? 'light' : 'dark';
            
            if (next === 'light') {
                bulbIcon.classList.remove('anim-flicker');
                void bulbIcon.offsetWidth;
                bulbIcon.classList.add('anim-flicker');
                setTimeout(function(){ bulbIcon.classList.remove('anim-flicker'); }, 400);
            }

            applyTheme(next);
            localStorage.setItem('theme', next);
        }

        toggle.addEventListener('mousedown', pullDown);
        toggle.addEventListener('touchstart', pullDown, {passive: true});
        window.addEventListener('mouseup', releasePull);
        window.addEventListener('touchend', releasePull);
        toggle.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); pullDown(e); }
        });
        toggle.addEventListener('keyup', function(e) {
            if (e.key === 'Enter' || e.key === ' ') { releasePull(); }
        });

        // === LÓGICA DE LOS OJOS-LLANTAS CARICATURA (TRACKING, PARPADEO Y CIERRE) ===
        var eyes = document.querySelectorAll('.eye');
        var pupils = document.querySelectorAll('.pupil');
        var eyelids = document.querySelectorAll('.eyelid');
        var passwordInput = document.getElementById('password');
        var pwToggle = document.getElementById('toggle-password');
        
        var isPasswordFocused = false;
        var isPasswordVisible = false;

        // Movimiento de todo el rin (pupila gigante animada)
        window.addEventListener('mousemove', function(e) {
            if(isPasswordFocused && !isPasswordVisible) return; // Si la clave está oculta y enfocada, no siguen el ratón
            
            eyes.forEach(function(eye, index) {
                var rect = eye.getBoundingClientRect();
                var x = rect.left + (rect.width / 2);
                var y = rect.top + (rect.height / 2);
                
                var rad = Math.atan2(e.clientX - x, e.clientY - y);
                // Distancia adaptada para que el rin entero se mueva sin salirse de la llanta
                var dist = Math.min(Math.hypot(e.clientX - x, e.clientY - y) / 12, 11);
                
                var pupilX = Math.sin(rad) * dist;
                var pupilY = Math.cos(rad) * dist;
                
                pupils[index].style.transform = 'translate(' + pupilX + 'px, ' + pupilY + 'px)';
            });
        });

        // Parpadeo Aleatorio
        setInterval(function() {
            if(isPasswordFocused && !isPasswordVisible) return; 
            eyelids.forEach(function(lid) {
                lid.style.transitionDuration = '100ms';
                lid.classList.add('eyelid-closed');
                setTimeout(function() {
                    lid.classList.remove('eyelid-closed');
                    lid.style.transitionDuration = '300ms'; 
                }, 150);
            });
        }, 4500);

        // Cerrar ojos al enfocar contraseña
        passwordInput.addEventListener('focus', function() {
            isPasswordFocused = true;
            if(!isPasswordVisible) {
                eyelids.forEach(function(lid) { lid.classList.add('eyelid-closed'); });
                // Centrar el rin al cerrar
                pupils.forEach(function(p) { p.style.transform = 'translate(0px, 0px)'; });
            }
        });
        
        passwordInput.addEventListener('blur', function() {
            isPasswordFocused = false;
            eyelids.forEach(function(lid) { lid.classList.remove('eyelid-closed'); });
        });

        // Mostrar / Ocultar Contraseña
        var eyeOpen = document.getElementById('pw-eye-open');
        var eyeClosed = document.getElementById('pw-eye-closed');

        pwToggle.addEventListener('click', function () {
            isPasswordVisible = passwordInput.type === 'password'; 
            passwordInput.type = isPasswordVisible ? 'text' : 'password';
            eyeOpen.classList.toggle('hidden', isPasswordVisible);
            eyeClosed.classList.toggle('hidden', !isPasswordVisible);

            if(isPasswordFocused) {
                if(isPasswordVisible) {
                    eyelids.forEach(function(lid) { lid.classList.remove('eyelid-closed'); });
                } else {
                    eyelids.forEach(function(lid) { lid.classList.add('eyelid-closed'); });
                    pupils.forEach(function(p) { p.style.transform = 'translate(0px, 0px)'; });
                }
            }
        });

        // Parallax suave al mover el mouse en el panel de foto
        var photoPanel = document.getElementById('photo-panel');
        var photoLayer = document.getElementById('photo-layer');
        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var isFinePointer = window.matchMedia('(pointer: fine)').matches;

        if (photoPanel && photoLayer && !reduceMotion && isFinePointer) {
            photoPanel.addEventListener('mousemove', function (e) {
                var rect = photoPanel.getBoundingClientRect();
                var x = ((e.clientX - rect.left) / rect.width - 0.5) * 2;
                var y = ((e.clientY - rect.top) / rect.height - 0.5) * 2;
                photoLayer.style.transform = 'translate(' + (x * -12) + 'px,' + (y * -12) + 'px)';
            });
            photoPanel.addEventListener('mouseleave', function () {
                photoLayer.style.transform = 'translate(0, 0)';
            });
        }

        // Animación de carga en el botón
        var loginForm = document.querySelector('form[action="{{ route('login') }}"]');
        var submitBtn = loginForm ? loginForm.querySelector('button[type="submit"]') : null;

        if (loginForm && submitBtn) {
            loginForm.addEventListener('submit', function () {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-80', 'cursor-wait');
                submitBtn.innerHTML =
                    '<svg class="relative h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">' +
                    '<path d="M12 3a9 9 0 1 0 9 9" opacity="0.9"/></svg>' +
                    '<span class="relative">Verificando…</span>';
            });
        }
    })();
</script>

@endsection