@extends('layouts.app')

@section('header_title', 'Punto de Venta')

@section('content')
{{-- Estilos globales para eliminar barras de desplazamiento y animaciones de resplandor (glow) --}}
<style>
    html, body, * {
        -ms-overflow-style: none !important; /* IE y Edge antiguo */
        scrollbar-width: none !important;    /* Firefox */
    }
    html::-webkit-scrollbar,
    body::-webkit-scrollbar,
    *::-webkit-scrollbar {
        display: none !important;            /* Chrome, Safari, Edge, Opera */
        width: 0 !important;
        height: 0 !important;
        background: transparent !important;
    }

    /* EFECTOS DE RESPLANDOR (GLOW) */
    .btn-glow-white {
        transition: all 0.3s ease;
    }
    .btn-glow-white:hover, .btn-glow-white:active {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12);
    }
    .dark .btn-glow-white:hover, .dark .btn-glow-white:active {
        box-shadow: 0 10px 25px -5px rgba(255, 255, 255, 0.22);
    }

    .btn-glow-green {
        transition: all 0.3s ease;
    }
    .btn-glow-green:hover, .btn-glow-green:active {
        box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.5);
    }
    
    .btn-glow-red {
        transition: all 0.3s ease;
    }
    .btn-glow-red:hover, .btn-glow-red:active {
        box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.5);
    }

    .btn-glow-blue {
        transition: all 0.3s ease;
    }
    .btn-glow-blue:hover, .btn-glow-blue:active {
        box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.5);
    }
</style>

