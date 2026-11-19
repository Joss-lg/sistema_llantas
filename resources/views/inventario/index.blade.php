@extends('layouts.app')

@section('header_title', 'Inventario y Catálogo')

@section('content')

<style>[x-cloak] { display: none !important; }</style>

@php
    // ---- Reglas de la lista ----
    $viendoSoloServicios = request('tipo') === 'Servicio';
    $nuevosHoy           = $productosNuevosHoy ?? [];
    $totalNuevos         = count($nuevosHoy);
    $porcentaje          = fn ($n) => $totalProductos > 0 ? round(($n / $totalProductos) * 100) : 0;

    $hayFiltros = collect(['q', 'stock_status', 'marca_filtro', 'ordenar_precio', 'sucursal_id', 'tipo', 'solo_nuevos'])
        ->contains(fn ($k) => request()->filled($k) && ! in_array(request($k), ['Todos', 'Todas'], true));

    // ---- Ventana "Registrar entrada": si falló la validación se vuelve a abrir con lo escrito ----
    $elegido = null;
    if (old('_form') === 'entrada' && old('producto_id')) {
        $elegido = collect($catalogo)->firstWhere('id', (int) old('producto_id'));
    }
    $deEntrada = fn ($campo) => old('_form') === 'entrada' ? old($campo) : '';

    $camposEntrada = [
        ['cantidad',       'Cantidad a ingresar', '1',    '1', 'Ej. 10'],
        ['costo_unitario', 'Costo unitario ($)',  '0.01', '0', '0.00'],
        ['precio_publico', 'Precio al público ($)', '0.01', '0', '0.00'],
        ['precio_mayoreo', 'Precio mayoreo ($)',  '0.01', '0', '0.00'],
    ];

    // ---- Clases que se repiten ----
    $etiqueta   = 'block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2';
    $campoBase  = 'w-full px-3 py-2.5 bg-gray-50 dark:bg-white/[0.03] border text-gray-900 dark:text-white text-sm font-medium rounded-xl outline-none placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500';
    $bordeOk    = 'border-gray-200 dark:border-white/10';
    $bordeError = 'border-red-400 dark:border-red-500/60';
    $chip       = 'px-3.5 py-1.5 rounded-full text-xs font-bold border transition-colors bg-white dark:bg-white/[0.03] border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-300 peer-checked:bg-indigo-600 peer-checked:border-indigo-600 peer-checked:text-white';
@endphp

