{{--
  Requisito importante: para que el modo oscuro funcione con las clases "dark:",
  tu tailwind.config.js debe tener:  darkMode: 'class'
  Si usas Vite/Mix con Tailwind, agrega esa línea y recompila assets.
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
        .anim-toggle-in   { animation: toggle-in .7s cubic-bezier(.16,1,.3,1) both; animation-delay: .15s; }
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
        from { opacity: 0; transform: translateY(-10px) scale(.85); }
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
</style>

<div id="llantas-app" class="relative min-h-screen w-full lg:grid lg:grid-cols-[1.05fr_1fr] bg-[#F4F3EF] dark:bg-[#0B0C0E] font-body transition-colors duration-500">

    {{-- ============ INTERRUPTOR CLARO / OSCURO — FIJO EN LA ESQUINA DE TODA LA PANTALLA ============ --}}
    <button type="button" id="theme-toggle"
        aria-label="Cambiar entre modo claro y oscuro"
        class="anim-toggle-in group fixed right-4 top-4 sm:right-6 sm:top-6 z-50 flex h-9 w-16 items-center rounded-full border border-black/10 dark:border-white/10 bg-white/60 dark:bg-white/5 backdrop-blur-md px-1 shadow-sm shadow-black/5 dark:shadow-black/30 transition-all duration-300 hover:border-[#E8590C]/40 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[#E8590C] focus-visible:ring-offset-2 dark:focus-visible:ring-offset-[#0B0C0E]">
        <span id="theme-thumb"
            class="flex h-7 w-7 items-center justify-center rounded-full bg-white dark:bg-[#1B1D22] shadow-md transition-transform duration-300 ease-out translate-x-0 dark:translate-x-7 group-active:scale-90">
            <svg id="icon-sun" class="h-4 w-4 text-[#E8590C] block dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>
            </svg>
            <svg id="icon-moon" class="h-4 w-4 text-[#9A9EA6] hidden dark:block" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20.9 14.6c-.2-.3-.6-.4-.9-.3-1 .4-2 .6-3 .6-4.4 0-8-3.6-8-8 0-1.1.2-2.1.6-3 .1-.3 0-.7-.3-.9-.3-.2-.6-.2-.9 0C4.9 4.9 3 8.2 3 12c0 5 4 9 9 9 3.8 0 7.1-2.4 8.4-5.8.1-.3 0-.6-.5-.6Z"/>
            </svg>
        </span>
    </button>

    {{-- ============ PANEL IZQUIERDO — FOTO DE FONDO ============ --}}
    <div id="photo-panel" class="relative overflow-hidden px-8 py-12 sm:px-14 sm:py-16 lg:min-h-screen flex flex-col justify-end">

        {{-- foto de fondo local: capa exterior hace el parallax con el mouse, la imagen interior hace el zoom Ken Burns --}}
        <div id="photo-layer" class="parallax-layer absolute inset-0 h-[108%] w-[108%] -left-[4%] -top-[4%]">
            <img src="{{ asset('img/fondo-local.webp') }}" alt="" class="h-full w-full object-cover anim-kenburns">
        </div>

        {{-- degradados cinematográficos para contraste y legibilidad del texto --}}
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-[#0B0C0E] via-[#0B0C0E]/55 to-[#0B0C0E]/5"></div>
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-[#0B0C0E]/50 via-transparent to-[#0B0C0E]/30"></div>
        <div class="pointer-events-none absolute inset-0 shadow-[inset_0_0_140px_60px_rgba(11,12,14,0.55)]"></div>

        {{-- textura de grano sutil --}}
        <svg class="pointer-events-none absolute inset-0 h-full w-full mix-blend-overlay opacity-[0.35]">
            <filter id="grain"><feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2" stitchTiles="stitch"/></filter>
            <rect width="100%" height="100%" filter="url(#grain)"/>
        </svg>

        {{-- resplandores cálidos que respiran --}}
        <div class="pointer-events-none absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-[#E8590C] blur-3xl anim-glow"></div>
        <div class="pointer-events-none absolute -top-20 -right-10 h-72 w-72 rounded-full bg-[#E8590C] blur-3xl anim-glow-slow"></div>

        {{-- partículas de polvo/luz flotando --}}
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <span class="particle absolute left-[8%] bottom-0 h-1 w-1 rounded-full bg-[#E8590C]" style="animation-delay:0s; animation-duration:8s;"></span>
            <span class="particle absolute left-[22%] bottom-0 h-[3px] w-[3px] rounded-full bg-white/70" style="animation-delay:2.2s; animation-duration:11s;"></span>
            <span class="particle absolute left-[41%] bottom-0 h-1 w-1 rounded-full bg-[#E8590C]/80" style="animation-delay:4.1s; animation-duration:9.5s;"></span>
            <span class="particle absolute left-[58%] bottom-0 h-[3px] w-[3px] rounded-full bg-white/60" style="animation-delay:1.3s; animation-duration:10s;"></span>
            <span class="particle absolute left-[74%] bottom-0 h-1 w-1 rounded-full bg-[#E8590C]/70" style="animation-delay:5.5s; animation-duration:8.5s;"></span>
            <span class="particle absolute left-[88%] bottom-0 h-[3px] w-[3px] rounded-full bg-white/70" style="animation-delay:3s; animation-duration:12s;"></span>
        </div>

        {{-- chip de estado en vivo --}}
        <div class="relative z-10 anim-rise mb-6 inline-flex w-fit items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1.5 backdrop-blur-sm">
            <span class="relative flex h-2 w-2">
                <span class="anim-pulse-dot absolute inline-flex h-full w-full rounded-full bg-[#E8590C]"></span>
            </span>
            <span class="font-mono text-[10px] uppercase tracking-widest text-white/70">Panel en línea</span>
        </div>

        {{-- texto --}}
        <div class="relative z-10 anim-rise" style="animation-delay:.08s">
            <h1 class="font-display text-4xl sm:text-5xl font-semibold uppercase tracking-tight text-white leading-none">
                Inventario<br class="hidden sm:block"> &amp; Ventas
            </h1>
            <p class="mt-3 max-w-sm font-body text-sm text-white/70">
                Control de stock, precios y movimientos entre sucursales, en un mismo lugar.
            </p>
            <div class="mt-6 flex flex-wrap items-center gap-2 font-mono text-[11px] text-white/50">
                <span class="rounded border border-white/15 px-2 py-1 backdrop-blur-sm transition-colors duration-300 hover:border-[#E8590C]/50 hover:text-white/80">MULTISUCURSAL</span>
                <span class="rounded border border-white/15 px-2 py-1 backdrop-blur-sm transition-colors duration-300 hover:border-[#E8590C]/50 hover:text-white/80">TIEMPO REAL</span>
            </div>
        </div>
    </div>

    {{-- ============ HAZ DE LUZ EN EL DIVISOR (solo escritorio) ============ --}}
    <div class="hidden lg:block absolute left-[calc(51.2%)] top-0 h-full w-px overflow-hidden">
        <div class="anim-beam absolute left-0 h-1/3 w-full bg-gradient-to-b from-transparent via-[#E8590C]/70 to-transparent"></div>
    </div>

    {{-- ============ PANEL DERECHO — FORMULARIO ============ --}}
    <div class="relative flex min-h-screen flex-col justify-center overflow-hidden px-6 py-12 sm:px-12 lg:px-20">

        {{-- fondo decorativo: patrón de puntos + resplandor --}}
        <div class="pointer-events-none absolute inset-0 dot-grid text-[#16171A]/[0.05] dark:text-white/[0.05]"></div>
        <div class="pointer-events-none absolute top-0 right-0 h-80 w-80 rounded-full bg-[#E8590C]/10 dark:bg-[#E8590C]/[0.08] blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-0 left-0 h-72 w-72 rounded-full bg-[#16171A]/5 dark:bg-white/[0.04] blur-3xl"></div>

        <div class="card-hover anim-card-in relative z-10 mx-auto w-full max-w-sm rounded-2xl border border-black/5 dark:border-white/10 bg-white/70 dark:bg-white/[0.03] p-8 shadow-xl shadow-black/5 dark:shadow-black/30 backdrop-blur-xl hover:border-[#E8590C]/20 hover:shadow-2xl hover:shadow-[#E8590C]/5">

            <div class="anim-rise" style="animation-delay:.05s">
                <span class="anim-drawline block h-1 w-10 origin-left rounded-full bg-[#E8590C]" style="animation-delay:.3s"></span>
                <h2 class="mt-4 font-display text-3xl font-semibold uppercase tracking-tight text-[#16171A] dark:text-[#ECEBE7]">
                    Iniciar sesión
                </h2>
                <p class="mt-2 text-sm text-[#6B6E76] dark:text-[#8A8F98]">
                    Ingresa tus credenciales para acceder al panel.
                </p>
            </div>

            <form class="mt-8 space-y-5" method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Correo --}}
                <div class="anim-rise" style="animation-delay:.12s">
                    <label for="email" class="block text-sm font-medium text-[#16171A] dark:text-[#ECEBE7]">
                        Correo electrónico
                    </label>
                    <div class="anim-field-focus relative mt-1.5">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-[#9A9EA6] transition-colors duration-200 peer-focus:text-[#E8590C]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2.5"/><path d="M2 6.5 12 13l10-6.5"/>
                        </svg>
                        <input id="email" name="email" type="email" autocomplete="email" required
                            value="{{ old('email') }}"
                            placeholder="tucorreo@empresa.com"
                            class="peer block w-full rounded-lg border bg-white dark:bg-[#15161A] pl-10 pr-3.5 py-2.5 text-sm text-[#16171A] dark:text-[#ECEBE7] placeholder-[#9A9EA6] shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#E8590C] focus:border-transparent
                            @error('email') border-red-500 focus:ring-red-500 @else border-[#DEDCD6] dark:border-[#2A2C31] @enderror">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contraseña --}}
                <div class="anim-rise" style="animation-delay:.19s">
                    <label for="password" class="block text-sm font-medium text-[#16171A] dark:text-[#ECEBE7]">
                        Contraseña
                    </label>
                    <div class="anim-field-focus relative mt-1.5">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-[#9A9EA6] transition-colors duration-200 peer-focus:text-[#E8590C]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                        </svg>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            placeholder="••••••••"
                            class="peer block w-full rounded-lg border bg-white dark:bg-[#15161A] pl-10 pr-11 py-2.5 text-sm text-[#16171A] dark:text-[#ECEBE7] placeholder-[#9A9EA6] shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#E8590C] focus:border-transparent
                            @error('password') border-red-500 focus:ring-red-500 @else border-[#DEDCD6] dark:border-[#2A2C31] @enderror">
                        <button type="button" id="toggle-password" tabindex="-1"
                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-[#9A9EA6] hover:text-[#E8590C] transition-colors">
                            <svg id="pw-eye-open" class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg id="pw-eye-closed" class="hidden h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 4.2A10.4 10.4 0 0 1 12 4c7 0 11 7 11 7a13.2 13.2 0 0 1-3.1 3.7M6.1 6.1C3.6 7.9 2 12 2 12s4 7 11 7c1.2 0 2.3-.2 3.4-.6"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Recordarme --}}
                <div class="anim-rise flex items-center" style="animation-delay:.26s">
                    <label for="remember" class="flex cursor-pointer items-center gap-2 select-none">
                        <input id="remember" name="remember" type="checkbox" {{ old('remember') ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-[#DEDCD6] dark:border-[#2A2C31] text-[#E8590C] focus:ring-[#E8590C] cursor-pointer">
                        <span class="text-sm text-[#6B6E76] dark:text-[#9A9EA6]">Recordarme</span>
                    </label>
                </div>

                {{-- Submit --}}
                <div class="anim-rise" style="animation-delay:.33s">
                    <button type="submit"
                        class="btn-submit group relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-lg bg-[#16171A] dark:bg-[#E8590C] px-4 py-2.5 text-sm font-semibold text-white shadow-sm active:translate-y-0 active:shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[#E8590C] focus-visible:ring-offset-2 dark:focus-visible:ring-offset-[#0B0C0E]">
                        <span class="absolute inset-0 -translate-x-full bg-[#E8590C] dark:bg-[#16171A] transition-transform duration-300 group-hover:translate-x-0"></span>
                        <span class="relative">Ingresar al sistema</span>
                        <svg class="relative h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M13 6l6 6-6 6"/>
                        </svg>
                    </button>
                </div>
            </form>

            <p class="anim-rise mt-8 text-center font-mono text-[11px] uppercase tracking-widest text-[#9A9EA6]" style="animation-delay:.4s">
                Acceso restringido · Personal autorizado
            </p>
        </div>
    </div>
</div>

<script>
    (function () {
        var root = document.documentElement;
        var toggle = document.getElementById('theme-toggle');
        var stored = localStorage.getItem('theme');
        var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        function applyTheme(mode) {
            if (mode === 'dark') {
                root.classList.add('dark');
            } else {
                root.classList.remove('dark');
            }
        }

        applyTheme(stored || (prefersDark ? 'dark' : 'light'));

        toggle.addEventListener('click', function () {
            var isDark = root.classList.contains('dark');
            var next = isDark ? 'light' : 'dark';
            applyTheme(next);
            localStorage.setItem('theme', next);
        });

        var pwInput = document.getElementById('password');
        var pwToggle = document.getElementById('toggle-password');
        var eyeOpen = document.getElementById('pw-eye-open');
        var eyeClosed = document.getElementById('pw-eye-closed');

        pwToggle.addEventListener('click', function () {
            var isPassword = pwInput.type === 'password';
            pwInput.type = isPassword ? 'text' : 'password';
            eyeOpen.classList.toggle('hidden', isPassword);
            eyeClosed.classList.toggle('hidden', !isPassword);
        });

        // Parallax suave de la foto al mover el mouse (solo escritorio, respeta reduced-motion)
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

        // Estado de carga en el botón al enviar el formulario
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