<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sistema Llantas') }}</title>

    <!-- Aplica el tema guardado ANTES de pintar la página, evita parpadeo blanco/negro -->
    <script>
        (function () {
            var stored = localStorage.getItem('theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (stored === 'dark' || (!stored && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Alpine.js: requerido por los x-data de ventas/inventario/gastos/reportes -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Scripts y Estilos (Vite + Tailwind CSS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-[#0a0a0a] transition-colors duration-300">
    <div id="app"
         class="min-h-screen flex"
         x-data="{ sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false' }"
         x-init="$watch('sidebarOpen', value => localStorage.setItem('sidebarOpen', value))">

        <!-- Sidebar -->
        <aside
            class="shrink-0 bg-white dark:bg-[#0a0a0a] border-r border-gray-200 dark:border-white/5 overflow-hidden transition-[width] duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]"
            :class="sidebarOpen ? 'w-64' : 'w-20'">

            <!-- Contenedor interno que acompaña el ancho del aside -->
            <div class="h-full flex flex-col transition-[width] duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]"
                 :class="sidebarOpen ? 'w-64' : 'w-20'">

                <div class="h-16 flex items-center border-b border-gray-200 dark:border-white/5 transition-all duration-300"
                     :class="sidebarOpen ? 'justify-between px-4' : 'justify-center px-0'">

                    <a href="{{ url('/') }}" class="flex items-center gap-2 overflow-hidden"
                       x-show="sidebarOpen"
                       x-transition:enter="transition ease-out duration-200 delay-100"
                       x-transition:enter-start="opacity-0"
                       x-transition:enter-end="opacity-100"
                       x-transition:leave="transition ease-in duration-75"
                       x-transition:leave-start="opacity-100"
                       x-transition:leave-end="opacity-0">
                        <img src="{{ asset('img/logo-llantas.webp') }}" alt="{{ config('app.name', 'Panel') }}" class="h-8 w-auto object-contain">
                    </a>

                    <!-- Botón hamburguesa (siempre 3 líneas) -->
                    <button
                        @click="sidebarOpen = !sidebarOpen"
                        type="button"
                        aria-label="Alternar barra lateral"
                        class="group relative w-9 h-9 flex items-center justify-center rounded-xl text-gray-500 dark:text-gray-400 bg-transparent hover:bg-gray-100 dark:hover:bg-white/5 hover:text-[#D32030] active:scale-90 transition-all duration-200 shrink-0">
                        <svg class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>

                @auth
                    <div class="px-4 pb-4 pt-2 transition-all duration-300"
                         :class="sidebarOpen ? '' : 'flex justify-center px-0'">
                        <div class="bg-gray-50 dark:bg-white/[0.03] rounded-2xl p-3 flex items-center gap-3 border border-gray-200 dark:border-white/10 transition-all duration-300"
                             :class="sidebarOpen ? '' : 'p-2 border-0 bg-transparent dark:bg-transparent'">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#D32030] to-[#8f1522] text-white flex items-center justify-center font-bold text-lg shrink-0 ring-2 ring-white dark:ring-white/10 shadow-sm">
                                {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                            </div>
                            <div class="flex flex-col overflow-hidden"
                                 x-show="sidebarOpen"
                                 x-transition:enter="transition ease-out duration-200 delay-100"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0">
                                <span class="text-sm font-bold text-gray-900 dark:text-gray-100 leading-none mb-1.5 truncate">{{ Auth::user()->name ?? 'Usuario' }}</span>
                                <span class="text-[10px] uppercase tracking-wider font-semibold text-[#D32030] bg-red-50 dark:bg-[#D32030]/10 px-2 py-0.5 rounded-full w-max">
                                    {{ Auth::user()->rol ?? 'Admin' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto overflow-x-hidden">
                        <a href="{{ route('dashboard') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                           {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-[#D32030]/10 to-transparent text-[#D32030] font-semibold shadow-[inset_3px_0_0_0_#D32030]' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-gray-100 hover:translate-x-0.5' }}"
                           :class="sidebarOpen ? '' : 'justify-center px-0'">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            <span class="whitespace-nowrap" x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200 delay-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Dashboard</span>
                        </a>

                        <a href="{{ route('empleados.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                            {{ request()->routeIs('empleados.*') ? 'bg-gradient-to-r from-[#D32030]/10 to-transparent text-[#D32030] font-semibold shadow-[inset_3px_0_0_0_#D32030]' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-gray-100 hover:translate-x-0.5' }}"
                            :class="sidebarOpen ? '' : 'justify-center px-0'">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <span class="whitespace-nowrap" x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200 delay-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Empleados</span>
                        </a>

                        <a href="{{ route('ventas.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                           {{ request()->routeIs('ventas.index') ? 'bg-gradient-to-r from-[#D32030]/10 to-transparent text-[#D32030] font-semibold shadow-[inset_3px_0_0_0_#D32030]' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-gray-100 hover:translate-x-0.5' }}"
                           :class="sidebarOpen ? '' : 'justify-center px-0'">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span class="whitespace-nowrap" x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200 delay-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Punto de Venta</span>
                        </a>

                        <a href="{{ route('ventas.historial') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                           {{ request()->routeIs('ventas.historial') ? 'bg-gradient-to-r from-[#D32030]/10 to-transparent text-[#D32030] font-semibold shadow-[inset_3px_0_0_0_#D32030]' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-gray-100 hover:translate-x-0.5' }}"
                           :class="sidebarOpen ? '' : 'justify-center px-0'">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            <span class="whitespace-nowrap" x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200 delay-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Historial de Ventas</span>
                        </a>

                        <a href="{{ route('inventario.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                           {{ request()->routeIs('inventario.*') ? 'bg-gradient-to-r from-[#D32030]/10 to-transparent text-[#D32030] font-semibold shadow-[inset_3px_0_0_0_#D32030]' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-gray-100 hover:translate-x-0.5' }}"
                           :class="sidebarOpen ? '' : 'justify-center px-0'">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <span class="whitespace-nowrap" x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200 delay-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Inventario</span>
                        </a>

                        <a href="{{ route('clientes.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                           {{ request()->routeIs('clientes.*') ? 'bg-gradient-to-r from-[#D32030]/10 to-transparent text-[#D32030] font-semibold shadow-[inset_3px_0_0_0_#D32030]' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-gray-100 hover:translate-x-0.5' }}"
                           :class="sidebarOpen ? '' : 'justify-center px-0'">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span class="whitespace-nowrap" x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200 delay-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Clientes</span>
                        </a>

                        <a href="{{ route('reportes.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                           {{ request()->routeIs('reportes.*') ? 'bg-gradient-to-r from-[#D32030]/10 to-transparent text-[#D32030] font-semibold shadow-[inset_3px_0_0_0_#D32030]' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-gray-100 hover:translate-x-0.5' }}"
                           :class="sidebarOpen ? '' : 'justify-center px-0'">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            <span class="whitespace-nowrap" x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200 delay-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Reportes</span>
                        </a>
                    </nav>

                    <div class="p-4 border-t border-gray-100 dark:border-white/5">
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[#D32030] hover:bg-red-50 dark:hover:bg-[#D32030]/10 hover:translate-x-0.5 transition-all duration-200"
                           :class="sidebarOpen ? 'w-full' : 'justify-center px-0'">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            <span class="whitespace-nowrap" x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200 delay-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">Cerrar Sesión</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                    </div>
                @endauth
            </div>
        </aside>

        <!-- Contenido Principal -->
        <div class="flex-1 flex flex-col h-screen overflow-hidden">
            <header class="h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 border-b border-gray-200 dark:border-white/5 bg-white dark:bg-[#0a0a0a] shrink-0 transition-colors duration-300">
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">@yield('header_title', 'Panel de Control')</h1>

                @guest
                    <div class="flex items-center gap-3">
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white px-3 py-2 rounded-md transition">
                                {{ __('Login') }}
                            </a>
                        @endif

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-sm font-bold bg-[#D32030] text-white hover:bg-[#B91C2C] px-4 py-2 rounded-md shadow-sm transition">
                                {{ __('Registro') }}
                            </a>
                        @endif
                    </div>
                @endguest
            </header>

            <main class="flex-1 overflow-y-auto py-8 px-4 sm:px-6 lg:px-8">
                @yield('content')
            </main>
        </div>
    </div>
<!-- Detección de retroceso en el historial para destruir sesión -->
    <script>
        (function() {
            // Se inserta un estado extra en el historial al cargar la vista
            history.pushState(null, null, location.href);

            window.addEventListener('popstate', function (event) {
                // Al presionar la flecha 'Atrás', enviamos una petición para matar la sesión
                fetch("{{ route('logout.back') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                }).finally(function() {
                    // Sin importar la respuesta, redirigimos inmediatamente al login
                    window.location.href = "{{ route('login') }}";
                });
            });
        })();
    </script>
</body>
</html>