<div x-data="puntoVenta()" class="relative transition-colors duration-300">

    {{-- Fondo animado con luces suavizadas --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0 opacity-20 dark:opacity-10 transition-opacity duration-500" aria-hidden="true">
        <div class="absolute -top-[160px] -left-[80px] w-[420px] h-[420px] rounded-full bg-[#D32030] blur-[90px] animate-[pulse_16s_ease-in-out_infinite]"></div>
        <div class="absolute top-[40px] -right-[140px] w-[420px] h-[420px] rounded-full bg-blue-500 blur-[90px] animate-[pulse_20s_ease-in-out_infinite]" style="animation-delay: -6s;"></div>
    </div>

    <div class="relative z-10 flex flex-col lg:flex-row gap-4 lg:gap-6 h-auto lg:h-[calc(100vh-130px)] pb-6 lg:pb-0 px-2 sm:px-0">

        {{-- TOAST DE NOTIFICACIONES --}}
        <div x-show="toastVisible" x-cloak
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 -translate-y-3 -translate-x-1/2"
            x-transition:enter-end="opacity-100 translate-y-0 -translate-x-1/2"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 -translate-x-1/2"
            x-transition:leave-end="opacity-0 -translate-y-1 -translate-x-1/2"
            class="fixed top-5 left-1/2 z-50 px-4 py-2.5 rounded-xl shadow-lg shadow-black/10 dark:shadow-black/40 text-sm font-semibold flex items-center gap-2 max-w-sm"
            :class="toastType === 'error' ? 'bg-red-500 text-white' : 'bg-gray-900 dark:bg-[#0A0A0A] text-white border border-transparent dark:border-neutral-800'">
            <svg x-show="toastType !== 'error'" class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <svg x-show="toastType === 'error'" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span x-text="toastMsg"></span>
        </div>

        {{-- PANEL IZQUIERDO: PRODUCTOS --}}
        <div class="flex-1 flex flex-col bg-white dark:bg-[#151515] rounded-3xl border border-gray-200 dark:border-neutral-800 overflow-hidden shadow-lg dark:shadow-none h-[65vh] lg:h-full transform transition-all duration-700 ease-out delay-100"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            <div class="h-1 w-full bg-gradient-to-r from-[#D32030] via-emerald-500 to-blue-500 bg-[length:200%_100%] animate-gradient-flow shrink-0"></div>

            <div class="px-4 sm:px-6 pt-5 pb-4 border-b border-gray-100 dark:border-neutral-800 space-y-4 shrink-0 transition-shadow duration-300"
                :class="scrolledProductos ? 'shadow-[0_6px_12px_-8px_rgba(0,0,0,0.15)] dark:shadow-[0_6px_12px_-8px_rgba(0,0,0,0.8)]' : ''">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col sm:flex-row sm:items-start gap-2 sm:gap-6">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Productos y Servicios</h2>
                            <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                <span class="text-[10px] font-bold px-2 py-1 rounded-lg bg-gray-100 dark:bg-[#0A0A0A] border border-transparent dark:border-neutral-800 text-gray-500 dark:text-neutral-400 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-neutral-600"></span>
                                    <span x-text="productosFiltrados().length + ' de ' + productos.length"></span>
                                </span>
                                <span class="text-[10px] font-bold px-2 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-transparent dark:border-emerald-900/50 text-emerald-700 dark:text-emerald-500 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span x-text="contarConStock() + ' con stock'"></span>
                                </span>
                                <span class="text-[10px] font-bold px-2 py-1 rounded-lg bg-red-50 dark:bg-red-900/20 border border-transparent dark:border-red-900/50 text-red-600 dark:text-red-500 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    <span x-text="contarAgotados() + ' agotados'"></span>
                                </span>
                            </div>
                        </div>

                        @if($esAdmin)
                            {{-- DESPLEGABLE PERSONALIZADO: SUCURSAL --}}
                            <div x-data="{ openSucursal: false }" @click.away="openSucursal = false" class="relative">
                                <div class="flex items-center gap-2 bg-amber-50 dark:bg-[#0A0A0A] border border-amber-200 dark:border-neutral-800 px-3 py-1.5 rounded-xl shadow-sm hover:shadow transition-all duration-300">
                                    <svg class="w-3.5 h-3.5 text-amber-700 dark:text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
                                    <span class="text-[10px] font-black text-amber-800 dark:text-amber-500 uppercase tracking-wider">Sucursal:</span>
                                    
                                    <button @click="openSucursal = !openSucursal" type="button" 
                                        class="text-xs font-bold bg-white dark:bg-[#151515] border border-amber-300 dark:border-neutral-700 rounded-lg px-2 py-1 text-gray-700 dark:text-white flex items-center gap-2 focus:outline-none cursor-pointer btn-glow-white">
                                        <span x-text="sucursales.find(s => s.id == sucursalSeleccionada)?.nombre || 'Seleccionar'"></span>
                                        <svg class="w-3 h-3 text-gray-400 transition-transform duration-200" :class="openSucursal ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>

                                    <div x-show="openSucursal" x-cloak
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="absolute top-full left-0 mt-2 w-48 bg-white dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl shadow-xl z-50 p-1 space-y-0.5">
                                        <template x-for="suc in sucursales" :key="suc.id">
                                            <button @click="sucursalSeleccionada = suc.id; openSucursal = false" type="button"
                                                class="w-full text-left px-3 py-2 text-xs font-bold rounded-lg transition-colors flex items-center justify-between"
                                                :class="sucursalSeleccionada == suc.id ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-800'">
                                                <span x-text="suc.nombre"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div>
                        <button @click="limpiarFiltros()" x-show="hayFiltrosActivos()" x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-90"
                            x-transition:enter-end="opacity-100 scale-100"
                            class="text-xs text-red-500 dark:text-red-400 border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-900/10 px-3 py-2 rounded-lg font-medium btn-glow-red hover:bg-red-100 dark:hover:bg-red-900/30 active:scale-95 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            <span class="hidden sm:inline">Limpiar filtros</span>
                            <span class="sm:hidden">Limpiar</span>
                        </button>
                    </div>
                </div>

                {{-- CAMPO DE BÚSQUEDA --}}
                <div class="relative">
                    <input x-model="busqueda" type="text" placeholder="Buscar por medida, marca o descripción..."
                        class="w-full pl-11 pr-10 py-3 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-neutral-600 focus:outline-none focus:ring-2 focus:ring-[#D32030] focus:border-transparent focus:bg-white dark:focus:bg-[#151515] transition-all">
                    <svg class="absolute left-4 top-3.5 w-4 h-4 transition-colors" :class="busqueda ? 'text-[#D32030] dark:text-red-500' : 'text-gray-400 dark:text-neutral-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button x-show="busqueda" @click="busqueda = ''" x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-75"
                        x-transition:enter-end="opacity-100 scale-100"
                        class="absolute right-3.5 top-3 w-6 h-6 rounded-full bg-gray-200 dark:bg-neutral-800 hover:bg-gray-300 dark:hover:bg-neutral-700 active:scale-90 flex items-center justify-center text-gray-500 dark:text-neutral-400 transition-all btn-glow-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- FILTROS --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-6 flex-wrap">
                        
                        {{-- DESPLEGABLE MARCA --}}
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-wider shrink-0">Marca</span>
                            <div x-data="{ openMarca: false }" @click.away="openMarca = false" class="relative min-w-[170px]">
                                <button @click="openMarca = !openMarca" type="button"
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-sm border border-gray-200 dark:border-neutral-800 bg-white dark:bg-[#0A0A0A] text-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#D32030] cursor-pointer transition-shadow btn-glow-white">
                                    <span class="truncate" x-text="filtroMarca ? filtroMarca + ' (' + contarMarca(filtroMarca) + ')' : 'Todas las marcas'"></span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="openMarca ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div x-show="openMarca" x-cloak
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute left-0 mt-1.5 w-full max-h-60 overflow-y-auto rounded-xl bg-white dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 shadow-2xl z-50 p-1 space-y-0.5">
                                    
                                    <button @click="filtroMarca = ''; openMarca = false" type="button"
                                        class="w-full text-left px-3 py-2 text-xs rounded-lg transition-colors flex items-center justify-between"
                                        :class="filtroMarca === '' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold' : 'text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-800/60'">
                                        <span>Todas las marcas</span>
                                    </button>

                                    <template x-for="m in marcas" :key="m">
                                        <button @click="filtroMarca = m; openMarca = false" type="button"
                                            class="w-full text-left px-3 py-2 text-xs rounded-lg transition-colors flex items-center justify-between"
                                            :class="filtroMarca === m ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold' : 'text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-800/60'">
                                            <span x-text="m" class="truncate pr-2"></span>
                                            <span class="text-[10px] text-gray-400 dark:text-neutral-500 font-normal shrink-0" x-text="'(' + contarMarca(m) + ')'"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- FILTRO USO --}}
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-wider shrink-0">Uso</span>
                            <div class="flex gap-1.5 overflow-x-auto">
                                <button @click="filtroUso = ''" class="px-3 py-1.5 rounded-full text-[11px] font-medium border transition-all duration-200 active:scale-95 shrink-0 btn-glow-white"
                                    :class="filtroUso === '' ? 'bg-blue-500 text-white border-blue-500 shadow-[0_0_15px_rgba(59,130,246,0.5)]' : 'bg-white dark:bg-[#0A0A0A] text-gray-500 dark:text-neutral-400 border-gray-200 dark:border-neutral-800 hover:border-blue-300 dark:hover:border-blue-500/50'">
                                    Todos
                                </button>
                                <template x-for="u in usos" :key="u">
                                    <button @click="filtroUso = u" class="px-3 py-1.5 rounded-full text-[11px] font-medium border transition-all duration-200 active:scale-95 shrink-0 btn-glow-white"
                                        :class="filtroUso === u ? 'bg-blue-500 text-white border-blue-500 shadow-[0_0_15px_rgba(59,130,246,0.5)]' : 'bg-white dark:bg-[#0A0A0A] text-gray-500 dark:text-neutral-400 border-gray-200 dark:border-neutral-800 hover:border-blue-300 dark:hover:border-blue-500/50'"
                                        x-text="u">
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 flex-wrap">
                        {{-- FILTRO STOCK --}}
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-wider shrink-0">Stock</span>
                            <div class="flex gap-1.5 overflow-x-auto">
                                <button @click="filtroStock = ''" class="px-3 py-1.5 rounded-full text-[11px] font-medium border transition-all duration-200 active:scale-95 shrink-0 flex items-center gap-1 btn-glow-white"
                                    :class="filtroStock === '' ? 'bg-gray-800 dark:bg-neutral-700 text-white border-gray-800 dark:border-neutral-600' : 'bg-white dark:bg-[#0A0A0A] text-gray-500 dark:text-neutral-400 border-gray-200 dark:border-neutral-800'">
                                    Todos
                                </button>
                                <button @click="filtroStock = 'disponible'" class="px-3 py-1.5 rounded-full text-[11px] font-medium border transition-all duration-200 active:scale-95 shrink-0 flex items-center gap-1 btn-glow-green"
                                    :class="filtroStock === 'disponible' ? 'bg-emerald-500 text-white border-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.5)]' : 'bg-white dark:bg-[#0A0A0A] text-gray-500 dark:text-neutral-400 border-gray-200 dark:border-neutral-800'">
                                    Con stock
                                </button>
                                <button @click="filtroStock = 'agotado'" class="px-3 py-1.5 rounded-full text-[11px] font-medium border transition-all duration-200 active:scale-95 shrink-0 flex items-center gap-1 btn-glow-red"
                                    :class="filtroStock === 'agotado' ? 'bg-[#D32030] text-white border-[#D32030] shadow-[0_0_15px_rgba(211,32,48,0.5)]' : 'bg-white dark:bg-[#0A0A0A] text-gray-500 dark:text-neutral-400 border-gray-200 dark:border-neutral-800'">
                                    Agotado
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- FILTRO CATEGORÍA --}}
                    <div class="flex items-center gap-2 w-full pt-2 border-t border-gray-50 dark:border-neutral-800">
                        <span class="text-[10px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-wider shrink-0">Categoría</span>
                        <div class="flex gap-1.5 overflow-x-auto">
                            <template x-for="cat in ['Todos', 'Llanta', 'Rin', 'Accesorio', 'Servicio']" :key="cat">
                                <button @click="filtroCategoria = cat" class="px-4 py-1.5 rounded-full text-[11px] font-bold border transition-all duration-200 active:scale-95 shrink-0 flex items-center gap-1.5 btn-glow-white"
                                    :class="filtroCategoria === cat ? 'bg-indigo-600 text-white border-indigo-600 shadow-[0_0_15px_rgba(79,70,229,0.5)]' : 'bg-white dark:bg-[#0A0A0A] text-gray-500 dark:text-neutral-400 border-gray-200 dark:border-neutral-800 hover:border-indigo-300 dark:hover:border-indigo-500/50'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="cat === 'Todos' ? (filtroCategoria === cat ? 'bg-white' : 'bg-gray-300 dark:bg-neutral-600') : bordeTipo(cat)"></span>
                                    <span x-text="cat"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- GRID DE PRODUCTOS CON RESPLANDOR (GLOW) AL PASAR EL MOUSE --}}
            <div class="flex-1 overflow-y-auto p-4 sm:p-5" @scroll.passive="scrolledProductos = $event.target.scrollTop > 4">
                <div class="grid grid-cols-1 sm:grid-cols-2 2xl:grid-cols-3 gap-4 sm:gap-5">
                    <template x-for="(p, index) in productosFiltrados()" :key="p.id">
                        <button @click="agregar(p)" :disabled="p.tipo !== 'Servicio' && p.stock_cantidad <= 0"
                            class="group relative flex flex-col text-left p-5 pl-6 rounded-2xl border transition-all duration-300 h-full overflow-hidden"
                            :class="[
                                (p.tipo === 'Servicio' || p.stock_cantidad > 0)
                                    ? 'border-gray-200 dark:border-neutral-800 bg-white dark:bg-[#0A0A0A]/80 hover:border-emerald-300 dark:hover:border-emerald-600 hover:bg-emerald-50/30 dark:hover:bg-emerald-900/10 hover:shadow-[0_10px_25px_-5px_rgba(16,185,129,0.3)] dark:hover:shadow-[0_10px_25px_-5px_rgba(16,185,129,0.2)] hover:-translate-y-0.5 active:scale-[0.98] cursor-pointer'
                                    : 'border-gray-100 dark:border-neutral-800/50 bg-gray-50 dark:bg-neutral-900/30 opacity-50 cursor-not-allowed'
                            ]">

                            <span class="absolute left-0 top-3 bottom-3 w-1 rounded-full transition-colors" :class="bordeTipo(p.tipo)"></span>

                            <div class="w-full flex-1">
                                <div class="flex items-start justify-between mb-3">
                                    <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider border dark:border-transparent"
                                        :class="tipoBadgeClase(p.tipo)" x-text="p.tipo"></span>

                                    <template x-if="p.tipo !== 'Servicio'">
                                        <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full inline-flex items-center gap-1 border dark:border-transparent"
                                            :class="p.stock_cantidad > 5 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : p.stock_cantidad > 0 ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : 'bg-red-100 dark:bg-red-900/30 text-[#D32030] dark:text-red-400'">
                                            <span x-text="p.stock_cantidad > 0 ? 'Stock: ' + p.stock_cantidad : 'Agotado'"></span>
                                        </span>
                                    </template>
                                </div>

                                <div class="font-bold text-base text-gray-800 dark:text-white leading-tight mb-1" x-text="p.tipo === 'Servicio' ? p.descripcion : p.marca"></div>

                                <template x-if="p.tipo !== 'Servicio'">
                                    <div class="text-xs text-gray-500 dark:text-neutral-500 font-mono" x-text="p.medida"></div>
                                </template>
                            </div>

                            <div class="w-full mt-4 pt-3 border-t border-gray-100 dark:border-neutral-800 flex items-center justify-between">
                                <div class="text-lg font-black text-emerald-600 dark:text-emerald-400 tabular-nums" x-text="'$' + (+p.precio_publico).toLocaleString()"></div>
                                <svg class="w-4 h-4 text-emerald-500 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </div>
                        </button>
                    </template>
                </div>

                <div x-show="productosFiltrados().length === 0" x-cloak class="text-center text-gray-400 dark:text-neutral-500 py-20 text-sm">
                    No se encontraron registros con los filtros actuales.
                </div>
            </div>
        </div>

        {{-- PANEL DERECHO: CARRITO / COMPRA (TOTALMENTE COMPLETO) --}}
        <div class="w-full lg:w-[380px] xl:w-[420px] flex flex-col bg-white dark:bg-[#151515] rounded-3xl border border-gray-200 dark:border-neutral-800 shadow-lg overflow-hidden h-full">
            
            {{-- Encabezado del Carrito --}}
            <div class="p-5 border-b border-gray-100 dark:border-neutral-800 flex items-center justify-between bg-gray-50/50 dark:bg-[#0A0A0A]/50">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 2 0 100 4 2 2 0 000-4z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-800 dark:text-white">Carrito de Compra</h3>
                        <p class="text-xs text-gray-400 dark:text-neutral-500" x-text="carrito.length + ' elementos seleccionados'"></p>
                    </div>
                </div>

                <button @click="limpiarCarrito()" x-show="carrito.length > 0" x-cloak
                    class="text-xs text-red-500 hover:text-red-600 font-semibold px-2.5 py-1.5 rounded-lg border border-red-200 dark:border-red-900/40 hover:bg-red-50 dark:hover:bg-red-900/20 btn-glow-red transition-all">
                    Vaciar
                </button>
            </div>

            {{-- Lista de Items en el Carrito --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <template x-for="(item, idx) in carrito" :key="item.id">
                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-gray-50 dark:bg-[#0A0A0A] border border-gray-100 dark:border-neutral-800/80 transition-all">
                        
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-bold text-gray-800 dark:text-white truncate" x-text="item.tipo === 'Servicio' ? item.descripcion : item.marca + ' - ' + item.medida"></h4>
                            <div class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-0.5" x-text="'$' + (+item.precio_publico).toLocaleString()"></div>
                        </div>

                        {{-- Control de Cantidad --}}
                        <div class="flex items-center gap-1.5 bg-white dark:bg-[#151515] border border-gray-200 dark:border-neutral-800 rounded-xl p-1">
                            <button @click="disminuir(item)" class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-neutral-800 hover:bg-gray-200 dark:hover:bg-neutral-700 flex items-center justify-center text-gray-600 dark:text-neutral-300 transition-colors btn-glow-white">
                                -
                            </button>
                            <span class="text-xs font-bold w-6 text-center text-gray-800 dark:text-white" x-text="item.cantidad"></span>
                            <button @click="aumentar(item)" class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-neutral-800 hover:bg-gray-200 dark:hover:bg-neutral-700 flex items-center justify-center text-gray-600 dark:text-neutral-300 transition-colors btn-glow-white">
                                +
                            </button>
                        </div>

                        {{-- Subtotal por Item --}}
                        <div class="text-right min-w-[60px]">
                            <span class="text-xs font-black text-gray-900 dark:text-white" x-text="'$' + (item.cantidad * item.precio_publico).toLocaleString()"></span>
                        </div>

                        {{-- Botón Eliminar --}}
                        <button @click="quitar(item)" class="text-gray-400 hover:text-red-500 transition-colors p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>

                {{-- Estado Vacío --}}
                <div x-show="carrito.length === 0" class="h-full flex flex-col items-center justify-center text-center p-6 text-gray-400 dark:text-neutral-600 space-y-2">
                    <svg class="w-12 h-12 stroke-current opacity-50" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <p class="text-xs font-medium">No hay productos agregados al carrito</p>
                </div>
            </div>

            {{-- Resumen de Pago y Totales --}}
            <div class="p-5 border-t border-gray-100 dark:border-neutral-800 bg-gray-50/50 dark:bg-[#0A0A0A]/50 space-y-4">
                
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between text-gray-500 dark:text-neutral-400">
                        <span>Subtotal</span>
                        <span class="font-bold text-gray-800 dark:text-white" x-text="'$' + subtotal().toLocaleString()"></span>
                    </div>
                    <div class="flex justify-between text-gray-500 dark:text-neutral-400">
                        <span>IVA (16%)</span>
                        <span class="font-bold text-gray-800 dark:text-white" x-text="'$' + iva().toLocaleString()"></span>
                    </div>
                    <div class="flex justify-between text-base font-black text-gray-900 dark:text-white pt-2 border-t border-gray-200 dark:border-neutral-800">
                        <span>Total</span>
                        <span class="text-emerald-600 dark:text-emerald-400" x-text="'$' + total().toLocaleString()"></span>
                    </div>
                </div>

                {{-- Método de Pago --}}
                <div class="grid grid-cols-3 gap-2">
                    <button @click="metodoPago = 'Efectivo'" 
                        class="py-2 px-1 text-center rounded-xl text-xs font-bold border transition-all btn-glow-white"
                        :class="metodoPago === 'Efectivo' ? 'bg-emerald-500 text-white border-emerald-500 shadow-md' : 'bg-white dark:bg-[#151515] text-gray-700 dark:text-neutral-300 border-gray-200 dark:border-neutral-800'">
                        Efectivo
                    </button>
                    <button @click="metodoPago = 'Tarjeta'" 
                        class="py-2 px-1 text-center rounded-xl text-xs font-bold border transition-all btn-glow-white"
                        :class="metodoPago === 'Tarjeta' ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white dark:bg-[#151515] text-gray-700 dark:text-neutral-300 border-gray-200 dark:border-neutral-800'">
                        Tarjeta
                    </button>
                    <button @click="metodoPago = 'Transferencia'" 
                        class="py-2 px-1 text-center rounded-xl text-xs font-bold border transition-all btn-glow-white"
                        :class="metodoPago === 'Transferencia' ? 'bg-indigo-500 text-white border-indigo-500 shadow-md' : 'bg-white dark:bg-[#151515] text-gray-700 dark:text-neutral-300 border-gray-200 dark:border-neutral-800'">
                        Transf.
                    </button>
                </div>

                {{-- Botón Finalizar / Cobrar con Resplandor --}}
                <button @click="procesarVenta()" :disabled="carrito.length === 0"
                    class="w-full py-3.5 px-4 rounded-2xl font-bold text-white text-sm bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 active:scale-[0.99] transition-all btn-glow-green disabled:opacity-40 disabled:cursor-not-allowed shadow-lg flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Completar Venta</span>
                </button>

            </div>
        </div>
    </div>
</div>

{{-- SCRIPT ALPINE.JS INTEGRADO --}}
<script>
function puntoVenta() {
    return {
        cargado: true,
        scrolledProductos: false,
        toastVisible: false,
        toastMsg: '',
        toastType: 'success',
        
        busqueda: '',
        filtroMarca: '',
        filtroUso: '',
        filtroStock: '',
        filtroPrecio: '',
        filtroCategoria: 'Todos',
        
        sucursalSeleccionada: 1,
        metodoPago: 'Efectivo',
        
        sucursales: [
            { id: 1, nombre: 'Sucursal Matriz' },
            { id: 2, nombre: 'Sucursal Norte' }
        ],

        marcas: ['Michelin', 'Pirelli', 'Bridgestone', 'Brembo', 'Continental'],
        usos: ['Automóvil', 'Camioneta', 'Moto'],
        
        // Datos de prueba / servidor
        productos: [
            { id: 101, tipo: 'Llanta', marca: 'Michelin', medida: '205/55 R16', uso: 'Automóvil', stock_cantidad: 12, precio_publico: 2450 },
            { id: 102, tipo: 'Llanta', marca: 'Pirelli', medida: '225/45 R17', uso: 'Automóvil', stock_cantidad: 3, precio_publico: 2890 },
            { id: 103, tipo: 'Rin', marca: 'Brembo', medida: '17 " 5x114', uso: 'Camioneta', stock_cantidad: 0, precio_publico: 4200 },
            { id: 104, tipo: 'Servicio', marca: 'Alineación y Balanceo', medida: 'N/A', uso: 'Automóvil', stock_cantidad: 999, descripcion: 'Alineación láser 4 ruedas', precio_publico: 650 },
            { id: 105, tipo: 'Accesorio', marca: 'Continental', medida: 'Universal', uso: 'Moto', stock_cantidad: 8, descripcion: 'Válvulas de aluminio', precio_publico: 150 }
        ],

        carrito: [],

        // Métodos y lógica
        productosFiltrados() {
            return this.productos.filter(p => {
                const matchBusqueda = !this.busqueda || 
                    (p.marca && p.marca.toLowerCase().includes(this.busqueda.toLowerCase())) ||
                    (p.medida && p.medida.toLowerCase().includes(this.busqueda.toLowerCase())) ||
                    (p.descripcion && p.descripcion.toLowerCase().includes(this.busqueda.toLowerCase()));

                const matchMarca = !this.filtroMarca || p.marca === this.filtroMarca;
                const matchUso = !this.filtroUso || p.uso === this.filtroUso;
                const matchCategoria = this.filtroCategoria === 'Todos' || p.tipo === this.filtroCategoria;
                
                let matchStock = true;
                if (this.filtroStock === 'disponible') matchStock = p.tipo === 'Servicio' || p.stock_cantidad > 0;
                if (this.filtroStock === 'agotado') matchStock = p.tipo !== 'Servicio' && p.stock_cantidad <= 0;

                return matchBusqueda && matchMarca && matchUso && matchCategoria && matchStock;
            });
        },

        contarConStock() {
            return this.productos.filter(p => p.tipo === 'Servicio' || p.stock_cantidad > 0).length;
        },

        contarAgotados() {
            return this.productos.filter(p => p.tipo !== 'Servicio' && p.stock_cantidad <= 0).length;
        },

        contarMarca(m) {
            return this.productos.filter(p => p.marca === m).length;
        },

        hayFiltrosActivos() {
            return this.busqueda || this.filtroMarca || this.filtroUso || this.filtroStock || this.filtroCategoria !== 'Todos';
        },

        limpiarFiltros() {
            this.busqueda = '';
            this.filtroMarca = '';
            this.filtroUso = '';
            this.filtroStock = '';
            this.filtroCategoria = 'Todos';
        },

        bordeTipo(tipo) {
            switch(tipo) {
                case 'Llanta': return 'bg-emerald-500';
                case 'Rin': return 'bg-blue-500';
                case 'Accesorio': return 'bg-amber-500';
                case 'Servicio': return 'bg-purple-500';
                default: return 'bg-gray-400';
            }
        },

        tipoBadgeClase(tipo) {
            switch(tipo) {
                case 'Llanta': return 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border-emerald-200';
                case 'Rin': return 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border-blue-200';
                case 'Accesorio': return 'bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 border-amber-200';
                case 'Servicio': return 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 border-purple-200';
                default: return 'bg-gray-50 text-gray-600';
            }
        },

        // Carrito
        agregar(producto) {
            const existe = this.carrito.find(item => item.id === producto.id);
            if (existe) {
                if (producto.tipo !== 'Servicio' && existe.cantidad >= producto.stock_cantidad) {
                    this.mostrarToast('No hay suficiente stock disponible', 'error');
                    return;
                }
                existe.cantidad++;
            } else {
                this.carrito.push({ ...producto, cantidad: 1 });
            }
            this.mostrarToast('Producto agregado al carrito', 'success');
        },

        aumentar(item) {
            const p = this.productos.find(prod => prod.id === item.id);
            if (p && p.tipo !== 'Servicio' && item.cantidad >= p.stock_cantidad) {
                this.mostrarToast('Límite de stock alcanzado', 'error');
                return;
            }
            item.cantidad++;
        },

        disminuir(item) {
            if (item.cantidad > 1) {
                item.cantidad--;
            } else {
                this.quitar(item);
            }
        },

        quitar(item) {
            this.carrito = this.carrito.filter(i => i.id !== item.id);
            this.mostrarToast('Producto removido', 'error');
        },

        limpiarCarrito() {
            this.carrito = [];
            this.mostrarToast('Carrito vaciado', 'error');
        },

        subtotal() {
            return this.carrito.reduce((sum, item) => sum + (item.cantidad * item.precio_publico), 0);
        },

        iva() {
            return this.subtotal() * 0.16;
        },

        total() {
            return this.subtotal() + this.iva();
        },

        procesarVenta() {
            if (this.carrito.length === 0) return;
            this.mostrarToast('¡Venta completada con éxito!', 'success');
            this.carrito = [];
        },

        mostrarToast(msg, type = 'success') {
            this.toastMsg = msg;
            this.toastType = type;
            this.toastVisible = true;
            setTimeout(() => {
                this.toastVisible = false;
            }, 3000);
        }
    }
}
</script>
@endsection