<div class="max-w-7xl mx-auto space-y-6"
     x-data="{
        modalProducto: @js(old('_form') === 'producto'),
        modalEntrada: @js(old('_form') === 'entrada')
     }"
     @keydown.escape.window="modalProducto = false; modalEntrada = false">

    {{-- Avisos --}}
    @if(session('success'))
        <div class="flex items-start gap-3 p-4 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-xl text-sm font-medium">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-start gap-3 p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-xl text-sm font-medium">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Encabezado y botones --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Catálogo general de mercancía y su stock por sucursal.</p>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('inventario.historial') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Historial
            </a>

            <a href="{{ route('inventario.importar') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Importar
            </a>

            {{-- Exportar --}}
            <div class="relative z-30" x-data="{ abierto: false }">
                <button type="button" @click="abierto = !abierto" @click.away="abierto = false"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Exportar
                    <svg class="w-3.5 h-3.5" :class="abierto ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="abierto" x-cloak
                     class="absolute right-0 mt-2 w-52 bg-white dark:bg-[#15161A] rounded-2xl shadow-xl border border-gray-200 dark:border-white/10 overflow-hidden p-1.5 space-y-0.5">
                    <a href="{{ route('inventario.exportar.excel', request()->query()) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                        <span class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                        <span>
                            <span class="block text-sm font-bold text-gray-900 dark:text-white">Excel</span>
                            <span class="block text-[11px] text-gray-400">Hoja de cálculo</span>
                        </span>
                    </a>
                    <a href="{{ route('inventario.exportar.pdf', request()->query()) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                        <span class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </span>
                        <span>
                            <span class="block text-sm font-bold text-gray-900 dark:text-white">PDF</span>
                            <span class="block text-[11px] text-gray-400">Documento imprimible</span>
                        </span>
                    </a>
                </div>
            </div>

            <button type="button" @click="modalProducto = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-transparent border border-indigo-200 dark:border-indigo-500/30 text-indigo-600 dark:text-indigo-400 font-semibold text-sm rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo producto
            </button>

            <button type="button" @click="modalEntrada = true"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Registrar entrada
            </button>
        </div>
    </div>

    {{-- TARJETAS: los números son de TODO el filtro, no solo de la página --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Productos</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($totalProductos) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">en el catálogo</p>
        </div>

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">En stock</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 tabular-nums">{{ number_format($enStock) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500 tabular-nums">{{ $porcentaje($enStock) }}% del total</p>
        </div>

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Bajo mínimo</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-amber-600 dark:text-amber-400 tabular-nums">{{ number_format($bajoStock) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500 tabular-nums">{{ $porcentaje($bajoStock) }}% del total</p>
        </div>

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Sin stock</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-red-600 dark:text-red-400 tabular-nums">{{ number_format($sinStock) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500 tabular-nums">{{ $porcentaje($sinStock) }}% del total</p>
        </div>
    </div>
    <p class="-mt-3 text-xs text-gray-400 dark:text-gray-500">
        Las tarjetas cuentan todos los productos del filtro (sucursal, categoría, marca y búsqueda), no solo los de esta página.
        Stock de: {{ $sucursalNombre ?? 'todas las sucursales juntas' }}.
    </p>

    {{-- FILTROS --}}
    <form id="filter-form" method="GET" action="{{ route('inventario.index') }}"
          class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5 sm:p-6 space-y-5">

        @if(request('solo_nuevos') == '1')
            <input type="hidden" name="solo_nuevos" value="1">
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="filtro_q" class="{{ $etiqueta }}">Búsqueda</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                    <input type="text" id="filtro_q" name="q" value="{{ request('q') }}" placeholder="Medida, marca..."
                           class="{{ $campoBase }} {{ $bordeOk }} pl-9">
                </div>
            </div>

            <div>
                <label for="filtro_stock" class="{{ $etiqueta }}">Estado del stock</label>
                <select id="filtro_stock" name="stock_status" onchange="this.form.submit()"
                        class="{{ $campoBase }} {{ $bordeOk }} cursor-pointer">
                    <option value="">Todos los niveles</option>
                    <option value="sin_stock"  @selected(request('stock_status') === 'sin_stock')>Sin stock</option>
                    <option value="bajo_stock" @selected(request('stock_status') === 'bajo_stock')>Bajo mínimo</option>
                    <option value="ok"         @selected(request('stock_status') === 'ok')>En stock</option>
                </select>
            </div>

            <div>
                <label for="filtro_marca" class="{{ $etiqueta }}">Marca</label>
                <select id="filtro_marca" name="marca_filtro" onchange="this.form.submit()"
                        class="{{ $campoBase }} {{ $bordeOk }} cursor-pointer">
                    <option value="Todas">Todas las marcas</option>
                    @foreach($marcasDisponibles ?? [] as $m)
                        <option value="{{ $m }}" @selected(request('marca_filtro') === $m)>{{ $m }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filtro_orden" class="{{ $etiqueta }}">Ordenar</label>
                <select id="filtro_orden" name="ordenar_precio" onchange="this.form.submit()"
                        class="{{ $campoBase }} {{ $bordeOk }} cursor-pointer">
                    <option value="">Por defecto (marca y medida)</option>
                    <option value="publico_mayor" @selected(request('ordenar_precio') === 'publico_mayor')>P. Público: mayor a menor</option>
                    <option value="publico_menor" @selected(request('ordenar_precio') === 'publico_menor')>P. Público: menor a mayor</option>
                    <option value="mayoreo_mayor" @selected(request('ordenar_precio') === 'mayoreo_mayor')>P. Mayoreo: mayor a menor</option>
                    <option value="mayoreo_menor" @selected(request('ordenar_precio') === 'mayoreo_menor')>P. Mayoreo: menor a mayor</option>
                    <option value="costo_mayor"   @selected(request('ordenar_precio') === 'costo_mayor')>Costo: mayor a menor</option>
                    <option value="costo_menor"   @selected(request('ordenar_precio') === 'costo_menor')>Costo: menor a mayor</option>
                </select>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-white/10 flex flex-col lg:flex-row gap-5 lg:items-center">

            {{-- Sucursal: solo cuando la persona puede elegir entre varias --}}
            @if(($sucursales ?? collect())->count() > 1)
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mr-1">Sucursal</span>
                    <label class="relative block cursor-pointer">
                        <input type="radio" name="sucursal_id" value="" onchange="this.form.submit()" class="peer absolute inset-0 opacity-0 cursor-pointer z-10" {{ ! request()->filled('sucursal_id') ? 'checked' : '' }}>
                        <div class="{{ $chip }}">Todas</div>
                    </label>
                    @foreach($sucursales as $suc)
                        <label class="relative block cursor-pointer">
                            <input type="radio" name="sucursal_id" value="{{ $suc->id }}" onchange="this.form.submit()" class="peer absolute inset-0 opacity-0 cursor-pointer z-10" {{ request('sucursal_id') == $suc->id ? 'checked' : '' }}>
                            <div class="{{ $chip }}">{{ $suc->nombre }}</div>
                        </label>
                    @endforeach
                </div>
            @endif

            {{-- Categoría --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mr-1">Categoría</span>
                @foreach(array_merge(['Todos'], $tipos) as $tipo)
                    <label class="relative block cursor-pointer">
                        <input type="radio" name="tipo" value="{{ $tipo }}" onchange="this.form.submit()" class="peer absolute inset-0 opacity-0 cursor-pointer z-10" {{ request('tipo', 'Todos') == $tipo ? 'checked' : '' }}>
                        <div class="{{ $chip }}">{{ $tipo }}</div>
                    </label>
                @endforeach

                <a href="{{ request()->fullUrlWithQuery(['solo_nuevos' => request('solo_nuevos') == '1' ? null : '1', 'page' => null]) }}"
                   class="px-3.5 py-1.5 rounded-full text-xs font-bold border transition-colors inline-flex items-center gap-1.5
                   {{ request('solo_nuevos') == '1' ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-indigo-50 dark:bg-indigo-500/10 border-indigo-200 dark:border-indigo-500/30 text-indigo-600 dark:text-indigo-400' }}">
                    Llegó hoy
                    @if($totalNuevos > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black tabular-nums {{ request('solo_nuevos') == '1' ? 'bg-white text-indigo-600' : 'bg-indigo-600 text-white' }}">{{ $totalNuevos }}</span>
                    @endif
                </a>
            </div>

            <div class="flex gap-3 lg:ml-auto">
                @if($hayFiltros)
                    <a href="{{ route('inventario.index') }}"
                       class="text-center px-5 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                        Limpiar
                    </a>
                @endif
                <button type="submit"
                        class="inline-flex items-center justify-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors">
                    Buscar
                </button>
            </div>
        </div>
    </form>

    {{-- TABLA DE PRODUCTOS --}}
    <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/[0.03] text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-white/10">
                        @if($viendoSoloServicios)
                            <th class="px-5 py-4 font-bold whitespace-nowrap">Servicio de taller</th>
                            <th class="px-5 py-4 font-bold whitespace-nowrap text-right">Precio al público</th>
                            <th class="px-5 py-4 font-bold whitespace-nowrap text-center">Estado</th>
                        @else
                            <th class="px-5 py-4 font-bold whitespace-nowrap">Producto</th>
                            <th class="px-5 py-4 font-bold whitespace-nowrap">Medida</th>
                            <th class="px-5 py-4 font-bold whitespace-nowrap text-center">Stock</th>
                            <th class="px-5 py-4 font-bold whitespace-nowrap text-right">Costo</th>
                            <th class="px-5 py-4 font-bold whitespace-nowrap text-right">P. Público</th>
                            <th class="px-5 py-4 font-bold whitespace-nowrap text-right">P. Mayoreo</th>
                            <th class="px-5 py-4 font-bold whitespace-nowrap text-center">Estado</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700 dark:text-gray-300 divide-y divide-gray-100 dark:divide-white/10">
                    @forelse($productos as $producto)
                        @php
                            $st      = (int) ($producto->stock_cantidad ?? 0);
                            $min     = (int) ($producto->stock_minimo ?? 5);
                            $esNuevo = in_array($producto->id, $nuevosHoy);
                            $nombre  = trim((string) $producto->descripcion) !== ''
                                ? $producto->descripcion
                                : trim($producto->marca . ' ' . $producto->medida);

                            // Misma regla que las tarjetas y el filtro
                            if ($st <= 0) {
                                $nivel = ['pastilla' => 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border-red-200 dark:border-red-500/20', 'punto' => 'bg-red-500', 'texto' => 'Agotado'];
                            } elseif ($st < $min) {
                                $nivel = ['pastilla' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/20', 'punto' => 'bg-amber-500', 'texto' => 'Bajo stock'];
                            } else {
                                $nivel = ['pastilla' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20', 'punto' => 'bg-emerald-500', 'texto' => 'En stock'];
                            }
                        @endphp

                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors {{ $esNuevo ? 'bg-indigo-50/40 dark:bg-indigo-500/5' : '' }}">
                            @if($viendoSoloServicios)
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </span>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $nombre }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap font-extrabold text-gray-900 dark:text-white tabular-nums">${{ number_format($producto->precio_publico, 2) }}</td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>Disponible
                                    </span>
                                </td>
                            @else
                                <td class="px-5 py-4">
                                    <p class="font-bold text-gray-900 dark:text-white">
                                        {{ $nombre }}
                                        @if($esNuevo)
                                            <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20">Llegó hoy</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $producto->tipo }}@if(! empty($producto->marca) && $producto->marca !== $nombre) · {{ $producto->marca }}@endif
                                    </p>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">{{ $producto->medida ?: '—' }}</td>
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center min-w-[3.5rem] px-2.5 py-1 rounded-lg text-sm font-extrabold border tabular-nums {{ $nivel['pastilla'] }}">{{ $st }} uds</span>
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap tabular-nums">${{ number_format($producto->costo, 2) }}</td>
                                <td class="px-5 py-4 text-right whitespace-nowrap font-extrabold text-gray-900 dark:text-white tabular-nums">${{ number_format($producto->precio_publico, 2) }}</td>
                                <td class="px-5 py-4 text-right whitespace-nowrap font-semibold text-indigo-600 dark:text-indigo-400 tabular-nums">${{ number_format($producto->precio_mayoreo, 2) }}</td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border whitespace-nowrap {{ $nivel['pastilla'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $nivel['punto'] }}"></span>{{ $nivel['texto'] }}
                                    </span>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $viendoSoloServicios ? 3 : 7 }}" class="px-6 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 mb-4">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </div>
                                <p class="text-base font-bold text-gray-800 dark:text-gray-200">Sin productos</p>
                                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 max-w-sm mx-auto">
                                    @if($hayFiltros)
                                        Ningún producto coincide con los filtros. Prueba quitando alguno.
                                    @else
                                        Aún no hay productos en el catálogo.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($productos->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-white/10">
                {{ $productos->links() }}
            </div>
        @endif
    </div>

    {{-- ===================== VENTANA: NUEVO PRODUCTO ===================== --}}
    <div x-show="modalProducto" x-cloak
         class="fixed inset-0 !mt-0 z-50 overflow-y-auto bg-gray-900/50 flex items-start sm:items-center justify-center p-4">
        <div @click.away="modalProducto = false"
             class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-xl max-w-lg w-full my-8">

            <div class="px-6 py-5 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Registrar nuevo producto</h3>
                <button type="button" @click="modalProducto = false"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-full text-gray-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 transition-colors" title="Cerrar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('inventario.storeProducto') }}" method="POST" class="p-6 space-y-5" x-data="{ enviando: false }" @submit="enviando = true">
                @csrf
                <input type="hidden" name="_form" value="producto">

                @php $deProducto = fn ($campo) => old('_form') === 'producto' ? old($campo) : ''; @endphp

                <div>
                    <label for="nuevo_tipo" class="{{ $etiqueta }}">Tipo de artículo *</label>
                    <select id="nuevo_tipo" name="tipo" required
                            class="{{ $campoBase }} {{ $errors->has('tipo') ? $bordeError : $bordeOk }} cursor-pointer">
                        <option value="">Elige un tipo</option>
                        @foreach($tipos as $t)
                            <option value="{{ $t }}" @selected($deProducto('tipo') === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                    @error('tipo') <p class="mt-1.5 text-xs font-semibold text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nuevo_marca" class="{{ $etiqueta }}">Marca *</label>
                        <input type="text" id="nuevo_marca" name="marca" value="{{ $deProducto('marca') }}" placeholder="Ej. Firestone" required
                               class="{{ $campoBase }} {{ $errors->has('marca') ? $bordeError : $bordeOk }}">
                        @error('marca') <p class="mt-1.5 text-xs font-semibold text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="nuevo_medida" class="{{ $etiqueta }}">Medida *</label>
                        <input type="text" id="nuevo_medida" name="medida" value="{{ $deProducto('medida') }}" placeholder="Ej. 11R-24.5" required
                               class="{{ $campoBase }} {{ $errors->has('medida') ? $bordeError : $bordeOk }}">
                        @error('medida') <p class="mt-1.5 text-xs font-semibold text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="nuevo_descripcion" class="{{ $etiqueta }}">Descripción completa</label>
                    <textarea id="nuevo_descripcion" name="descripcion" rows="3" placeholder="Descripción detallada del producto..."
                              class="{{ $campoBase }} {{ $errors->has('descripcion') ? $bordeError : $bordeOk }} resize-none">{{ $deProducto('descripcion') }}</textarea>
                    @error('descripcion') <p class="mt-1.5 text-xs font-semibold text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">La marca y la medida se guardan en mayúsculas. El producto nace con precio y stock en 0 en todas las sucursales.</p>
                </div>

                <div class="flex justify-end gap-3 pt-5 border-t border-gray-100 dark:border-white/10">
                    <button type="button" @click="modalProducto = false"
                            class="px-5 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">Cancelar</button>
                    <button type="submit" :disabled="enviando"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl transition-colors">Guardar producto</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== VENTANA: REGISTRAR ENTRADA ===================== --}}
    <div x-show="modalEntrada" x-cloak
         class="fixed inset-0 !mt-0 z-50 overflow-y-auto bg-gray-900/50 flex items-start sm:items-center justify-center p-4">
        <div @click.away="modalEntrada = false"
             class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-xl max-w-lg w-full my-8">

            <div class="px-6 py-5 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Registrar entrada de stock</h3>
                <button type="button" @click="modalEntrada = false"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-full text-gray-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 transition-colors" title="Cerrar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('inventario.storeEntrada') }}" method="POST" class="p-6 space-y-5" x-data="{ enviando: false }" @submit="enviando = true">
                @csrf
                <input type="hidden" name="_form" value="entrada">

                @if(isset($sucursales) && count($sucursales) > 1)
                    <div>
                        <label for="entrada_sucursal" class="{{ $etiqueta }}">Sucursal que recibe</label>
                        <select id="entrada_sucursal" name="sucursal_id" required
                                class="{{ $campoBase }} {{ $errors->has('sucursal_id') ? $bordeError : $bordeOk }} cursor-pointer">
                            @foreach($sucursales as $suc)
                                <option value="{{ $suc->id }}" @selected(($deEntrada('sucursal_id') ?: (request('sucursal_id') ?? auth()->user()->sucursal_id)) == $suc->id)>{{ $suc->nombre }}</option>
                            @endforeach
                        </select>
                        @error('sucursal_id') <p class="mt-1.5 text-xs font-semibold text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                @endif

                {{-- Producto: se busca por marca, medida o nombre --}}
                <div class="relative"
                     x-data="{
                        catalogo: @js($catalogo),
                        buscar: '',
                        abierto: false,
                        elegidoId: @js($elegido['id'] ?? ''),
                        elegidoTexto: @js($elegido['t'] ?? ''),
                        resultados() {
                            const palabras = this.buscar.toLowerCase().trim().split(/\s+/).filter(Boolean);
                            const lista = palabras.length ? this.catalogo.filter(p => palabras.every(w => p.b.includes(w))) : this.catalogo;
                            return lista.slice(0, 8);
                        },
                        elegir(p) { this.elegidoId = p.id; this.elegidoTexto = p.t; this.buscar = ''; this.abierto = false; }
                     }"
                     @click.away="abierto = false">
                    <label class="{{ $etiqueta }}">Producto *</label>
                    <input type="hidden" name="producto_id" :value="elegidoId">

                    <div x-show="elegidoId" x-cloak
                         class="flex items-center justify-between gap-3 px-3 py-2.5 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 rounded-xl">
                        <span class="text-sm font-bold text-indigo-700 dark:text-indigo-300" x-text="elegidoTexto"></span>
                        <button type="button" @click="elegidoId = ''; elegidoTexto = ''; $nextTick(() => $refs.buscador.focus())"
                                class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline shrink-0">Cambiar</button>
                    </div>

                    <div x-show="! elegidoId">
                        <input type="text" x-ref="buscador" x-model="buscar" autocomplete="off"
                               placeholder="Busca por marca, medida o nombre..."
                               @focus="abierto = true" @input="abierto = true"
                               @keydown.enter.prevent="if (resultados().length === 1) elegir(resultados()[0])"
                               class="{{ $campoBase }} {{ $errors->has('producto_id') ? $bordeError : $bordeOk }}">

                        <div x-show="abierto" x-cloak
                             class="absolute z-10 left-0 right-0 mt-1.5 py-1 bg-white dark:bg-[#1a1b20] border border-gray-200 dark:border-white/10 rounded-xl shadow-xl max-h-64 overflow-y-auto">
                            <template x-for="p in resultados()" :key="p.id">
                                <button type="button" @click="elegir(p)" x-text="p.t"
                                        class="block w-full text-left px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5"></button>
                            </template>
                            <p x-show="resultados().length === 0" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">Ningún producto coincide.</p>
                        </div>
                    </div>
                    @error('producto_id') <p class="mt-1.5 text-xs font-semibold text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($camposEntrada as [$campo, $texto, $paso, $minimo, $ejemplo])
                        <div>
                            <label for="entrada_{{ $campo }}" class="{{ $etiqueta }}">{{ $texto }}{{ in_array($campo, ['cantidad', 'costo_unitario']) ? ' *' : '' }}</label>
                            <input type="number" id="entrada_{{ $campo }}" name="{{ $campo }}" step="{{ $paso }}" min="{{ $minimo }}"
                                   value="{{ $deEntrada($campo) }}" placeholder="{{ $ejemplo }}"
                                   {{ in_array($campo, ['cantidad', 'costo_unitario']) ? 'required' : '' }}
                                   class="{{ $campoBase }} {{ $errors->has($campo) ? $bordeError : $bordeOk }}">
                            @error($campo) <p class="mt-1.5 text-xs font-semibold text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400 dark:text-gray-500">Si dejas un precio vacío, el producto conserva el que ya tenía.</p>

                <div class="flex justify-end gap-3 pt-5 border-t border-gray-100 dark:border-white/10">
                    <button type="button" @click="modalEntrada = false"
                            class="px-5 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">Cancelar</button>
                    <button type="submit" :disabled="enviando"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl transition-colors">Confirmar entrada</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
