@extends('layouts.app')

@section('header_title', 'Punto de Venta')

@section('content')

<style>
    [x-cloak] { display: none !important; }

    /* Barras de desplazamiento delgadas pero visibles en el catálogo y el carrito */
    .pv-scroll { scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }
    .pv-scroll::-webkit-scrollbar { width: 8px; height: 8px; }
    .pv-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    html.dark .pv-scroll { scrollbar-color: #3f3f46 transparent; }
    html.dark .pv-scroll::-webkit-scrollbar-thumb { background: #3f3f46; }
</style>

<div x-data="puntoVenta()" @keydown.escape.window="sucursalPendiente = null" class="relative">

    {{-- AVISO (errores, stock máximo y venta lista) --}}
    <div x-show="toastVisible" x-cloak
         x-transition.opacity.duration.200ms
         class="fixed top-5 left-1/2 -translate-x-1/2 z-[150] px-4 py-3 rounded-xl shadow-lg text-sm font-semibold flex items-center gap-2.5 max-w-md border"
         :class="toastType === 'error'
                    ? 'bg-red-600 text-white border-red-500'
                    : (toastType === 'warning'
                        ? 'bg-amber-500 text-white border-amber-400'
                        : 'bg-emerald-600 text-white border-emerald-500')"
         role="alert">
        <svg x-show="toastType === 'success'" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <svg x-show="toastType !== 'success'" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        <span x-text="toastMsg"></span>
    </div>

    <div class="flex flex-col lg:flex-row gap-4 lg:gap-6 h-auto lg:h-[calc(100vh-120px)]">

        {{-- ================= CATÁLOGO ================= --}}
        <div class="flex-1 min-w-0 flex flex-col bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden h-[70vh] lg:h-full">

            {{-- Encabezado y filtros --}}
            <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-white/5 space-y-4 shrink-0">

                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Productos y servicios</h2>
                        <div class="mt-2 flex items-center gap-2 flex-wrap text-xs font-semibold">
                            <span class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-white/5 text-gray-600 dark:text-gray-300 tabular-nums"
                                  x-text="productosFiltrados().length + ' de ' + productos.length"></span>
                            <span class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 tabular-nums"
                                  x-text="contarConStock() + ' con stock'"></span>
                            <span class="px-2.5 py-1 rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 tabular-nums"
                                  x-text="contarAgotados() + ' agotados'"></span>
                            <span x-show="contarNuevos() > 0" x-cloak
                                  class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 tabular-nums"
                                  x-text="contarNuevos() + ' recién llegados'"></span>
                        </div>
                    </div>

                    {{-- Sucursal: el administrador la elige; los demás solo la ven --}}
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Sucursal</span>
                        @if($esAdmin)
                            <select @change="pedirCambioSucursal($event)"
                                    class="px-3 py-2 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                                <template x-for="suc in sucursales" :key="suc.id">
                                    <option :value="suc.id" :selected="suc.id == sucursalSeleccionada" x-text="suc.nombre"></option>
                                </template>
                            </select>
                        @else
                            <span class="px-3 py-2 bg-gray-100 dark:bg-white/5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-200"
                                  x-text="(sucursales.find(s => s.id == sucursalSeleccionada) || {}).nombre || '—'"></span>
                        @endif
                    </div>
                </div>

                {{-- Búsqueda --}}
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input x-model="busqueda" @keydown.enter.prevent="agregarUnico()" type="text"
                           placeholder="Buscar por marca, medida o descripción (Enter agrega si solo hay uno)"
                           class="w-full pl-10 pr-10 py-2.5 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button x-show="busqueda" @click="busqueda = ''" x-cloak type="button" aria-label="Borrar búsqueda"
                            class="absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Filtros --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-x-5 gap-y-2 flex-wrap">

                        <div class="flex items-center gap-2">
                            <label for="pv_marca" class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Marca</label>
                            <select id="pv_marca" x-model="filtroMarca"
                                    class="min-w-[160px] px-3 py-1.5 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                                <option value="">Todas las marcas</option>
                                <template x-for="m in marcas()" :key="m">
                                    <option :value="m" x-text="m + ' (' + contarMarca(m) + ')'"></option>
                                </template>
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Stock</span>
                            <div class="flex gap-1">
                                <template x-for="op in [['', 'Todos'], ['disponible', 'Con stock'], ['agotado', 'Agotado']]" :key="op[1]">
                                    <button @click="filtroStock = op[0]" type="button"
                                            class="px-3 py-1 rounded-full text-xs font-semibold border transition-colors"
                                            :class="filtroStock === op[0]
                                                ? 'bg-indigo-600 text-white border-indigo-600'
                                                : 'bg-white dark:bg-transparent text-gray-600 dark:text-gray-300 border-gray-200 dark:border-white/15 hover:bg-gray-50 dark:hover:bg-white/5'"
                                            x-text="op[1]"></button>
                                </template>
                            </div>
                        </div>

                        <button @click="filtroNuevo = !filtroNuevo" type="button"
                                class="px-3 py-1 rounded-full text-xs font-semibold border transition-colors inline-flex items-center gap-1.5"
                                :class="filtroNuevo
                                    ? 'bg-emerald-600 text-white border-emerald-600'
                                    : 'bg-white dark:bg-transparent text-gray-600 dark:text-gray-300 border-gray-200 dark:border-white/15 hover:bg-gray-50 dark:hover:bg-white/5'">
                            Recién llegado
                            <span x-show="contarNuevos() > 0" x-cloak class="tabular-nums" x-text="'(' + contarNuevos() + ')'"></span>
                        </button>

                        <button @click="limpiarFiltros()" x-show="hayFiltrosActivos()" x-cloak type="button"
                                class="px-3 py-1 rounded-full text-xs font-semibold text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/20 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors">
                            Limpiar filtros
                        </button>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Categoría</span>
                        <div class="flex gap-1.5 flex-wrap">
                            <template x-for="cat in ['Todos', 'Llanta', 'Rin', 'Accesorio', 'Servicio']" :key="cat">
                                <button @click="filtroCategoria = cat" type="button"
                                        class="px-3 py-1 rounded-full text-xs font-semibold border transition-colors inline-flex items-center gap-1.5"
                                        :class="filtroCategoria === cat
                                            ? 'bg-indigo-600 text-white border-indigo-600'
                                            : 'bg-white dark:bg-transparent text-gray-600 dark:text-gray-300 border-gray-200 dark:border-white/15 hover:bg-gray-50 dark:hover:bg-white/5'">
                                    <span x-show="cat !== 'Todos'" class="w-1.5 h-1.5 rounded-full" :class="puntoTipo(cat)"></span>
                                    <span x-text="cat"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Catálogo --}}
            <div class="flex-1 overflow-y-auto pv-scroll p-4 sm:p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                    <template x-for="p in productosFiltrados()" :key="p.id">
                        <button @click="agregar(p)" :disabled="agotado(p)" type="button"
                                class="relative flex flex-col justify-between text-left p-4 rounded-xl border transition-colors"
                                :class="agotado(p)
                                    ? 'border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/[0.02] opacity-60 cursor-not-allowed'
                                    : 'border-gray-200 dark:border-white/10 bg-white dark:bg-transparent hover:border-indigo-400 dark:hover:border-indigo-500 hover:bg-indigo-50/40 dark:hover:bg-white/5 cursor-pointer'">

                            <div>
                                <div class="flex items-center gap-1.5 flex-wrap mb-2">
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-white/5 text-gray-600 dark:text-gray-300">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="puntoTipo(p.tipo)"></span>
                                        <span x-text="p.tipo"></span>
                                    </span>
                                    <span x-show="p.es_nuevo" class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">Nuevo</span>
                                    <span x-show="p.tipo !== 'Servicio'"
                                          class="text-[11px] font-bold px-2 py-0.5 rounded-full tabular-nums"
                                          :class="p.stock_cantidad > 5
                                                    ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400'
                                                    : (p.stock_cantidad > 0
                                                        ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400'
                                                        : 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400')"
                                          x-text="p.stock_cantidad > 0 ? 'Stock: ' + p.stock_cantidad : 'Agotado'"></span>
                                </div>

                                <div class="text-sm font-bold text-gray-900 dark:text-white leading-snug"
                                     x-text="p.tipo === 'Servicio' ? p.descripcion : p.marca"></div>
                                <div x-show="p.tipo !== 'Servicio'" class="mt-0.5 text-xs font-mono text-gray-500 dark:text-gray-400" x-text="p.medida"></div>
                            </div>

                            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-white/5 flex items-center justify-between">
                                <span class="text-base font-extrabold tabular-nums"
                                      :class="usaMayoreo(p) ? 'text-amber-600 dark:text-amber-400' : 'text-indigo-600 dark:text-indigo-400'"
                                      x-text="fmt(precioDe(p))"></span>
                                <span x-show="usaMayoreo(p)" class="text-[11px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Mayoreo</span>
                            </div>
                        </button>
                    </template>
                </div>

                <div x-show="productosFiltrados().length === 0" x-cloak class="py-16 text-center">
                    <span class="mx-auto w-11 h-11 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <p class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-200">No se encontraron productos</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Prueba con otra búsqueda o limpia los filtros.</p>
                </div>
            </div>
        </div>

        {{-- ================= CARRITO Y COBRO ================= --}}
        <div class="w-full lg:w-[380px] xl:w-[420px] shrink-0 flex flex-col bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden lg:h-full">

            <div class="p-4 border-b border-gray-100 dark:border-white/5 flex items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Carrito</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 tabular-nums" x-text="totalItems() + (totalItems() === 1 ? ' pieza' : ' piezas')"></p>
                    </div>
                </div>
                <button @click="limpiarCarrito()" x-show="carrito.length > 0" x-cloak type="button"
                        class="px-3 py-1.5 text-xs font-semibold text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/20 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                    Vaciar
                </button>
            </div>

            {{-- Partidas --}}
            <div class="flex-1 min-h-0 overflow-y-auto pv-scroll">
            <div class="p-4 space-y-2 min-h-[120px]">
                <template x-for="item in carrito" :key="item.id">
                    <div class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/[0.03]">
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate" x-text="nombreDe(item)"></h4>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 tabular-nums" x-text="fmt(precioDe(item)) + ' c/u'"></p>
                        </div>

                        <div class="flex items-center gap-1 bg-white dark:bg-transparent border border-gray-200 dark:border-white/10 rounded-lg p-0.5">
                            <button @click="disminuir(item)" type="button" aria-label="Quitar una pieza"
                                    class="w-6 h-6 rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10 font-bold transition-colors">−</button>
                            <span class="text-xs font-bold w-6 text-center text-gray-900 dark:text-white tabular-nums" x-text="item.cantidad"></span>
                            <button @click="aumentar(item)" type="button" aria-label="Agregar una pieza"
                                    class="w-6 h-6 rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10 font-bold transition-colors">+</button>
                        </div>

                        <span class="min-w-[68px] text-right text-xs font-extrabold text-gray-900 dark:text-white tabular-nums"
                              x-text="fmt(r2(item.cantidad * precioDe(item)))"></span>

                        <button @click="quitar(item)" type="button" title="Quitar" aria-label="Quitar del carrito"
                                class="p-1 text-gray-400 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>

                <div x-show="carrito.length === 0" class="h-full min-h-[160px] flex flex-col items-center justify-center text-center text-gray-400 dark:text-gray-500">
                    <svg class="w-10 h-10 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">El carrito está vacío</p>
                    <p class="mt-1 text-xs">Elige un producto del catálogo.</p>
                </div>
            </div>

            {{-- Opciones de cobro (si no caben, esta zona hace scroll) --}}
            <div class="p-4 border-t border-gray-100 dark:border-white/5 bg-gray-50/60 dark:bg-white/[0.02] space-y-3">

                @if($puedeMayoreo)
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tipo de precio</span>
                        <div class="flex gap-1">
                            <button @click="tipoPrecio = 'menudeo'" type="button"
                                    class="px-3 py-1 rounded-lg text-xs font-bold border transition-colors"
                                    :class="tipoPrecio === 'menudeo' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-transparent text-gray-600 dark:text-gray-300 border-gray-200 dark:border-white/15'">
                                Público
                            </button>
                            <button @click="tipoPrecio = 'mayoreo'" type="button"
                                    class="px-3 py-1 rounded-lg text-xs font-bold border transition-colors"
                                    :class="tipoPrecio === 'mayoreo' ? 'bg-amber-500 text-white border-amber-500' : 'bg-white dark:bg-transparent text-gray-600 dark:text-gray-300 border-gray-200 dark:border-white/15'">
                                Mayoreo
                            </button>
                        </div>
                    </div>
                @endif

                <div>
                    <label for="input_cliente" class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Cliente (opcional)</label>
                    <select id="input_cliente" x-model="clienteId"
                            class="w-full px-3 py-2 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Público General</option>
                        <template x-for="c in clientes" :key="c.id">
                            <option :value="c.id" x-text="c.nombre + (c.rfc ? ' (' + c.rfc + ')' : '')"></option>
                        </template>
                    </select>
                </div>

                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" x-model="requiereFactura" class="w-4 h-4 rounded border-gray-300 dark:border-white/20 accent-indigo-600">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">¿Requiere factura?</span>
                </label>

                <div x-show="requiereFactura && !clienteConRfc()" x-cloak
                     class="text-xs font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl px-3 py-2">
                    Para facturar elige un cliente que tenga RFC registrado.
                </div>

                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Método de pago</span>
                    <div class="grid grid-cols-3 gap-1.5">
                        <template x-for="m in [['efectivo', 'Efectivo'], ['tarjeta', 'Tarjeta'], ['transferencia', 'Transferencia']]" :key="m[0]">
                            <button @click="metodoPago = m[0]" type="button"
                                    class="px-1 py-2 rounded-xl text-xs font-bold border transition-colors"
                                    :class="metodoPago === m[0]
                                        ? 'bg-indigo-600 text-white border-indigo-600'
                                        : 'bg-white dark:bg-transparent text-gray-600 dark:text-gray-300 border-gray-200 dark:border-white/15 hover:bg-gray-50 dark:hover:bg-white/5'"
                                    x-text="m[1]"></button>
                        </template>
                    </div>
                </div>

                {{-- Con cuánto paga el cliente (solo efectivo) --}}
                <div x-show="metodoPago === 'efectivo'" x-cloak>
                    <label for="input_recibe" class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Recibe (opcional)</label>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">$</span>
                            <input id="input_recibe" x-model="montoRecibido" type="number" min="0" step="0.01" inputmode="decimal" placeholder="Total exacto"
                                   class="w-full pl-7 pr-3 py-2 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-800 dark:text-white tabular-nums placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <button @click="montoRecibido = total() > 0 ? String(total()) : ''" type="button"
                                class="px-3 py-2 text-xs font-semibold bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                            Exacto
                        </button>
                    </div>
                    <p x-show="faltaDinero()" x-cloak class="mt-1.5 text-xs font-semibold text-red-600 dark:text-red-400 tabular-nums"
                       x-text="'Faltan ' + fmt(r2(total() - recibeNum())) + ' para cubrir el total.'"></p>
                    <p x-show="montoRecibido !== '' && !faltaDinero()" x-cloak class="mt-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400 tabular-nums"
                       x-text="'Cambio: ' + fmt(cambio())"></p>
                </div>

            </div>
            </div>

            {{-- Total y botón: siempre visibles abajo --}}
            <div class="shrink-0 p-4 border-t border-gray-200 dark:border-white/10 bg-white dark:bg-[#15161A] space-y-3">
                <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between text-gray-500 dark:text-gray-400">
                        <span>Subtotal</span>
                        <span class="font-bold text-gray-800 dark:text-white tabular-nums" x-text="fmt(subtotal())"></span>
                    </div>
                    <div x-show="requiereFactura" x-cloak class="flex justify-between text-gray-500 dark:text-gray-400">
                        <span>IVA (16%)</span>
                        <span class="font-bold text-gray-800 dark:text-white tabular-nums" x-text="fmt(iva())"></span>
                    </div>
                    <div class="flex justify-between items-center pt-1">
                        <span class="text-base font-extrabold text-gray-900 dark:text-white">Total</span>
                        <span class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 tabular-nums" x-text="fmt(total())"></span>
                    </div>
                </div>

                <button @click="procesarVenta()" type="button"
                        :disabled="carrito.length === 0 || procesando || (requiereFactura && !clienteConRfc()) || faltaDinero()"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 dark:disabled:bg-white/10 disabled:text-gray-500 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl transition-colors flex items-center justify-center gap-2">
                    <svg x-show="procesando" x-cloak class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span x-text="procesando ? 'Procesando venta...' : (carrito.length ? 'Cobrar ' + fmt(total()) : 'Cobrar')"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- VENTANA: cambiar de sucursal con el carrito lleno --}}
    <div x-show="sucursalPendiente !== null" x-cloak
         @click.self="sucursalPendiente = null"
         class="fixed inset-0 !mt-0 z-[100] bg-gray-900/50 flex items-center justify-center p-4">
        <div class="w-full max-w-sm bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-xl p-6">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">¿Cambiar de sucursal?</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Al cambiar de sucursal se vacía el carrito, porque el stock es distinto en cada una.
            </p>
            <div class="mt-5 flex justify-end gap-3">
                <button @click="sucursalPendiente = null" type="button"
                        class="px-4 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    Cancelar
                </button>
                <button @click="irASucursal(sucursalPendiente)" type="button"
                        class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors">
                    Cambiar y vaciar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function puntoVenta() {
        return {
            procesando: false,

            // Datos del servidor
            productos: @json($productos ?? []),
            sucursales: @json($sucursales ?? []),
            sucursalSeleccionada: @json($sucursalSeleccionada),
            clientes: @json($clientes ?? []),

            // Filtros
            busqueda: '',
            filtroMarca: '',
            filtroStock: '',
            filtroCategoria: 'Todos',
            filtroNuevo: false,

            // Carrito y cobro
            carrito: [],
            clienteId: '',
            requiereFactura: false,
            metodoPago: 'efectivo',
            tipoPrecio: 'menudeo',   // 'menudeo' (Público) o 'mayoreo'
            montoRecibido: '',

            // Cambio de sucursal pendiente de confirmar
            sucursalPendiente: null,

            // Aviso
            toastVisible: false,
            toastMsg: '',
            toastType: 'success',
            toastTimer: null,

            /* ---------- Dinero ---------- */
            r2(n) { return Math.round((Number(n) + Number.EPSILON) * 100) / 100; },

            fmt(n) {
                return '$' + Number(n).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },

            // Mayoreo solo si está elegido y el producto lo tiene.
            // (El servidor vuelve a calcular el precio real al cobrar.)
            usaMayoreo(p) {
                return this.tipoPrecio === 'mayoreo' && +p.precio_mayoreo > 0;
            },

            precioDe(p) {
                return this.usaMayoreo(p) ? +p.precio_mayoreo : +p.precio_publico;
            },

            nombreDe(p) {
                return p.tipo === 'Servicio' ? p.descripcion : (p.marca + ' ' + (p.medida || '')).trim();
            },

            subtotal() {
                return this.r2(this.carrito.reduce((s, i) => s + this.r2(i.cantidad * this.precioDe(i)), 0));
            },

            iva() {
                return this.requiereFactura ? this.r2(this.subtotal() * 0.16) : 0;
            },

            total() {
                return this.r2(this.subtotal() + this.iva());
            },

            totalItems() {
                return this.carrito.reduce((s, i) => s + i.cantidad, 0);
            },

            // Cuánto entregó el cliente (solo cuenta en efectivo y si escribió algo)
            recibeNum() {
                const n = parseFloat(this.montoRecibido);
                return isNaN(n) ? 0 : n;
            },

            faltaDinero() {
                return this.metodoPago === 'efectivo' && this.montoRecibido !== '' && this.carrito.length > 0
                    && this.r2(this.recibeNum()) < this.total();
            },

            cambio() {
                return (this.metodoPago === 'efectivo' && this.montoRecibido !== '' && !this.faltaDinero())
                    ? this.r2(this.recibeNum() - this.total())
                    : 0;
            },

            /* ---------- Catálogo ---------- */
            agotado(p) {
                return p.tipo !== 'Servicio' && p.stock_cantidad <= 0;
            },

            quitarAcentos(t) {
                return String(t || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
            },

            marcas() {
                return [...new Set(this.productos.map(p => p.marca).filter(Boolean))].sort();
            },

            contarMarca(marca) {
                return this.productos.filter(p => p.marca === marca).length;
            },

            contarConStock() {
                return this.productos.filter(p => !this.agotado(p)).length;
            },

            contarAgotados() {
                return this.productos.filter(p => this.agotado(p)).length;
            },

            contarNuevos() {
                return this.productos.filter(p => p.es_nuevo).length;
            },

            productosFiltrados() {
                // Cada palabra escrita debe aparecer en la marca, la medida o la descripción
                const palabras = this.quitarAcentos(this.busqueda).split(/\s+/).filter(Boolean);

                return this.productos.filter(p => {
                    const texto = this.quitarAcentos([p.marca, p.medida, p.descripcion].join(' '));
                    const coincideBusqueda = palabras.every(w => texto.includes(w));

                    const coincideMarca = !this.filtroMarca || p.marca === this.filtroMarca;
                    const coincideCategoria = this.filtroCategoria === 'Todos' || p.tipo === this.filtroCategoria;
                    const coincideNuevo = !this.filtroNuevo || p.es_nuevo;

                    let coincideStock = true;
                    if (this.filtroStock === 'disponible') coincideStock = !this.agotado(p);
                    if (this.filtroStock === 'agotado') coincideStock = this.agotado(p);

                    return coincideBusqueda && coincideMarca && coincideCategoria && coincideStock && coincideNuevo;
                });
            },

            hayFiltrosActivos() {
                return this.busqueda !== '' || this.filtroMarca !== '' || this.filtroStock !== ''
                    || this.filtroCategoria !== 'Todos' || this.filtroNuevo;
            },

            limpiarFiltros() {
                this.busqueda = '';
                this.filtroMarca = '';
                this.filtroStock = '';
                this.filtroCategoria = 'Todos';
                this.filtroNuevo = false;
            },

            // Color del puntito de cada tipo
            puntoTipo(tipo) {
                switch (tipo) {
                    case 'Llanta': return 'bg-indigo-500';
                    case 'Rin': return 'bg-amber-500';
                    case 'Accesorio': return 'bg-purple-500';
                    case 'Servicio': return 'bg-emerald-500';
                    default: return 'bg-gray-400';
                }
            },

            /* ---------- Sucursal ---------- */
            // Al cambiar de sucursal se recarga la página para mostrar el stock de ESA sucursal
            pedirCambioSucursal(evento) {
                const id = evento.target.value;
                if (id == this.sucursalSeleccionada) return;

                if (this.carrito.length === 0) {
                    this.irASucursal(id);
                    return;
                }

                // Hay carrito: se deja el selector como estaba y se pide confirmar
                evento.target.value = this.sucursalSeleccionada;
                this.sucursalPendiente = id;
            },

            irASucursal(id) {
                window.location = @json(route('ventas.index')) + '?sucursal_id=' + encodeURIComponent(id);
            },

            /* ---------- Carrito ---------- */
            productoVivo(id) {
                return this.productos.find(p => p.id === id);
            },

            agregar(producto) {
                const prod = this.productoVivo(producto.id) || producto;
                const item = this.carrito.find(i => i.id === prod.id);

                if (item) {
                    if (prod.tipo !== 'Servicio' && item.cantidad >= prod.stock_cantidad) {
                        this.showToast('Ya agregaste todo el stock de este producto.', 'warning');
                        return;
                    }
                    item.cantidad++;
                } else {
                    this.carrito.push({ ...prod, cantidad: 1 });
                }
            },

            // Enter en la búsqueda: si solo queda un producto disponible, lo agrega
            agregarUnico() {
                const lista = this.productosFiltrados().filter(p => !this.agotado(p));
                if (lista.length === 1) {
                    this.agregar(lista[0]);
                    this.busqueda = '';
                }
            },

            aumentar(item) {
                const prod = this.productoVivo(item.id);
                if (prod && prod.tipo !== 'Servicio' && item.cantidad >= prod.stock_cantidad) {
                    this.showToast('No hay más stock disponible de este producto.', 'warning');
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
                this.montoRecibido = '';
            },

            clienteSeleccionado() {
                if (!this.clienteId) return null;
                return this.clientes.find(c => c.id == this.clienteId) || null;
            },

            clienteConRfc() {
                const c = this.clienteSeleccionado();
                return !!(c && c.rfc && String(c.rfc).trim() !== '');
            },

            showToast(msg, type = 'success') {
                this.toastMsg = msg;
                this.toastType = type;
                this.toastVisible = true;
                clearTimeout(this.toastTimer);
                this.toastTimer = setTimeout(() => { this.toastVisible = false; }, 3500);
            },

            /* ---------- Cobro ---------- */
            procesarVenta() {
                if (this.carrito.length === 0 || this.procesando) return;

                if (this.requiereFactura && !this.clienteConRfc()) {
                    this.showToast('Para facturar elige un cliente que tenga RFC registrado.', 'warning');
                    return;
                }

                if (this.faltaDinero()) {
                    this.showToast('El monto recibido no cubre el total.', 'warning');
                    return;
                }

                this.procesando = true;

                // El servidor solo usa producto_id y cantidad; el precio lo calcula él
                const partidas = this.carrito.map(i => ({ producto_id: i.id, cantidad: i.cantidad }));
                const vendidas = this.carrito.map(i => ({ id: i.id, tipo: i.tipo, cantidad: i.cantidad }));

                const datos = {
                    sucursal_id: this.sucursalSeleccionada,
                    cliente_id: this.clienteId || null,   // vacío = Público General
                    tipo_precio: this.tipoPrecio,         // 'menudeo' o 'mayoreo'
                    requiereFactura: this.requiereFactura,
                    pagoCon: this.metodoPago.charAt(0).toUpperCase() + this.metodoPago.slice(1),
                    carrito: partidas
                };

                // Solo en efectivo y solo si el cajero escribió con cuánto pagó el cliente
                if (this.metodoPago === 'efectivo' && this.montoRecibido !== '') {
                    datos.montoRecibido = this.recibeNum();
                }

                fetch(@json(route('ventas.store')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token())
                    },
                    body: JSON.stringify(datos)
                })
                .then(res => res.json())
                .then(data => {
                    this.procesando = false;

                    if (!data.success) {
                        this.showToast(data.message || 'No se pudo procesar la venta.', 'error');
                        return;
                    }

                    // El stock en pantalla baja igual que en la base de datos (los servicios no descuentan)
                    vendidas.forEach(v => {
                        const prod = this.productoVivo(v.id);
                        if (prod && v.tipo !== 'Servicio') {
                            prod.stock_cantidad = Math.max(0, prod.stock_cantidad - v.cantidad);
                        }
                    });

                    this.showToast('Venta realizada con éxito.', 'success');
                    this.carrito = [];
                    this.clienteId = '';
                    this.requiereFactura = false;
                    this.montoRecibido = '';

                    if (data.ticket_url) {
                        imprimirTicket(data.ticket_url);
                    }
                })
                .catch(() => {
                    this.procesando = false;
                    this.showToast('Error de conexión con el servidor.', 'error');
                });
            }
        };
    }
</script>
@endsection
