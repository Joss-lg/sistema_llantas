@extends('layouts.app')

@section('header_title', 'Punto de Venta')

@section('content')

{{-- Estilos para ocultar barras de desplazamiento --}}
<style>
    *::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
    * {
        -ms-overflow-style: none !important;  /* IE y Edge */
        scrollbar-width: none !important;  /* Firefox */
    }

    .color-scheme-dark { color-scheme: light; }
    html.dark .color-scheme-dark { color-scheme: dark; }
</style>

<div x-data="puntoVenta()" x-init="init()" class="relative">

    <div class="relative z-10 flex flex-col lg:flex-row gap-4 lg:gap-6 h-auto lg:h-[calc(100vh-120px)] pb-6 lg:pb-0 px-2 sm:px-0">

        {{-- TOAST DE NOTIFICACIONES --}}
        <div x-show="toastVisible" x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-4 -translate-x-1/2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 -translate-x-1/2 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 -translate-x-1/2 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-2 -translate-x-1/2 scale-95"
            class="fixed top-5 left-1/2 z-50 px-4 py-3 rounded-2xl shadow-xl text-sm font-semibold flex items-center gap-2.5 max-w-md border"
            :class="toastType === 'error' 
                ? 'bg-red-600 text-white border-red-500' 
                : (toastType === 'warning' 
                    ? 'bg-amber-500 text-white border-amber-400' 
                    : 'bg-gray-900 dark:bg-[#0A0A0A] text-white border-gray-800 dark:border-neutral-800')"
            role="alert">
            <svg x-show="toastType === 'success'" class="w-5 h-5 text-[#818CF8] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <svg x-show="toastType === 'error'" class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <svg x-show="toastType === 'warning'" class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span x-text="toastMsg"></span>
        </div>

        {{-- PANEL IZQUIERDO: CATÁLOGO DE PRODUCTOS --}}
        <div class="flex-1 flex flex-col bg-white dark:bg-[#151515] rounded-3xl border border-gray-200 dark:border-neutral-800 overflow-hidden shadow-lg dark:shadow-none h-[65vh] lg:h-full transition-all duration-500"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
            
            <div class="h-1.5 w-full bg-[#818CF8] shrink-0"></div>

            {{-- Encabezado y Filtros --}}
            <div class="px-4 sm:px-6 pt-5 pb-4 border-b border-gray-100 dark:border-neutral-800 space-y-4 shrink-0 transition-shadow duration-300"
                :class="scrolledProductos ? 'shadow-md dark:shadow-neutral-950/50' : ''">
                
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white tracking-tight">Productos y Servicios</h2>
                            <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-[#0A0A0A] border border-transparent dark:border-neutral-800 text-gray-500 dark:text-neutral-400 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-neutral-600"></span>
                                    <span x-text="productosFiltrados().length + ' de ' + productos.length"></span>
                                </span>
                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-lg bg-[#818CF8]/10 dark:bg-[#818CF8]/20 border border-transparent dark:border-[#818CF8]/30 text-[#818CF8] flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#818CF8]"></span>
                                    <span x-text="contarConStock() + ' con stock'"></span>
                                </span>
                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-lg bg-red-50 dark:bg-red-900/20 border border-transparent dark:border-red-900/40 text-red-600 dark:text-red-400 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    <span x-text="contarAgotados() + ' agotados'"></span>
                                </span>
                                <span x-show="contarNuevos() > 0" x-cloak class="text-[10px] font-bold px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-transparent dark:border-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span x-text="contarNuevos() + ' recién llegados'"></span>
                                </span>
                            </div>
                        </div>

                        @if($esAdmin)
                            {{-- Seleccionar Sucursal (Administrador) --}}
                            <div x-data="{ openSucursal: false }" @click.away="openSucursal = false" class="relative">
                                <div class="flex items-center gap-2 bg-amber-50 dark:bg-[#0A0A0A] border border-amber-200 dark:border-neutral-800 px-3 py-1.5 rounded-xl shadow-sm">
                                    <svg class="w-3.5 h-3.5 text-amber-700 dark:text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
                                    <span class="text-[10px] font-black text-amber-800 dark:text-amber-500 uppercase tracking-wider">Sucursal:</span>
                                    
                                    <button @click="openSucursal = !openSucursal" type="button" 
                                        class="text-xs font-bold bg-white dark:bg-[#151515] border border-amber-300 dark:border-neutral-700 rounded-lg px-2.5 py-1 text-gray-700 dark:text-white flex items-center gap-2 focus:outline-none cursor-pointer hover:bg-gray-50 dark:hover:bg-neutral-800">
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
                                        class="absolute top-full left-0 mt-2 w-52 bg-white dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl shadow-xl z-50 p-1 space-y-0.5">
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

                    <button @click="limpiarFiltros()" x-show="hayFiltrosActivos()" x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-90"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-90"
                        class="text-xs text-red-500 dark:text-red-400 border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-900/10 px-3 py-1.5 rounded-lg font-semibold hover:bg-red-100 dark:hover:bg-red-900/30 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <span>Limpiar filtros</span>
                    </button>
                </div>

                {{-- Campo de Búsqueda --}}
                <div class="relative">
                    <input x-model="busqueda" type="text" placeholder="Buscar por medida, marca o descripción..."
                        class="w-full pl-11 pr-10 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-neutral-600 focus:outline-none focus:ring-2 focus:ring-[#818CF8] focus:border-transparent transition-all">
                    <svg class="absolute left-3.5 top-3 w-4 h-4 transition-colors" :class="busqueda ? 'text-[#818CF8]' : 'text-gray-400 dark:text-neutral-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button x-show="busqueda" @click="busqueda = ''" x-cloak type="button"
                        class="absolute right-3 top-2.5 w-5 h-5 rounded-full bg-gray-200 dark:bg-neutral-800 hover:bg-gray-300 dark:hover:bg-neutral-700 flex items-center justify-center text-gray-500 dark:text-neutral-400 transition-all">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Filtros Avanzados --}}
                <div class="space-y-2.5 pt-1">
                    <div class="flex items-center gap-4 flex-wrap">
                        {{-- Filtro Marca --}}
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-wider shrink-0">Marca</span>
                            <div x-data="{ openMarca: false }" @click.away="openMarca = false" class="relative min-w-[160px]">
                                <button @click="openMarca = !openMarca" type="button"
                                    class="w-full flex items-center justify-between px-3 py-1.5 rounded-xl text-xs font-semibold border border-gray-200 dark:border-neutral-800 bg-white dark:bg-[#0A0A0A] text-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#818CF8] cursor-pointer hover:bg-gray-50 dark:hover:bg-neutral-800">
                                    <span class="truncate" x-text="filtroMarca ? filtroMarca + ' (' + contarMarca(filtroMarca) + ')' : 'Todas las marcas'"></span>
                                    <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200" :class="openMarca ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div x-show="openMarca" x-cloak
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute left-0 mt-1 w-full max-h-56 overflow-y-auto rounded-xl bg-white dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 shadow-xl z-50 p-1 space-y-0.5">
                                    <button @click="filtroMarca = ''; openMarca = false" type="button"
                                        class="w-full text-left px-3 py-1.5 text-xs rounded-lg transition-colors flex items-center justify-between"
                                        :class="filtroMarca === '' ? 'bg-[#818CF8]/10 text-[#818CF8] font-bold' : 'text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-800'">
                                        <span>Todas las marcas</span>
                                    </button>
                                    <template x-for="m in marcas" :key="m">
                                        <button @click="filtroMarca = m; openMarca = false" type="button"
                                            class="w-full text-left px-3 py-1.5 text-xs rounded-lg transition-colors flex items-center justify-between"
                                            :class="filtroMarca === m ? 'bg-[#818CF8]/10 text-[#818CF8] font-bold' : 'text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-800'">
                                            <span x-text="m" class="truncate pr-2"></span>
                                            <span class="text-[10px] text-gray-400 dark:text-neutral-500 font-normal shrink-0" x-text="'(' + contarMarca(m) + ')'"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Filtro Stock --}}
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-wider shrink-0">Stock</span>
                            <div class="flex gap-1 overflow-x-auto">
                                <button @click="filtroStock = ''" type="button" class="px-2.5 py-1 rounded-full text-[11px] font-semibold border transition-all"
                                    :class="filtroStock === '' ? 'bg-gray-800 dark:bg-neutral-700 text-white border-gray-800 dark:border-neutral-600' : 'bg-white dark:bg-[#0A0A0A] text-gray-600 dark:text-neutral-400 border-gray-200 dark:border-neutral-800'">
                                    Todos
                                </button>
                                <button @click="filtroStock = 'disponible'" type="button" class="px-2.5 py-1 rounded-full text-[11px] font-semibold border transition-all"
                                    :class="filtroStock === 'disponible' ? 'bg-[#818CF8] text-white border-[#818CF8]' : 'bg-white dark:bg-[#0A0A0A] text-gray-600 dark:text-neutral-400 border-gray-200 dark:border-neutral-800'">
                                    Con stock
                                </button>
                                <button @click="filtroStock = 'agotado'" type="button" class="px-2.5 py-1 rounded-full text-[11px] font-semibold border transition-all"
                                    :class="filtroStock === 'agotado' ? 'bg-[#D32030] text-white border-[#D32030]' : 'bg-white dark:bg-[#0A0A0A] text-gray-600 dark:text-neutral-400 border-gray-200 dark:border-neutral-800'">
                                    Agotado
                                </button>
                            </div>
                        </div>

                        {{-- Filtro Recién Llegado --}}
                        <div class="flex items-center gap-2">
                            <button @click="filtroNuevo = !filtroNuevo" type="button"
                                class="px-2.5 py-1 rounded-full text-[11px] font-semibold border transition-all inline-flex items-center gap-1.5"
                                :class="filtroNuevo ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white dark:bg-[#0A0A0A] text-gray-600 dark:text-neutral-400 border-gray-200 dark:border-neutral-800'">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 16a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 7.323V16h2a1 1 0 110 2H7a1 1 0 110-2h2V7.323L6.237 8.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 16a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.616a1 1 0 01.894-1.79l1.599.8L9 4.323V3a1 1 0 011-1z"/></svg>
                                <span>Recién llegado</span>
                                <span x-show="contarNuevos() > 0" x-cloak
                                    class="px-1.5 py-0.5 rounded-full text-[10px] font-extrabold"
                                    :class="filtroNuevo ? 'bg-white text-emerald-600' : 'bg-emerald-500 text-white'"
                                    x-text="contarNuevos()"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Filtro Categoría --}}
                    <div class="flex items-center gap-2 w-full pt-2 border-t border-gray-100 dark:border-neutral-800">
                        <span class="text-[10px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-wider shrink-0">Categoría</span>
                        <div class="flex gap-1.5 overflow-x-auto">
                            <template x-for="cat in ['Todos', 'Llanta', 'Rin', 'Accesorio', 'Servicio']" :key="cat">
                                <button @click="filtroCategoria = cat" type="button" class="px-3 py-1 rounded-full text-[11px] font-bold border transition-all shrink-0 flex items-center gap-1.5"
                                    :class="filtroCategoria === cat ? 'bg-[#818CF8] text-white border-[#818CF8]' : 'bg-white dark:bg-[#0A0A0A] text-gray-600 dark:text-neutral-400 border-gray-200 dark:border-neutral-800 hover:border-[#818CF8]'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="cat === 'Todos' ? (filtroCategoria === cat ? 'bg-white' : 'bg-gray-300 dark:bg-neutral-600') : bordeTipo(cat)"></span>
                                    <span x-text="cat"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Grid de Productos --}}
            <div class="flex-1 overflow-y-auto p-4 sm:p-5" @scroll.passive="scrolledProductos = $event.target.scrollTop > 4">
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                    <template x-for="p in productosFiltrados()" :key="p.id">
                        <button @click="agregar(p)" :disabled="p.tipo !== 'Servicio' && p.stock_cantidad <= 0" type="button"
                            class="group relative flex flex-col justify-between text-left p-4 pl-5 rounded-2xl border transition-all duration-200 overflow-hidden"
                            :class="[
                                (p.tipo === 'Servicio' || p.stock_cantidad > 0)
                                    ? 'border-gray-200 dark:border-neutral-800 bg-white dark:bg-[#0A0A0A] hover:border-[#818CF8] dark:hover:border-[#818CF8] cursor-pointer'
                                    : 'border-gray-100 dark:border-neutral-800/50 bg-gray-50 dark:bg-neutral-900/30 opacity-50 cursor-not-allowed'
                            ]">

                            {{-- Barra lateral de color según tipo --}}
                            <span class="absolute left-0 top-3 bottom-3 w-1 rounded-r-full transition-colors" :class="bordeTipo(p.tipo)"></span>

                            <div>
                                <div class="flex items-start justify-between mb-2 gap-1.5 flex-wrap">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider border dark:border-transparent"
                                        :class="tipoBadgeClase(p.tipo)" x-text="p.tipo"></span>

                                    <template x-if="p.es_nuevo">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 inline-flex items-center gap-1 border dark:border-transparent">
                                            Nuevo
                                        </span>
                                    </template>

                                    <template x-if="p.tipo !== 'Servicio'">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full inline-flex items-center gap-1 border dark:border-transparent"
                                            :class="p.stock_cantidad > 5 ? 'bg-[#818CF8]/10 dark:bg-[#818CF8]/20 text-[#818CF8]' : p.stock_cantidad > 0 ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : 'bg-red-100 dark:bg-red-900/30 text-[#D32030] dark:text-red-400'">
                                            <span x-text="p.stock_cantidad > 0 ? 'Stock: ' + p.stock_cantidad : 'Agotado'"></span>
                                        </span>
                                    </template>
                                </div>

                                <div class="font-bold text-sm text-gray-800 dark:text-white leading-snug" x-text="p.tipo === 'Servicio' ? p.descripcion : p.marca"></div>

                                <template x-if="p.tipo !== 'Servicio'">
                                    <div class="text-xs text-gray-500 dark:text-neutral-400 font-mono mt-0.5" x-text="p.medida"></div>
                                </template>
                            </div>

                            <div class="w-full mt-3 pt-2.5 border-t border-gray-100 dark:border-neutral-800 flex items-center justify-between">
                                <div class="text-base font-black text-[#818CF8] tabular-nums" x-text="'$' + (+p.precio_publico).toLocaleString('es-MX', {minimumFractionDigits:2})"></div>
                                <div class="w-7 h-7 rounded-lg bg-[#818CF8]/10 text-[#818CF8] flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>

                {{-- Estado Vacío --}}
                <div x-show="productosFiltrados().length === 0" x-cloak class="text-center text-gray-400 dark:text-neutral-500 py-16 text-sm">
                    No se encontraron productos ni servicios con los filtros aplicados.
                </div>
            </div>
        </div>

        {{-- PANEL DERECHO: CARRITO / CHECKOUT --}}
        <div class="w-full lg:w-[380px] xl:w-[420px] flex flex-col bg-white dark:bg-[#151515] rounded-3xl border border-gray-200 dark:border-neutral-800 shadow-lg overflow-hidden h-full shrink-0">
            
            {{-- Encabezado del Carrito --}}
            <div class="p-4 border-b border-gray-100 dark:border-neutral-800 flex items-center justify-between bg-gray-50/50 dark:bg-[#0A0A0A]/50">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-[#818CF8]/10 text-[#818CF8]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 2 0 100 4 2 2 0 000-4z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white">Carrito de Compra</h3>
                        <p class="text-xs text-gray-400 dark:text-neutral-500" x-text="totalItems() + ' artículos seleccionados'"></p>
                    </div>
                </div>

                <button @click="limpiarCarrito()" x-show="carrito.length > 0" x-cloak type="button"
                    class="text-xs text-red-500 hover:text-red-600 font-semibold px-2.5 py-1 rounded-lg border border-red-200 dark:border-red-900/40 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                    Vaciar
                </button>
            </div>

            {{-- Lista de Items en el Carrito --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-2.5">
                <template x-for="item in carrito" :key="item.id">
                    <div class="flex items-center gap-2.5 p-3 rounded-2xl bg-gray-50 dark:bg-[#0A0A0A] border border-gray-100 dark:border-neutral-800">
                        
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-bold text-gray-800 dark:text-white truncate" x-text="item.tipo === 'Servicio' ? item.descripcion : item.marca + ' - ' + item.medida"></h4>
                            <div class="text-[11px] text-gray-500 dark:text-neutral-400" x-text="'$' + (+item.precio_publico).toLocaleString('es-MX', {minimumFractionDigits:2}) + ' c/u'"></div>
                        </div>

                        {{-- Controles de Cantidad --}}
                        <div class="flex items-center gap-1 bg-white dark:bg-[#151515] border border-gray-200 dark:border-neutral-800 rounded-xl p-1">
                            <button @click="disminuir(item)" type="button" class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-neutral-800 hover:bg-gray-200 dark:hover:bg-neutral-700 flex items-center justify-center text-gray-600 dark:text-neutral-300 font-bold transition-colors">
                                -
                            </button>
                            <span class="text-xs font-bold w-6 text-center text-gray-800 dark:text-white" x-text="item.cantidad"></span>
                            <button @click="aumentar(item)" type="button" class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-neutral-800 hover:bg-gray-200 dark:hover:bg-neutral-700 flex items-center justify-center text-gray-600 dark:text-neutral-300 font-bold transition-colors">
                                +
                            </button>
                        </div>

                        {{-- Subtotal por Item --}}
                        <div class="text-right min-w-[65px]">
                            <span class="text-xs font-black text-gray-900 dark:text-white" x-text="'$' + (item.cantidad * item.precio_publico).toLocaleString('es-MX', {minimumFractionDigits:2})"></span>
                        </div>

                        {{-- Eliminar Item --}}
                        <button @click="quitar(item)" type="button" class="text-gray-400 hover:text-red-500 transition-colors p-1" title="Quitar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>

                {{-- Estado Vacío --}}
                <div x-show="carrito.length === 0" class="h-full min-h-[180px] flex flex-col items-center justify-center text-center p-6 text-gray-400 dark:text-neutral-600 space-y-2">
                    <svg class="w-10 h-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <p class="text-xs font-medium">El carrito está vacío</p>
                </div>
            </div>

            {{-- Resumen de Cobro y Datos de Cliente --}}
            <div class="p-4 border-t border-gray-100 dark:border-neutral-800 bg-gray-50/50 dark:bg-[#0A0A0A]/50 space-y-3 shrink-0">
                
                {{-- Nombre del cliente --}}
                <div>
                    <label for="input_cliente" class="block text-[10px] font-bold text-gray-500 dark:text-neutral-400 uppercase tracking-wider mb-1">Cliente (Opcional)</label>
                    <input id="input_cliente" x-model="cliente" type="text" placeholder="Público General"
                        class="w-full px-3 py-1.5 bg-white dark:bg-[#151515] border border-gray-200 dark:border-neutral-800 rounded-xl text-xs text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#818CF8]">
                </div>

                {{-- Factura & Métodos de Pago --}}
                <div class="flex items-center justify-between gap-3 pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" x-model="requiereFactura" class="rounded border-gray-300 dark:border-neutral-700 text-[#818CF8] focus:ring-[#818CF8] w-4 h-4">
                        <span class="text-xs font-bold text-gray-700 dark:text-neutral-300">¿Requiere Factura?</span>
                    </label>

                    <div class="flex gap-1">
                        <template x-for="metodo in ['efectivo', 'tarjeta', 'transferencia']" :key="metodo">
                            <button @click="metodoPago = metodo" type="button" 
                                class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider border transition-all"
                                :class="metodoPago === metodo ? 'bg-[#818CF8] text-white border-[#818CF8]' : 'bg-white dark:bg-[#151515] text-gray-600 dark:text-neutral-400 border-gray-200 dark:border-neutral-800'">
                                <span x-text="metodo.substr(0,4)"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Cálculo de Totales --}}
                <div class="space-y-1.5 text-xs pt-2 border-t border-gray-200/60 dark:border-neutral-800">
                    <div class="flex justify-between text-gray-500 dark:text-neutral-400">
                        <span>Subtotal</span>
                        <span class="font-bold text-gray-800 dark:text-white" x-text="'$' + subtotal().toLocaleString('es-MX', {minimumFractionDigits:2})"></span>
                    </div>

                    <template x-if="requiereFactura">
                        <div class="flex justify-between text-gray-500 dark:text-neutral-400">
                            <span>IVA (16%)</span>
                            <span class="font-bold text-gray-800 dark:text-white" x-text="'$' + iva().toLocaleString('es-MX', {minimumFractionDigits:2})"></span>
                        </div>
                    </template>

                    <div class="flex justify-between items-center text-base font-black text-gray-900 dark:text-white pt-1">
                        <span>Total</span>
                        <span class="text-xl text-[#818CF8]" x-text="'$' + total().toLocaleString('es-MX', {minimumFractionDigits:2})"></span>
                    </div>
                </div>

                {{-- Botón Registrar Venta --}}
                <button @click="procesarVenta()" :disabled="carrito.length === 0 || procesando" type="button"
                    class="w-full py-3 bg-[#818CF8] hover:bg-[#6366F1] disabled:bg-gray-300 dark:disabled:bg-neutral-800 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                    <svg x-show="!procesando" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg x-show="procesando" x-cloak class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span x-text="procesando ? 'Procesando Venta...' : 'Completar Venta'"></span>
                </button>
            </div>
        </div>

    </div>
</div>

{{-- SCRIPT ALPINE.JS CON LÓGICA REACTIVA --}}
<script>
    function puntoVenta() {
        return {
            cargado: false,
            procesando: false,
            scrolledProductos: false,
            
            // Datos del Servidor (o fallback)
            productos: @json($productos ?? []),
            sucursales: @json($sucursales ?? []),
            sucursalSeleccionada: @json($sucursalSeleccionada ?? 1),

            // Filtros
            busqueda: '',
            filtroMarca: '',
            filtroStock: '',
            filtroCategoria: 'Todos',
            filtroUso: '',
            filtroNuevo: false,

            // Carrito
            carrito: [],
            cliente: '',
            requiereFactura: false,
            metodoPago: 'efectivo',

            // Toast
            toastVisible: false,
            toastMsg: '',
            toastType: 'success',

            init() {
                setTimeout(() => { this.cargado = true; }, 100);
            },

            marcas() {
                return [...new Set(this.productos.map(p => p.marca).filter(Boolean))].sort();
            },

            contarMarca(marca) {
                return this.productos.filter(p => p.marca === marca).length;
            },

            contarConStock() {
                return this.productos.filter(p => p.tipo === 'Servicio' || p.stock_cantidad > 0).length;
            },

            contarAgotados() {
                return this.productos.filter(p => p.tipo !== 'Servicio' && p.stock_cantidad <= 0).length;
            },

            contarNuevos() {
                return this.productos.filter(p => p.es_nuevo).length;
            },

            productosFiltrados() {
                return this.productos.filter(p => {
                    const matchBusqueda = !this.busqueda || 
                        (p.marca && p.marca.toLowerCase().includes(this.busqueda.toLowerCase())) ||
                        (p.medida && p.medida.toLowerCase().includes(this.busqueda.toLowerCase())) ||
                        (p.descripcion && p.descripcion.toLowerCase().includes(this.busqueda.toLowerCase()));

                    const matchMarca = !this.filtroMarca || p.marca === this.filtroMarca;
                    const matchCategoria = this.filtroCategoria === 'Todos' || p.tipo === this.filtroCategoria;
                    const matchNuevo = !this.filtroNuevo || p.es_nuevo;
                    
                    let matchStock = true;
                    if (this.filtroStock === 'disponible') matchStock = p.tipo === 'Servicio' || p.stock_cantidad > 0;
                    if (this.filtroStock === 'agotado') matchStock = p.tipo !== 'Servicio' && p.stock_cantidad <= 0;

                    return matchBusqueda && matchMarca && matchCategoria && matchStock && matchNuevo;
                });
            },

            hayFiltrosActivos() {
                return this.busqueda !== '' || this.filtroMarca !== '' || this.filtroStock !== '' || this.filtroCategoria !== 'Todos' || this.filtroNuevo;
            },

            limpiarFiltros() {
                this.busqueda = '';
                this.filtroMarca = '';
                this.filtroStock = '';
                this.filtroCategoria = 'Todos';
                this.filtroNuevo = false;
            },

            bordeTipo(tipo) {
                switch(tipo) {
                    case 'Llanta': return 'bg-[#818CF8]';
                    case 'Rin': return 'bg-amber-500';
                    case 'Accesorio': return 'bg-purple-500';
                    case 'Servicio': return 'bg-[#818CF8]';
                    default: return 'bg-gray-400';
                }
            },

            tipoBadgeClase(tipo) {
                switch(tipo) {
                    case 'Llanta': return 'bg-[#818CF8]/10 text-[#818CF8] dark:bg-[#818CF8]/20';
                    case 'Rin': return 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
                    case 'Accesorio': return 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300';
                    case 'Servicio': return 'bg-[#818CF8]/10 text-[#818CF8] dark:bg-[#818CF8]/20';
                    default: return 'bg-gray-50 text-gray-700 dark:bg-neutral-800 dark:text-neutral-300';
                }
            },

            agregar(producto) {
                let item = this.carrito.find(i => i.id === producto.id);
                if (item) {
                    if (producto.tipo !== 'Servicio' && item.cantidad >= producto.stock_cantidad) {
                        this.showToast('Stock máximo alcanzado para este producto', 'warning');
                        return;
                    }
                    item.cantidad++;
                } else {
                    this.carrito.push({ ...producto, cantidad: 1 });
                }
                this.showToast('Producto agregado al carrito', 'success');
            },

            aumentar(item) {
                let prod = this.productos.find(p => p.id === item.id);
                if (prod && prod.tipo !== 'Servicio' && item.cantidad >= prod.stock_cantidad) {
                    this.showToast('No hay suficiente stock disponible', 'warning');
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
            },

            limpiarCarrito() {
                this.carrito = [];
            },

            totalItems() {
                return this.carrito.reduce((sum, i) => sum + i.cantidad, 0);
            },

            subtotal() {
                return this.carrito.reduce((sum, i) => sum + (i.cantidad * i.precio_publico), 0);
            },

            iva() {
                return this.requiereFactura ? this.subtotal() * 0.16 : 0;
            },

            total() {
                return this.subtotal() + this.iva();
            },

            showToast(msg, type = 'success') {
                this.toastMsg = msg;
                this.toastType = type;
                this.toastVisible = true;
                setTimeout(() => { this.toastVisible = false; }, 3000);
            },

            procesarVenta() {
                if (this.carrito.length === 0) return;
                this.procesando = true;

                // 1. Formateamos el carrito para que coincida EXACTAMENTE con lo que espera el Controlador
                const carritoFormateado = this.carrito.map(item => {
                    return {
                        producto_id: item.id,
                        nombre: item.tipo === 'Servicio' ? item.descripcion : (item.marca + ' ' + (item.medida || '')).trim(),
                        cantidad: item.cantidad,
                        precio_unitario: item.precio_publico,
                        subtotal: item.cantidad * item.precio_publico
                    };
                });

                // 2. Aseguramos la capitalización para el Método de Pago ('Efectivo', 'Tarjeta', 'Transferencia')
                // Esto es vital para que sume correctamente en el historial de la Caja
                const metodoPagoCapitalizado = this.metodoPago.charAt(0).toUpperCase() + this.metodoPago.slice(1);

                fetch('{{ route("ventas.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    // 3. Enviamos los nombres de variables exactos que Laravel está leyendo en el Backend
                    body: JSON.stringify({
                        sucursal_id: this.sucursalSeleccionada,
                        cliente: this.cliente,                  // El backend lee 'cliente'
                        requiereFactura: this.requiereFactura,  // El backend lee 'requiereFactura'
                        pagoCon: metodoPagoCapitalizado,        // El backend lee 'pagoCon'
                        cambio: 0,
                        carrito: carritoFormateado,             // El backend lee 'carrito'
                        total: this.total()
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.procesando = false;
                    if (data.success) {
                        this.showToast('Venta realizada con éxito', 'success');
                        this.limpiarCarrito();
                        if (data.ticket_url) {
                            window.open(data.ticket_url, 'Ticket', 'width=400,height=600');
                        }
                    } else {
                        this.showToast(data.message || 'Error al procesar la venta', 'error');
                    }
                })
                .catch(err => {
                    this.procesando = false;
                    this.showToast('Error de conexión con el servidor', 'error');
                });
            }
        };
    }
</script>
@endsection