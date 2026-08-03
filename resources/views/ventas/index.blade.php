@extends('layouts.app')

@section('header_title', 'Punto de Venta')

@section('content')
<div x-data="puntoVenta()" class="flex flex-col lg:flex-row gap-4 lg:gap-6 h-auto lg:h-[calc(100vh-130px)] pb-6 lg:pb-0">

    {{-- TOAST DE NOTIFICACIONES --}}
    <div x-show="toastVisible" x-cloak
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 -translate-y-3 -translate-x-1/2"
        x-transition:enter-end="opacity-100 translate-y-0 -translate-x-1/2"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 -translate-x-1/2"
        x-transition:leave-end="opacity-0 -translate-y-1 -translate-x-1/2"
        class="fixed top-5 left-1/2 z-50 px-4 py-2.5 rounded-xl shadow-lg shadow-black/10 dark:shadow-black/40 text-sm font-semibold flex items-center gap-2 max-w-sm"
        :class="toastType === 'error' ? 'bg-red-500 text-white' : 'bg-gray-900 dark:bg-zinc-700 text-white'">
        <svg x-show="toastType !== 'error'" class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <svg x-show="toastType === 'error'" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span x-text="toastMsg"></span>
    </div>

    <div class="panel-in panel-glow flex-1 flex flex-col bg-white dark:bg-zinc-950 rounded-3xl border border-gray-200 dark:border-white/[0.06] overflow-hidden shadow-sm dark:shadow-none h-[65vh] lg:h-full">
        <div class="h-1 w-full bg-gradient-to-r from-orange-400 via-emerald-400 to-violet-400 bg-[length:200%_100%] animate-gradient-flow shrink-0"></div>

        <div class="px-4 sm:px-6 pt-5 pb-4 border-b border-gray-100 dark:border-zinc-800 space-y-4 shrink-0 transition-shadow duration-300"
            :class="scrolledProductos ? 'shadow-[0_6px_12px_-8px_rgba(0,0,0,0.15)] dark:shadow-[0_6px_12px_-8px_rgba(0,0,0,0.5)]' : ''">
            <div class="flex items-center justify-between">
                <div class="flex flex-col sm:flex-row sm:items-start gap-2 sm:gap-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-zinc-100">Productos y Servicios</h2>
                        <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                            <span class="text-[10px] font-bold px-2 py-1 rounded-lg bg-gray-100 dark:bg-zinc-800 text-gray-500 dark:text-zinc-400 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-zinc-500"></span>
                                <span x-text="productosFiltrados().length + ' de ' + productos.length"></span>
                            </span>
                            <span class="text-[10px] font-bold px-2 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span x-text="contarConStock() + ' con stock'"></span>
                            </span>
                            <span class="text-[10px] font-bold px-2 py-1 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                <span x-text="contarAgotados() + ' agotados'"></span>
                            </span>
                        </div>
                    </div>

                    @if($esAdmin)
                        <div class="flex items-center gap-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-3 py-1.5 rounded-xl shadow-sm hover:shadow transition-all duration-300"
                            :class="sucursalFlash ? 'ring-2 ring-amber-400 ring-offset-1 dark:ring-offset-zinc-900' : ''">
                            <svg class="w-3.5 h-3.5 text-amber-700 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
                            <span class="text-[10px] font-black text-amber-800 dark:text-amber-400 uppercase tracking-wider">Sucursal Activa:</span>
                            <select x-model="sucursalSeleccionada"
                                class="text-xs font-bold bg-white dark:bg-zinc-800 border border-amber-300 dark:border-amber-700 rounded-lg p-1 text-gray-700 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-amber-500 cursor-pointer transition-shadow">
                                @foreach($sucursales as $suc)
                                    <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
                <div>
                    <button @click="limpiarFiltros()" x-show="hayFiltrosActivos()" x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-90"
                        x-transition:enter-end="opacity-100 scale-100"
                        class="text-xs text-red-500 dark:text-red-400 border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-3 py-2 rounded-lg font-medium hover:bg-red-100 dark:hover:bg-red-900/40 active:scale-95 transition flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <span class="hidden sm:inline">Limpiar filtros</span>
                        <span class="sm:hidden">Limpiar</span>
                    </button>
                </div>
            </div>

            <div class="relative">
                <input x-model="busqueda" type="text" placeholder="Buscar por medida o marca..."
                    class="w-full pl-11 pr-10 py-3 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-sm text-gray-800 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent focus:bg-white dark:focus:bg-zinc-800 transition-all">
                <svg class="absolute left-4 top-3.5 w-4 h-4 transition-colors" :class="busqueda ? 'text-emerald-500' : 'text-gray-400 dark:text-zinc-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <button x-show="busqueda" @click="busqueda = ''" x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-75"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="absolute right-3.5 top-3 w-6 h-6 rounded-full bg-gray-200 dark:bg-zinc-700 hover:bg-gray-300 dark:hover:bg-zinc-600 active:scale-90 flex items-center justify-center text-gray-500 dark:text-zinc-300 transition-all">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-3">
                <div class="flex items-center gap-6 flex-wrap">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-wider shrink-0">Marca</span>
                        <select x-model="filtroMarca"
                            class="px-3 py-1.5 rounded-lg text-sm border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-700 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-emerald-400 cursor-pointer min-w-[160px] transition-shadow">
                            <option value="">Todas las marcas</option>
                            <template x-for="m in marcas" :key="m">
                                <option :value="m" x-text="m + ' (' + contarMarca(m) + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-wider shrink-0">Uso</span>
                        <div class="flex gap-1.5 overflow-x-auto scrollbar-hide">
                            <button @click="filtroUso = ''" class="px-3 py-1.5 rounded-full text-[11px] font-medium border transition-all duration-200 active:scale-95 shrink-0"
                                :class="filtroUso === '' ? 'bg-blue-500 text-white border-blue-500 shadow-sm shadow-blue-200 dark:shadow-blue-900/40' : 'bg-white dark:bg-zinc-800 text-gray-500 dark:text-zinc-400 border-gray-200 dark:border-zinc-700 hover:border-blue-300 dark:hover:border-blue-500/50'">
                                Todos
                            </button>
                            <template x-for="u in usos" :key="u">
                                <button @click="filtroUso = u" class="px-3 py-1.5 rounded-full text-[11px] font-medium border transition-all duration-200 active:scale-95 shrink-0"
                                    :class="filtroUso === u ? 'bg-blue-500 text-white border-blue-500 shadow-sm shadow-blue-200 dark:shadow-blue-900/40' : 'bg-white dark:bg-zinc-800 text-gray-500 dark:text-zinc-400 border-gray-200 dark:border-zinc-700 hover:border-blue-300 dark:hover:border-blue-500/50'"
                                    x-text="u">
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-6 flex-wrap">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-wider shrink-0">Stock</span>
                        <div class="flex gap-1.5 overflow-x-auto scrollbar-hide">
                            <button @click="filtroStock = ''" class="px-3 py-1.5 rounded-full text-[11px] font-medium border transition-all duration-200 active:scale-95 shrink-0 flex items-center gap-1"
                                :class="filtroStock === '' ? 'bg-gray-800 dark:bg-zinc-600 text-white border-gray-800 dark:border-zinc-500 shadow-sm' : 'bg-white dark:bg-zinc-800 text-gray-500 dark:text-zinc-400 border-gray-200 dark:border-zinc-700 hover:border-gray-400 dark:hover:border-zinc-500'">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                Todos
                            </button>
                            <button @click="filtroStock = 'disponible'" class="px-3 py-1.5 rounded-full text-[11px] font-medium border transition-all duration-200 active:scale-95 shrink-0 flex items-center gap-1"
                                :class="filtroStock === 'disponible' ? 'bg-emerald-500 text-white border-emerald-500 shadow-sm shadow-emerald-200 dark:shadow-emerald-900/40' : 'bg-white dark:bg-zinc-800 text-gray-500 dark:text-zinc-400 border-gray-200 dark:border-zinc-700 hover:border-emerald-300 dark:hover:border-emerald-500/50'">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Con stock
                            </button>
                            <button @click="filtroStock = 'poco'" class="px-3 py-1.5 rounded-full text-[11px] font-medium border transition-all duration-200 active:scale-95 shrink-0 flex items-center gap-1"
                                :class="filtroStock === 'poco' ? 'bg-amber-500 text-white border-amber-500 shadow-sm shadow-amber-200 dark:shadow-amber-900/40' : 'bg-white dark:bg-zinc-800 text-gray-500 dark:text-zinc-400 border-gray-200 dark:border-zinc-700 hover:border-amber-300 dark:hover:border-amber-500/50'">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-1.5a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                                Poco
                            </button>
                            <button @click="filtroStock = 'agotado'" class="px-3 py-1.5 rounded-full text-[11px] font-medium border transition-all duration-200 active:scale-95 shrink-0 flex items-center gap-1"
                                :class="filtroStock === 'agotado' ? 'bg-red-500 text-white border-red-500 shadow-sm shadow-red-200 dark:shadow-red-900/40' : 'bg-white dark:bg-zinc-800 text-gray-500 dark:text-zinc-400 border-gray-200 dark:border-zinc-700 hover:border-red-300 dark:hover:border-red-500/50'">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Agotado
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-wider shrink-0">Precio</span>
                        <select x-model="filtroPrecio"
                            class="px-3 py-1.5 rounded-lg text-sm border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-700 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-emerald-400 cursor-pointer min-w-[140px] transition-shadow">
                            <option value="">Todos los precios</option>
                            <template x-for="r in rangosPrecios" :key="r.label">
                                <option :value="r.label" x-text="r.label"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2 w-full pt-2 border-t border-gray-50 dark:border-zinc-800">
                    <span class="text-[10px] font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-wider shrink-0">Categoría</span>
                    <div class="flex gap-1.5 overflow-x-auto scrollbar-hide">
                        <template x-for="cat in ['Todos', 'Llanta', 'Rin', 'Accesorio', 'Servicio']" :key="cat">
                            <button @click="filtroCategoria = cat" class="px-4 py-1.5 rounded-full text-[11px] font-bold border transition-all duration-200 active:scale-95 shrink-0 flex items-center gap-1.5"
                                :class="filtroCategoria === cat ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm shadow-indigo-200 dark:shadow-indigo-900/40' : 'bg-white dark:bg-zinc-800 text-gray-500 dark:text-zinc-400 border-gray-200 dark:border-zinc-700 hover:border-indigo-300 dark:hover:border-indigo-500/50'">
                                <span class="w-1.5 h-1.5 rounded-full" :class="cat === 'Todos' ? (filtroCategoria === cat ? 'bg-white' : 'bg-gray-300 dark:bg-zinc-600') : bordeTipo(cat)"></span>
                                <span x-text="cat"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 sm:p-5 pos-grid-bg"
            @scroll.passive="scrolledProductos = $event.target.scrollTop > 4">
            <div class="grid grid-cols-1 sm:grid-cols-2 2xl:grid-cols-3 gap-4 sm:gap-5">
                <template x-for="(p, index) in productosFiltrados()" :key="p.id">
                    <button @click="agregar(p)" :disabled="p.tipo !== 'Servicio' && p.stock_cantidad <= 0"
                        class="animate-card-in group relative flex flex-col text-left p-5 pl-6 rounded-2xl border transition-all duration-200 h-full overflow-hidden"
                        :style="'animation-delay:' + (Math.min(index, 14) * 30) + 'ms'"
                        :class="[
                            (p.tipo === 'Servicio' || p.stock_cantidad > 0)
                                ? 'border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/60 hover:border-emerald-300 dark:hover:border-emerald-600 hover:bg-emerald-50/40 dark:hover:bg-emerald-900/10 hover:shadow-lg hover:shadow-emerald-900/5 hover:-translate-y-0.5 active:scale-[0.98] active:translate-y-0 cursor-pointer'
                                : 'border-gray-100 dark:border-zinc-800 bg-gray-50 dark:bg-zinc-900/40 opacity-50 cursor-not-allowed',
                            ultimoAgregadoId === p.id ? 'ring-2 ring-emerald-400 ring-offset-2 dark:ring-offset-zinc-900' : ''
                        ]">

                        <span class="absolute left-0 top-3 bottom-3 w-1 rounded-full transition-colors" :class="bordeTipo(p.tipo)"></span>

                        <div class="w-full">
                            <div class="flex items-start justify-between mb-3">
                                <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider"
                                    :class="tipoBadgeClase(p.tipo)"
                                    x-text="p.tipo"></span>

                                <template x-if="p.tipo !== 'Servicio'">
                                    <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full inline-flex items-center gap-1"
                                        :class="p.stock_cantidad > 5 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : p.stock_cantidad > 0 ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'">
                                        <span x-show="p.stock_cantidad > 0 && p.stock_cantidad <= 5" class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span x-text="p.stock_cantidad > 0 ? 'Stock: ' + p.stock_cantidad : 'Agotado'"></span>
                                    </span>
                                </template>
                            </div>

                            <div class="font-bold text-base text-gray-800 dark:text-zinc-100 leading-tight mb-1" x-text="p.tipo === 'Servicio' ? p.descripcion : p.marca"></div>

                            <template x-if="p.tipo !== 'Servicio'">
                                <div class="text-xs text-gray-500 dark:text-zinc-400 font-mono" x-text="p.medida"></div>
                            </template>
                            <template x-if="p.tipo === 'Servicio'">
                                <div class="text-xs text-gray-500 dark:text-zinc-400 truncate" x-text="p.descripcion"></div>
                            </template>
                        </div>

                        <div class="w-full mt-4 pt-3 border-t border-gray-50 dark:border-zinc-700/60 flex items-center justify-between">
                            <div class="text-lg font-black text-emerald-600 dark:text-emerald-400 tabular-nums transition-transform group-hover:scale-105" x-text="'$' + (+p.precio_publico).toLocaleString()"></div>
                            <svg class="w-4 h-4 text-emerald-500 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                    </button>
                </template>
            </div>

            <div x-show="productosFiltrados().length === 0" x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                class="text-center text-gray-400 dark:text-zinc-500 py-20 text-sm">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-200 dark:text-zinc-700 animate-bounce-gentle" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                No se encontraron registros con los filtros actuales.
            </div>
        </div>
    </div>

    <div class="[--panel-bg:#fff] dark:[--panel-bg:#09090b] panel-in panel-glow w-full lg:w-[380px] xl:w-[420px] flex flex-col bg-white dark:bg-zinc-950 rounded-3xl border border-gray-200 dark:border-white/[0.06] overflow-hidden shadow-sm dark:shadow-none shrink-0 h-[65vh] lg:h-full" style="animation-delay:.08s">
        <div class="h-1 w-full bg-gradient-to-r from-orange-400 via-emerald-400 to-violet-400 bg-[length:200%_100%] animate-gradient-flow shrink-0"></div>

        <div class="p-4 bg-gradient-to-br from-gray-900 to-gray-800 text-white border-b border-gray-800 shrink-0">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-lg flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Venta actual
                </h3>
                <span class="text-xs px-3 py-1 rounded-full font-medium transition-all" :class="carrito.length > 0 ? 'bg-emerald-500/20 text-emerald-300' : 'bg-white/10'" x-text="carrito.length + ' partidas'"></span>
            </div>

            <div class="mt-3 transition-all duration-300" :class="mayoreoCelebrate ? 'ring-4 ring-emerald-300/40 rounded-lg' : ''">
                <div class="flex items-center justify-between text-[11px] mb-1.5">
                    <span class="font-mono font-bold text-white/90" x-text="totalLlantas + ' / 20 llantas'"></span>
                    <span class="font-semibold transition-colors" :class="aplicaMayoreoGlobal ? 'text-emerald-400' : 'text-white/50'"
                        x-text="aplicaMayoreoGlobal ? '¡Mayoreo activo!' : 'Faltan ' + (20 - totalLlantas) + ' para mayoreo'"></span>
                </div>
                <div class="h-2 w-full bg-white/10 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500 ease-out relative overflow-hidden"
                        :class="aplicaMayoreoGlobal ? 'bg-gradient-to-r from-emerald-400 to-emerald-500' : 'bg-gradient-to-r from-amber-400 to-amber-500'"
                        :style="'width:' + Math.min(100, (totalLlantas / 20 * 100)) + '%'">
                        <div class="absolute inset-0 shimmer-bar" x-show="totalLlantas > 0"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-2.5 bg-gray-50/50 dark:bg-zinc-950/40">
            <template x-for="(item, i) in carrito" :key="i">
                <div class="flex flex-col p-3 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 shadow-sm relative hover:shadow-md hover:border-gray-300 dark:hover:border-zinc-600 transition-shadow"
                    x-transition:enter="transition-all ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-x-4"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition-all ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-x-0 max-h-40"
                    x-transition:leave-end="opacity-0 translate-x-4 max-h-0 !py-0 !my-0 !border-0">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0 pr-6">
                            <div class="text-sm font-bold text-gray-800 dark:text-zinc-100 leading-tight" x-text="item.nombre"></div>
                            <div class="flex flex-wrap gap-1.5 mt-1.5">
                                <span x-show="item.tipo === 'Servicio'" class="text-[9px] bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400 px-1.5 py-0.5 rounded font-bold tracking-wider">SERVICIO</span>
                                <span class="text-xs font-semibold text-gray-500 dark:text-zinc-400" x-text="'$' + calcularItem(item).precioUnitario.toLocaleString() + ' c/u'"></span>
                                <span x-show="item.tipo === 'Llanta' && aplicaMayoreoGlobal" class="text-[9px] bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded font-bold tracking-wider border border-emerald-200 dark:border-emerald-800">MAYOREO</span>
                            </div>
                        </div>
                        <button @click="carrito.splice(i,1)" class="absolute top-2.5 right-2.5 text-gray-300 dark:text-zinc-600 hover:text-red-500 dark:hover:text-red-400 active:scale-90 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="flex items-center justify-between mt-2.5 pt-2.5 border-t border-gray-50 dark:border-zinc-700">
                        <div class="flex items-center gap-1 bg-gray-50 dark:bg-zinc-900 rounded-lg p-0.5 border border-gray-200 dark:border-zinc-700">
                            <button @click="item.cantidad > 1 ? (item.cantidad--, bumpQty(i)) : carrito.splice(i,1)" class="w-6 h-6 rounded bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-600 text-gray-600 dark:text-zinc-300 font-bold text-sm hover:bg-gray-100 dark:hover:bg-zinc-700 active:scale-90 transition flex items-center justify-center">&minus;</button>
                            <span class="w-7 text-center text-sm font-bold text-gray-800 dark:text-zinc-100 tabular-nums inline-block transition-transform duration-150"
                                :class="qtyBumpIndex === i ? 'scale-125 text-emerald-600 dark:text-emerald-400' : 'scale-100'"
                                x-text="item.cantidad"></span>
                            <button @click="item.cantidad++; bumpQty(i)" class="w-6 h-6 rounded bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-600 text-gray-600 dark:text-zinc-300 font-bold text-sm hover:bg-gray-100 dark:hover:bg-zinc-700 active:scale-90 transition flex items-center justify-center">+</button>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-black text-gray-900 dark:text-zinc-100 tabular-nums" x-text="'$' + calcularItem(item).totalFinal.toLocaleString()"></div>
                            <div x-show="calcularItem(item).descuento > 0" class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 mt-0.5" x-text="'-$' + calcularItem(item).descuento + ' Dto (4x)'"></div>
                        </div>
                    </div>
                </div>
            </template>

            <div x-show="carrito.length === 0" x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                class="text-center py-10">
                <div class="w-12 h-12 bg-gray-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-3 animate-bounce-gentle">
                    <svg class="w-6 h-6 text-gray-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <p class="text-gray-400 dark:text-zinc-500 font-medium text-xs">El carrito está vacío</p>
            </div>
        </div>

        <div class="relative h-3 shrink-0 bg-gray-50/50 dark:bg-zinc-950/40">
            <div class="absolute inset-x-0 bottom-0 h-3 ticket-notch"></div>
        </div>

        <div class="border-t border-gray-200 dark:border-white/[0.06] p-4 space-y-3 bg-white dark:bg-zinc-950 shrink-0">

            <div x-show="totalAhorro > 0" x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="text-xs font-bold text-emerald-600 dark:text-emerald-400 text-center bg-emerald-50 dark:bg-emerald-900/20 py-1.5 rounded-lg border border-emerald-100 dark:border-emerald-800 flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Ahorro total aplicado: $<span x-text="totalAhorro.toLocaleString()"></span>
            </div>

            <div class="total-shimmer bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl p-3.5 flex justify-between items-center shadow-md shadow-emerald-500/20 dark:shadow-emerald-900/30 transition-transform"
                :class="totalFlash ? 'animate-flash' : ''">
                <span class="text-emerald-50 text-xs font-semibold tracking-wide">TOTAL</span>
                <span class="text-white text-2xl font-black tracking-tight tabular-nums" x-text="'$' + totalGeneral().toLocaleString()"></span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[10px] text-gray-500 dark:text-zinc-400 font-bold block mb-1 tracking-wider uppercase">Paga con</label>
                    <input x-model.number="pagoCon" type="number" placeholder="0"
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg text-sm font-bold text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-white dark:focus:bg-zinc-800 transition-all">
                </div>
                <div>
                    <label class="text-[10px] text-gray-500 dark:text-zinc-400 font-bold block mb-1 tracking-wider uppercase">Cambio</label>
                    <div class="px-3 py-2 rounded-lg text-sm font-black flex items-center h-[38px] tabular-nums transition-colors"
                        :class="cambio() > 0 && pagoCon > 0 ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-gray-50 dark:bg-zinc-800 text-gray-400 dark:text-zinc-500 border border-gray-100 dark:border-zinc-700'"
                        x-text="'$' + cambio().toLocaleString()">
                    </div>
                </div>
            </div>

            <div class="flex gap-2">
                <template x-for="b in [200, 500, 1000, 2000]">
                    <button @click="pagoCon = b"
                        class="flex-1 py-1.5 text-[11px] rounded-md font-bold transition-all duration-150 active:scale-95 shadow-sm border"
                        :class="pagoCon === b ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white dark:bg-zinc-800 border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-700 hover:border-gray-300 dark:hover:border-zinc-600'"
                        x-text="'$' + b"></button>
                </template>
                <button @click="pagoCon = totalGeneral()"
                    class="flex-1 py-1.5 text-[11px] rounded-md font-bold transition-all duration-150 active:scale-95 shadow-sm border"
                    :class="pagoCon === totalGeneral() && totalGeneral() > 0 ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/40'">
                    Exacto
                </button>
            </div>

            <div class="flex items-center gap-3">
                <input x-model="cliente" type="text" placeholder="Cliente (opcional)"
                    class="flex-1 px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg text-sm text-gray-800 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white dark:focus:bg-zinc-800 transition-all">

                <label class="flex items-center gap-2 text-xs text-gray-500 dark:text-zinc-400 cursor-pointer whitespace-nowrap">
                    <input x-model="requiereFactura" type="checkbox" class="w-3.5 h-3.5 rounded border-gray-300 dark:border-zinc-600 dark:bg-zinc-800 text-emerald-600 focus:ring-emerald-500">
                    Factura
                </label>
            </div>

            <button @click="cobrar()" :disabled="carrito.length === 0 || procesando"
                class="w-full py-3 text-white dark:text-zinc-900 rounded-xl font-bold text-sm transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                :class="claseBotonCobrar()">
                <svg x-show="procesando" class="w-4 h-4 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span x-text="procesando ? 'Procesando...' : 'Cobrar venta'"></span>
            </button>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

    /* ── Fondo punteado del catálogo, con variante oscura ── */
    .pos-grid-bg {
        background-color: rgba(249,250,251,0.5);
        background-image: radial-gradient(circle, #e5e7eb 1px, transparent 1px);
        background-size: 22px 22px;
    }
    .dark .pos-grid-bg {
        background-color: rgba(0,0,0,0.5);
        background-image: radial-gradient(circle, #27272a 1px, transparent 1px);
    }

    /* ── Entrada de los paneles principales ── */
    .panel-in {
        opacity: 0;
        transform: translateY(10px);
        animation: fadeUpPanel .5s cubic-bezier(.22,.61,.36,1) forwards;
    }
    @keyframes fadeUpPanel {
        to { opacity: 1; transform: translateY(0); }
    }

    /* ── Resplandor ambiental muy sutil, solo en oscuro, para que el panel
         negro no se sienta plano contra el fondo negro de la página ── */
    .dark .panel-glow {
        animation: panelGlow 6s ease-in-out infinite;
    }
    @keyframes panelGlow {
        0%, 100% { box-shadow: 0 0 0 1px rgba(255,255,255,0.04), 0 20px 40px -20px rgba(16,185,129,0.06); }
        50%      { box-shadow: 0 0 0 1px rgba(255,255,255,0.07), 0 20px 46px -18px rgba(16,185,129,0.12); }
    }

    /* ── Barra degradada superior con movimiento continuo ── */
    .animate-gradient-flow { animation: gradientFlow 6s linear infinite; }
    @keyframes gradientFlow {
        0%   { background-position: 0% 0; }
        100% { background-position: 200% 0; }
    }

    /* ── Brillo continuo y sutil sobre la barra de TOTAL ── */
    .total-shimmer { position: relative; overflow: hidden; }
    .total-shimmer::after {
        content: '';
        position: absolute; top: 0; left: -40%;
        width: 40%; height: 100%;
        background: linear-gradient(100deg, transparent, rgba(255,255,255,.35), transparent);
        animation: totalShimmer 3.2s ease-in-out infinite;
    }
    @keyframes totalShimmer {
        0%   { left: -40%; }
        60%, 100% { left: 130%; }
    }

    /* ── Muesca tipo "boleto" al fondo del carrito: usa el color real del panel ── */
    .ticket-notch {
        background-image:
            linear-gradient(-45deg, var(--panel-bg, #fff) 6px, transparent 0),
            linear-gradient(45deg, var(--panel-bg, #fff) 6px, transparent 0);
        background-position: left bottom;
        background-repeat: repeat-x;
        background-size: 12px 12px;
    }

    @keyframes cardIn {
        from { opacity: 0; transform: translateY(10px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .animate-card-in { animation: cardIn 0.35s cubic-bezier(0.22, 1, 0.36, 1) both; }

    @keyframes pulseSubtle {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.015); }
    }
    .animate-pulse-subtle { animation: pulseSubtle 2.2s ease-in-out infinite; }

    @keyframes flashTotal {
        0% { transform: scale(1); }
        30% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    .animate-flash { animation: flashTotal 0.32s ease-out; }

    @keyframes shimmerBar {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    .shimmer-bar {
        background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.5) 50%, rgba(255,255,255,0) 100%);
        animation: shimmerBar 1.6s ease-in-out infinite;
    }

    @keyframes spinSlow { to { transform: rotate(360deg); } }
    .animate-spin-slow { animation: spinSlow 0.8s linear infinite; }

    @keyframes bounceGentle {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-4px); }
    }
    .animate-bounce-gentle { animation: bounceGentle 2.4s ease-in-out infinite; }

    @media (prefers-reduced-motion: reduce) {
        .animate-card-in, .animate-pulse-subtle, .animate-flash, .shimmer-bar, .animate-spin-slow,
        .animate-bounce-gentle, .panel-in, .panel-glow, .animate-gradient-flow, .total-shimmer::after {
            animation: none !important;
        }
        .panel-in { opacity: 1; transform: none; }
    }
</style>

<script>
function puntoVenta() {
    return {
        productos: @json($productos),
        carrito: [],
        busqueda: '',
        filtroCategoria: 'Todos',
        filtroMarca: '',
        filtroUso: '',
        filtroStock: '',
        filtroPrecio: '',
        pagoCon: '',
        cliente: '',
        requiereFactura: false,
        sucursalSeleccionada: '{{ $sucursalDefecto }}',
        rangosPrecios: [
            { label: '$0-$1,000', min: 0, max: 1000 },
            { label: '$1,000-$3,000', min: 1000, max: 3000 },
            { label: '$3,000-$5,000', min: 3000, max: 5000 },
            { label: '$5,000+', min: 5000, max: 999999 },
        ],

        // Estado de UI / animaciones
        toastMsg: '',
        toastType: 'success',
        toastVisible: false,
        toastTimer: null,
        ultimoAgregadoId: null,
        ultimoAgregadoTimer: null,
        totalFlash: false,
        totalFlashTimer: null,
        procesando: false,
        scrolledProductos: false,
        sucursalFlash: false,
        sucursalFlashTimer: null,
        mayoreoCelebrate: false,
        mayoreoCelebrateTimer: null,
        qtyBumpIndex: null,
        qtyBumpTimer: null,

        init() {
            this.actualizarStockPorSucursal();

            this.$watch('sucursalSeleccionada', () => {
                this.actualizarStockPorSucursal();
                this.carrito = [];
                this.mostrarToast('Sucursal actualizada — carrito vaciado');
                this.sucursalFlash = true;
                clearTimeout(this.sucursalFlashTimer);
                this.sucursalFlashTimer = setTimeout(() => this.sucursalFlash = false, 700);
            });

            this.$watch('totalActual', () => {
                this.totalFlash = true;
                clearTimeout(this.totalFlashTimer);
                this.totalFlashTimer = setTimeout(() => this.totalFlash = false, 320);
            });

            this.$watch('aplicaMayoreoGlobal', (valor, anterior) => {
                if (valor && !anterior) {
                    this.mostrarToast('¡Mayoreo activado! Precio especial en llantas', 'success');
                    this.mayoreoCelebrate = true;
                    clearTimeout(this.mayoreoCelebrateTimer);
                    this.mayoreoCelebrateTimer = setTimeout(() => this.mayoreoCelebrate = false, 900);
                }
            });
        },

        get totalActual() {
            return this.totalGeneral();
        },

        actualizarStockPorSucursal() {
            this.productos.forEach(p => {
                if (p.tipo === 'Servicio') {
                    p.stock_cantidad = 999999;
                } else {
                    p.stock_cantidad = p.stocks[this.sucursalSeleccionada] || 0;
                }
            });
        },

        contarConStock() {
            return this.productos.filter(p => p.tipo === 'Servicio' || p.stock_cantidad > 0).length;
        },

        contarAgotados() {
            return this.productos.filter(p => p.tipo !== 'Servicio' && p.stock_cantidad <= 0).length;
        },

        get marcas() {
            const m = [...new Set(this.productos.map(p => {
                const marca = (p.marca || '').split(' ')[0].toUpperCase();
                if (!marca || marca.length < 3) return null;
                if (/^[\d\-\.\/]+[A-Z]?$/.test(marca)) return null;
                if (/^\d+[A-Z]+\d*$/.test(marca)) return null;
                return marca;
            }).filter(Boolean))];
            return m.sort();
        },

        get usos() {
            const u = new Set();
            this.productos.forEach(p => {
                const t = ((p.tipo || '') + ' ' + (p.marca || '') + ' ' + (p.descripcion || '')).toUpperCase();
                if (t.includes('DIRECC')) u.add('Dirección');
                if (t.includes('TRACC')) u.add('Tracción');
                if (t.includes('LINEAL')) u.add('Lineal');
            });
            return [...u].sort();
        },

        contarMarca(m) {
            return this.productos.filter(p => (p.marca || '').split(' ')[0].toUpperCase() === m).length;
        },

        bordeTipo(tipo) {
            const map = { 'Llanta': 'bg-orange-400', 'Rin': 'bg-sky-400', 'Accesorio': 'bg-teal-400', 'Servicio': 'bg-violet-400' };
            return map[tipo] || 'bg-gray-300';
        },

        tipoBadgeClase(tipo) {
            const map = {
                'Llanta': 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                'Rin': 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400',
                'Accesorio': 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400',
                'Servicio': 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
            };
            return map[tipo] || 'bg-gray-100 text-gray-700 dark:bg-zinc-800 dark:text-zinc-300';
        },

        claseBotonCobrar() {
            if (this.carrito.length === 0 || this.procesando) return 'bg-gray-300 dark:bg-zinc-700';
            return 'bg-gray-900 hover:bg-black dark:bg-white dark:hover:bg-zinc-100 active:scale-[0.98] shadow-lg animate-pulse-subtle';
        },

        mostrarToast(msg, type = 'success') {
            this.toastMsg = msg;
            this.toastType = type;
            this.toastVisible = true;
            clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(() => this.toastVisible = false, 2200);
        },

        bumpQty(i) {
            this.qtyBumpIndex = i;
            clearTimeout(this.qtyBumpTimer);
            this.qtyBumpTimer = setTimeout(() => this.qtyBumpIndex = null, 200);
        },

        hayFiltrosActivos() {
            return this.filtroCategoria !== 'Todos' || this.filtroMarca || this.filtroUso || this.filtroStock || this.filtroPrecio || this.busqueda;
        },

        limpiarFiltros() {
            this.filtroCategoria = 'Todos'; this.filtroMarca = ''; this.filtroUso = ''; this.filtroStock = ''; this.filtroPrecio = ''; this.busqueda = '';
        },

        productosFiltrados() {
            let lista = this.productos;

            if (this.filtroCategoria !== 'Todos') {
                const catBuscada = this.filtroCategoria.trim().toLowerCase();
                lista = lista.filter(p => {
                    const tipoBd = (p.tipo || '').trim().toLowerCase();
                    return tipoBd === catBuscada || tipoBd.includes(catBuscada);
                });
            }

            if (this.filtroMarca) {
                lista = lista.filter(p => (p.marca || '').split(' ')[0].toUpperCase() === this.filtroMarca);
            }

            if (this.filtroUso) {
                lista = lista.filter(p => {
                    const t = ((p.tipo || '') + ' ' + (p.marca || '') + ' ' + (p.descripcion || '')).toUpperCase();
                    if (this.filtroUso === 'Dirección') return t.includes('DIRECC');
                    if (this.filtroUso === 'Tracción') return t.includes('TRACC');
                    if (this.filtroUso === 'Lineal') return t.includes('LINEAL');
                    return true;
                });
            }

            if (this.filtroStock === 'disponible') lista = lista.filter(p => p.tipo === 'Servicio' || p.stock_cantidad > 5);
            else if (this.filtroStock === 'poco') lista = lista.filter(p => p.stock_cantidad > 0 && p.stock_cantidad <= 5 && p.tipo !== 'Servicio');
            else if (this.filtroStock === 'agotado') lista = lista.filter(p => p.tipo !== 'Servicio' && p.stock_cantidad <= 0);

            if (this.filtroPrecio) {
                const r = this.rangosPrecios.find(r => r.label === this.filtroPrecio);
                if (r) lista = lista.filter(p => { const pr = +p.precio_publico; return pr >= r.min && pr < r.max; });
            }

            if (this.busqueda) {
                const q = this.busqueda.toLowerCase();
                lista = lista.filter(p => (p.marca + ' ' + p.medida + ' ' + p.tipo + ' ' + (p.descripcion || '')).toLowerCase().includes(q));
            }

            return lista;
        },

        agregar(p) {
            if (p.tipo !== 'Servicio' && p.stock_cantidad <= 0) return;

            const existe = this.carrito.find(i => i.producto_id === p.id && i.tipo !== 'Servicio');
            if (existe) {
                existe.cantidad++;
            } else {
                this.carrito.push({
                    producto_id: p.id,
                    nombre: p.tipo === 'Servicio' ? p.descripcion : (p.marca + ' ' + p.medida),
                    tipo: p.tipo,
                    precio_publico: +p.precio_publico,
                    precio_mayoreo: +p.precio_mayoreo || +p.precio_publico,
                    cantidad: 1,
                });
            }

            this.mostrarToast('Agregado: ' + (p.tipo === 'Servicio' ? p.descripcion : (p.marca + ' ' + p.medida)));

            this.ultimoAgregadoId = p.id;
            clearTimeout(this.ultimoAgregadoTimer);
            this.ultimoAgregadoTimer = setTimeout(() => this.ultimoAgregadoId = null, 500);
        },

        get totalLlantas() {
            return this.carrito.reduce((suma, item) => item.tipo === 'Llanta' ? suma + item.cantidad : suma, 0);
        },

        get aplicaMayoreoGlobal() {
            return this.totalLlantas >= 20;
        },

        calcularItem(item) {
            let precioUnitario = item.precio_publico;

            if (item.tipo === 'Llanta' && this.aplicaMayoreoGlobal) {
                precioUnitario = item.precio_mayoreo;
            }

            let subtotal = precioUnitario * item.cantidad;
            let descuento = 0;

            if (item.tipo === 'Llanta') {
                const bloquesDe4 = Math.floor(item.cantidad / 4);
                descuento = bloquesDe4 * 80;
            }

            return {
                precioUnitario,
                subtotal,
                descuento,
                totalFinal: subtotal - descuento
            };
        },

        totalGeneral() {
            return this.carrito.reduce((suma, item) => suma + this.calcularItem(item).totalFinal, 0);
        },

        get totalAhorro() {
            let ahorro = 0;
            this.carrito.forEach(item => {
                if (item.tipo === 'Llanta') {
                    const calculo = this.calcularItem(item);
                    ahorro += calculo.descuento;

                    if (this.aplicaMayoreoGlobal && item.precio_mayoreo < item.precio_publico) {
                        ahorro += (item.precio_publico - item.precio_mayoreo) * item.cantidad;
                    }
                }
            });
            return ahorro;
        },

        cambio() {
            const c = (+this.pagoCon || 0) - this.totalGeneral();
            return c > 0 ? c : 0;
        },

        async cobrar() {
            if (this.carrito.length === 0 || this.procesando) return;

            let pagoEfectivo = +this.pagoCon || this.totalGeneral();
            if (pagoEfectivo < this.totalGeneral()) {
                this.mostrarToast('El monto ingresado es menor al total de la venta.', 'error');
                return;
            }

            this.procesando = true;

            const payload = {
                sucursal_id: this.sucursalSeleccionada,
                carrito: this.carrito.map(item => {
                    const calc = this.calcularItem(item);
                    return {
                        producto_id: item.producto_id,
                        nombre: item.nombre,
                        tipo: item.tipo,
                        cantidad: item.cantidad,
                        precio_unitario: calc.precioUnitario,
                        descuento: calc.descuento,
                        subtotal: calc.totalFinal
                    };
                }),
                pagoCon: pagoEfectivo,
                cambio: this.cambio(),
                total: this.totalGeneral(),
                cliente: this.cliente,
                requiereFactura: this.requiereFactura
            };

            try {
                const ticketWindow = window.open('', 'TicketVenta', 'width=400,height=600');

                const response = await fetch('{{ route("ventas.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (data.success) {
                    ticketWindow.location.href = data.ticket_url;
                    window.location.reload();
                } else {
                    ticketWindow.close();
                    this.mostrarToast(data.message || 'Error al procesar la venta', 'error');
                    this.procesando = false;
                }
            } catch (error) {
                this.mostrarToast('Error de conexión con el servidor.', 'error');
                this.procesando = false;
            }
        }
    }
}
</script>
@endsection