@extends('layouts.app')

@section('header_title', 'Inventario y Catálogo')

@section('content')

<!-- Estilos para las animaciones y eliminación total de scrollbars -->
<style>
    /* Ocultar completamente todas las barras de desplazamiento */
    *::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
    * {
        -ms-overflow-style: none !important;  /* IE y Edge */
        scrollbar-width: none !important;  /* Firefox */
    }

    /* Animación para las alertas (Toast) */
    @keyframes slideInRight {
        0% { transform: translateX(100%) scale(0.9); opacity: 0; }
        100% { transform: translateX(0) scale(1); opacity: 1; }
    }
    .anim-toast {
        animation: slideInRight 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    /* Animación en cascada para las filas de la tabla */
    @keyframes fadeInUp {
        0% { transform: translateY(15px); opacity: 0; }
        100% { transform: translateY(0); opacity: 1; }
    }
    .row-anim {
        opacity: 0; 
        animation: fadeInUp 0.5s ease-out forwards;
    }

    /* Animación de pulso suave para los badges de "NUEVO" */
    @keyframes pulseSoft {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.08); opacity: 0.8; }
    }
    .anim-pulse-soft {
        animation: pulseSoft 2s infinite ease-in-out;
    }

    /* Efecto "Levantar y Hundir" para botones al pasar el ratón o tocar */
    .btn-pop {
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        will-change: transform, box-shadow;
    }
    .btn-pop:hover, .btn-pop:focus {
        transform: translateY(-4px) scale(1.02);
    }
    .btn-pop:active {
        transform: translateY(2px) scale(0.95);
    }

    /* Animación de entrada para el modal */
    @keyframes modalPop {
        0% { transform: scale(0.95) translateY(10px); opacity: 0; }
        100% { transform: scale(1) translateY(0); opacity: 1; }
    }
    .modal-pop {
        animation: modalPop 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    
    /* Efecto fade para el fondo oscuro del modal */
    @keyframes fadeBg {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }
    .modal-overlay-anim {
        animation: fadeBg 0.3s ease-out forwards;
    }
</style>

<!-- Contenedor principal con animación Alpine -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10" x-data="{ cargado: false }" x-init="setTimeout(() => cargado = true, 50)">

    {{-- Alertas (Toasts) --}}
    @if(session('success'))
        <div class="fixed top-6 right-6 z-[100] bg-white/80 dark:bg-emerald-950/80 backdrop-blur-xl border border-emerald-100 dark:border-emerald-900/50 text-emerald-800 dark:text-emerald-400 px-5 py-4 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] flex items-center gap-3 anim-toast transition-all duration-500 hover:scale-105">
            <div class="bg-emerald-100 dark:bg-emerald-900/50 p-1.5 rounded-full">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <span class="text-sm font-semibold tracking-wide">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="fixed top-6 right-6 z-[100] bg-white/80 dark:bg-red-950/80 backdrop-blur-xl border border-red-100 dark:border-red-900/50 text-red-800 dark:text-red-400 px-5 py-4 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] flex items-center gap-3 anim-toast transition-all duration-500 hover:scale-105">
            <div class="bg-red-100 dark:bg-red-900/50 p-1.5 rounded-full">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </div>
            <span class="text-sm font-semibold tracking-wide">{{ session('error') }}</span>
        </div>
    @endif

    {{-- 1. Encabezado y Botones --}}
    <div class="relative z-[60] flex flex-col md:flex-row justify-between items-start md:items-center gap-6 transform transition-all duration-700 ease-out"
         :class="cargado ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 translate-y-8 scale-95'">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Inventario</h2>
            <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1.5 font-medium">Gestión avanzada del catálogo general de mercancía.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <a href="{{ route('inventario.importar') }}" class="btn-pop inline-flex items-center gap-2 px-5 py-2.5 bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-200 rounded-xl text-sm font-semibold hover:bg-gray-50 dark:hover:bg-white/5 hover:shadow-lg dark:hover:shadow-white/5 shadow-sm">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Importar
            </a>

            <button onclick="openModal('modal-producto')" class="btn-pop inline-flex items-center gap-2 px-5 py-2.5 bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-200 rounded-xl text-sm font-semibold hover:bg-gray-50 dark:hover:bg-white/5 hover:shadow-lg dark:hover:shadow-white/5 shadow-sm">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo producto
            </button>

            <button onclick="openModal('modal-entrada')" class="btn-pop inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-500 shadow-[0_4px_15px_rgba(16,185,129,0.3)] hover:shadow-[0_8px_30px_rgba(16,185,129,0.6)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Registrar entrada
            </button>

            <span class="hidden md:block w-px h-8 bg-gray-200 dark:bg-neutral-800 mx-2"></span>

            <div class="relative" x-data="{ abierto: false }">
                <button @click="abierto = !abierto" @click.away="abierto = false"
                        class="btn-pop inline-flex items-center gap-2 px-5 py-2.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-xl text-sm font-bold hover:bg-gray-800 dark:hover:bg-gray-100 shadow-[0_4px_15px_rgba(0,0,0,0.15)] hover:shadow-[0_8px_25px_rgba(0,0,0,0.3)] dark:hover:shadow-[0_8px_25px_rgba(255,255,255,0.4)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Exportar
                    <svg class="w-4 h-4 transition-transform duration-300" :class="abierto ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="abierto" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                     class="absolute right-0 mt-3 w-56 bg-white/95 dark:bg-[#1a1a1a]/95 backdrop-blur-xl rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] dark:shadow-[0_10px_40px_rgba(0,0,0,0.5)] border border-gray-100 dark:border-white/10 overflow-hidden z-50 origin-top-right">
                    
                    <div class="p-2 space-y-1">
                        <a href="{{ route('inventario.exportar.excel', request()->query()) }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm hover:bg-emerald-50 dark:hover:bg-white/5 transition-all duration-200 group">
                            <span class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            <div>
                                <p class="font-bold text-gray-900 dark:text-white">Excel</p>
                                <p class="text-[11px] text-gray-500 font-medium">Hoja de cálculo</p>
                            </div>
                        </a>
                        <a href="{{ route('inventario.exportar.pdf', request()->query()) }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm hover:bg-red-50 dark:hover:bg-white/5 transition-all duration-200 group">
                            <span class="w-9 h-9 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </span>
                            <div>
                                <p class="font-bold text-gray-900 dark:text-white">PDF</p>
                                <p class="text-[11px] text-gray-500 font-medium">Documento imprimible</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Formulario de Filtros --}}
    <form id="filter-form" method="GET" action="{{ route('inventario.index') }}" 
          class="relative z-10 bg-white/60 dark:bg-[#111111]/60 backdrop-blur-2xl p-6 sm:p-8 rounded-3xl border border-gray-100/80 dark:border-white/5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] space-y-6 transform transition-all duration-700 ease-out delay-100"
          :class="cargado ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 translate-y-10 scale-95'">

        @if(request('solo_nuevos') == '1')
            <input type="hidden" name="solo_nuevos" value="1">
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-5 items-end">
            <!-- Buscador -->
            <div class="lg:col-span-3">
                <label class="block text-[10px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-widest mb-2.5">Búsqueda Inteligente</label>
                <div class="relative group">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Medida, marca..." class="bg-gray-50/50 dark:bg-black/50 border border-gray-200/80 dark:border-white/10 text-gray-900 dark:text-white placeholder-gray-400 text-[13px] font-medium rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 block w-full pl-4 pr-10 py-3 outline-none transition-all duration-300">
                    <svg class="w-4 h-4 text-gray-400 absolute right-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- Nuevo Menú Desplegable Animado: Estado del Stock -->
            <div class="lg:col-span-3">
                <label class="block text-[10px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-widest mb-2.5">Estado del Stock</label>
                <div x-data="{
                        abierto: false,
                        opciones: [
                            { value: '', label: 'Todos los niveles' },
                            { value: 'sin_stock', label: 'Sin Stock (0 uds)' },
                            { value: 'bajo_stock', label: 'Bajo Mínimo' },
                            { value: 'ok', label: 'Stock OK' }
                        ],
                        seleccionado: '{{ request('stock_status', '') }}'
                    }" class="relative">
                    
                    <input type="hidden" name="stock_status" :value="seleccionado">
                    
                    <button type="button" @click="abierto = !abierto" @click.away="abierto = false" 
                            class="bg-gray-50/50 dark:bg-black/50 border border-gray-200/80 dark:border-white/10 text-gray-900 dark:text-white text-[13px] font-medium rounded-xl flex justify-between items-center w-full px-4 py-3 outline-none transition-all duration-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        <span x-text="opciones.find(o => o.value === seleccionado)?.label || 'Todos los niveles'"></span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-300" :class="abierto ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="abierto" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                         class="absolute z-50 w-full mt-2 py-1.5 bg-white dark:bg-[#1a1a1a] border border-gray-100 dark:border-white/10 rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] dark:shadow-[0_10px_40px_rgba(0,0,0,0.5)] overflow-hidden">
                        <template x-for="opcion in opciones" :key="opcion.value">
                            <div @click="seleccionado = opcion.value; abierto = false; $nextTick(() => document.getElementById('filter-form').submit())"
                                 class="px-4 py-2.5 text-[13px] font-medium cursor-pointer transition-colors duration-200 flex items-center"
                                 :class="seleccionado === opcion.value ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5'">
                                <span x-text="opcion.label"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Nuevo Menú Desplegable Animado: Filtrar por Marca -->
            <div class="lg:col-span-3">
                <label class="block text-[10px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-widest mb-2.5">Filtrar por Marca</label>
                <div x-data="{
                        abierto: false,
                        opciones: [
                            { value: 'Todas', label: 'Todas las marcas' },
                            @foreach($marcasDisponibles ?? [] as $m)
                            { value: '{{ $m }}', label: '{{ $m }}' },
                            @endforeach
                        ],
                        seleccionado: '{{ request('marca_filtro', 'Todas') }}'
                    }" class="relative">
                    
                    <input type="hidden" name="marca_filtro" :value="seleccionado">
                    
                    <button type="button" @click="abierto = !abierto" @click.away="abierto = false" 
                            class="bg-gray-50/50 dark:bg-black/50 border border-gray-200/80 dark:border-white/10 text-gray-900 dark:text-white text-[13px] font-medium rounded-xl flex justify-between items-center w-full px-4 py-3 outline-none transition-all duration-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        <span x-text="opciones.find(o => o.value === seleccionado)?.label || 'Todas las marcas'"></span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-300" :class="abierto ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="abierto" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                         class="absolute z-50 w-full mt-2 py-1.5 max-h-60 overflow-y-auto bg-white dark:bg-[#1a1a1a] border border-gray-100 dark:border-white/10 rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] dark:shadow-[0_10px_40px_rgba(0,0,0,0.5)]">
                        <template x-for="opcion in opciones" :key="opcion.value">
                            <div @click="seleccionado = opcion.value; abierto = false; $nextTick(() => document.getElementById('filter-form').submit())"
                                 class="px-4 py-2.5 text-[13px] font-medium cursor-pointer transition-colors duration-200"
                                 :class="seleccionado === opcion.value ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5'">
                                <span x-text="opcion.label"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Nuevo Menú Desplegable Animado: Ordenar -->
            <div class="lg:col-span-3">
                <label class="block text-[10px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-widest mb-2.5">Ordenar</label>
                <div x-data="{
                        abierto: false,
                        opciones: [
                            { value: '', label: 'Por Defecto' },
                            { value: 'publico_mayor', label: 'P. Público: Mayor a Menor' },
                            { value: 'publico_menor', label: 'P. Público: Menor a Mayor' },
                            { value: 'mayoreo_mayor', label: 'P. Mayoreo: Mayor a Menor' },
                            { value: 'mayoreo_menor', label: 'P. Mayoreo: Menor a Mayor' },
                            { value: 'costo_mayor', label: 'Costo: Mayor a Menor' },
                            { value: 'costo_menor', label: 'Costo: Menor a Mayor' }
                        ],
                        seleccionado: '{{ request('ordenar_precio', '') }}'
                    }" class="relative">
                    
                    <input type="hidden" name="ordenar_precio" :value="seleccionado">
                    
                    <button type="button" @click="abierto = !abierto" @click.away="abierto = false" 
                            class="bg-gray-50/50 dark:bg-black/50 border border-gray-200/80 dark:border-white/10 text-gray-900 dark:text-white text-[13px] font-medium rounded-xl flex justify-between items-center w-full px-4 py-3 outline-none transition-all duration-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        <span x-text="opciones.find(o => o.value === seleccionado)?.label || 'Por Defecto'"></span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-300" :class="abierto ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="abierto" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                         class="absolute z-50 w-full mt-2 py-1.5 bg-white dark:bg-[#1a1a1a] border border-gray-100 dark:border-white/10 rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] dark:shadow-[0_10px_40px_rgba(0,0,0,0.5)] overflow-hidden">
                        <template x-for="opcion in opciones" :key="opcion.value">
                            <div @click="seleccionado = opcion.value; abierto = false; $nextTick(() => document.getElementById('filter-form').submit())"
                                 class="px-4 py-2.5 text-[13px] font-medium cursor-pointer transition-colors duration-200"
                                 :class="seleccionado === opcion.value ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5'">
                                <span x-text="opcion.label"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <hr class="border-gray-100/50 dark:border-white/5">

        <div class="flex flex-col md:flex-row gap-6 md:items-center">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mr-2">Sucursal:</span>
                <label class="relative block cursor-pointer group">
                    <input type="radio" name="sucursal_id" value="" onchange="this.form.submit()" class="peer absolute inset-0 opacity-0 cursor-pointer z-10" {{ !request()->filled('sucursal_id') ? 'checked' : '' }}>
                    <div class="chip px-4 py-1.5 rounded-full text-xs font-semibold border transition-all duration-300 peer-checked:bg-gray-900 peer-checked:border-gray-900 peer-checked:text-white dark:peer-checked:bg-white dark:peer-checked:border-white dark:peer-checked:text-black bg-white dark:bg-black/50 border-gray-200/80 dark:border-white/10 text-gray-600 dark:text-gray-400 shadow-sm">Todas</div>
                </label>
                @foreach($sucursales ?? [] as $suc)
                    <label class="relative block cursor-pointer group">
                        <input type="radio" name="sucursal_id" value="{{ $suc->id }}" onchange="this.form.submit()" class="peer absolute inset-0 opacity-0 cursor-pointer z-10" {{ request('sucursal_id') == $suc->id ? 'checked' : '' }}>
                        <div class="chip px-4 py-1.5 rounded-full text-xs font-semibold border transition-all duration-300 peer-checked:bg-gray-900 peer-checked:border-gray-900 peer-checked:text-white dark:peer-checked:bg-white dark:peer-checked:border-white dark:peer-checked:text-black bg-white dark:bg-black/50 border-gray-200/80 dark:border-white/10 text-gray-600 dark:text-gray-400 shadow-sm">{{ $suc->nombre }}</div>
                    </label>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mr-2">Categoría:</span>
                @foreach(['Todos', 'Llanta', 'Rin', 'Accesorio', 'Servicio'] as $tipo)
                    <label class="relative block cursor-pointer group">
                        <input type="radio" name="tipo" value="{{ $tipo }}" onchange="this.form.submit()" class="peer absolute inset-0 opacity-0 cursor-pointer z-10" {{ request('tipo', 'Todos') == $tipo ? 'checked' : '' }}>
                        <div class="chip px-4 py-1.5 rounded-full text-xs font-semibold border transition-all duration-300 peer-checked:bg-indigo-600 peer-checked:border-indigo-600 peer-checked:text-white peer-checked:shadow-indigo-500/20 peer-checked:shadow-md bg-white dark:bg-black/50 border-gray-200/80 dark:border-white/10 text-gray-600 dark:text-gray-400 shadow-sm">{{ $tipo }}</div>
                    </label>
                @endforeach

                @php $totalNuevos = count($productosNuevosHoy ?? []); @endphp
                <a href="{{ request('solo_nuevos') == '1' ? route('inventario.index') : route('inventario.index', ['solo_nuevos' => '1']) }}"
                   class="chip px-4 py-1.5 rounded-full text-xs font-semibold border transition-all duration-300 inline-flex items-center gap-1.5 shadow-sm hover:-translate-y-0.5
                   {{ request('solo_nuevos') == '1' ? 'bg-emerald-500 border-emerald-500 text-white shadow-emerald-500/20 shadow-md' : 'bg-emerald-50 dark:bg-emerald-900/10 border-emerald-200 dark:border-emerald-900/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/30' }}">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 16a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 7.323V16h2a1 1 0 110 2H7a1 1 0 110-2h2V7.323L6.237 8.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 16a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.616a1 1 0 01.894-1.79l1.599.8L9 4.323V3a1 1 0 011-1z"/></svg>
                    Llegó hoy
                    @if($totalNuevos > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-extrabold anim-pulse-soft {{ request('solo_nuevos') == '1' ? 'bg-white text-emerald-700' : 'bg-emerald-500 text-white' }}">{{ $totalNuevos }}</span>
                    @endif
                </a>
            </div>
        </div>

        @if(request()->hasAny(['sucursal_id', 'tipo', 'q', 'stock_status', 'marca_filtro', 'ordenar_precio', 'solo_nuevos']))
            <div class="flex flex-col sm:flex-row justify-between items-center pt-4 border-t border-gray-100/50 dark:border-white/5 gap-4">
                <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold rounded-xl text-xs hover:bg-gray-800 dark:hover:bg-gray-100 shadow-md hover:shadow-xl dark:hover:shadow-[0_8px_25px_rgba(255,255,255,0.4)] btn-pop">Aplicar Filtros</button>
                <a href="{{ route('inventario.index') }}" class="inline-flex items-center text-xs font-bold text-red-500 hover:text-red-600 dark:hover:text-red-400 transition-colors duration-300 uppercase tracking-wide">
                    Restablecer vista
                </a>
            </div>
        @endif
    </form>

    {{-- 3. Tabla de Productos --}}
    <div class="bg-white/80 dark:bg-[#111111]/80 backdrop-blur-2xl rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgba(0,0,0,0.5)] border border-gray-100/80 dark:border-white/5 flex flex-col transform transition-all duration-700 ease-out delay-200"
         :class="cargado ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 translate-y-12 scale-95'">
        
        <div class="overflow-x-auto overflow-y-hidden w-full rounded-t-3xl">
            @php
                $viendoSoloServicios = request('tipo') === 'Servicio';
                $nuevosHoy = $productosNuevosHoy ?? [];
            @endphp

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-black/30 text-gray-400 dark:text-neutral-500 text-[10px] uppercase tracking-widest border-b border-gray-100 dark:border-white/5">
                        @if($viendoSoloServicios)
                            <th class="px-6 py-5 font-bold whitespace-nowrap">Servicio de Taller</th>
                            <th class="px-6 py-5 font-bold whitespace-nowrap">Precio al Público</th>
                            <th class="px-6 py-5 font-bold text-center whitespace-nowrap">Estado</th>
                        @else
                            <th class="px-6 py-5 font-bold whitespace-nowrap">Producto</th>
                            <th class="px-6 py-5 font-bold whitespace-nowrap">Medida</th>
                            <th class="px-6 py-5 font-bold text-center whitespace-nowrap">Stock</th>
                            <th class="px-6 py-5 font-bold whitespace-nowrap">Costo Compra</th>
                            <th class="px-6 py-5 font-bold whitespace-nowrap">P. Público</th>
                            <th class="px-6 py-5 font-bold whitespace-nowrap">P. Mayoreo</th>
                            <th class="px-6 py-5 font-bold text-center whitespace-nowrap">Estado</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="text-[13px] text-gray-700 dark:text-gray-300 divide-y divide-gray-50/50 dark:divide-white/5">
                    @forelse($productos as $index => $producto)
                        @php
                            $st = $producto->stock_cantidad ?? 0;
                            $min = $producto->stock_minimo ?? 5;
                            $esServicio = $producto->tipo === 'Servicio';
                            $esNuevo = in_array($producto->id, $nuevosHoy);
                            $delayFila = min($index, 12) * 0.05; 
                        @endphp
                        <tr class="row-anim hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors duration-300 group {{ $esNuevo ? 'bg-emerald-50/30 dark:bg-emerald-900/10' : '' }}" style="animation-delay: {{ $delayFila }}s">
                            @if($viendoSoloServicios)
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-900 dark:text-white text-sm">{{ $producto->descripcion }}</p>
                                </td>
                                <td class="px-6 py-4 font-extrabold text-gray-900 dark:text-white text-sm whitespace-nowrap">
                                    ${{ number_format($producto->precio_publico, 2) }}
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300">Activo</span>
                                </td>
                            @else
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span class="inline-block px-2.5 py-0.5 {{ $esServicio ? 'bg-purple-50/80 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400' : 'bg-indigo-50/80 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400' }} text-[10px] rounded-full font-bold tracking-wider">
                                            {{ $producto->tipo }}
                                        </span>
                                        @if($esNuevo)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-emerald-500 text-white text-[10px] rounded-full font-bold tracking-wider anim-pulse-soft shadow-sm shadow-emerald-500/20">
                                                NUEVO
                                            </span>
                                        @endif
                                    </div>
                                    <p class="font-extrabold text-gray-900 dark:text-white text-[14px] leading-tight">
                                        {{ $esServicio ? $producto->descripcion : $producto->marca }}
                                    </p>
                                </td>

                                @if($esServicio)
                                    <td class="px-6 py-4"></td>
                                    <td class="px-6 py-4"></td>
                                    <td class="px-6 py-4"></td>
                                    <td class="px-6 py-4 font-extrabold text-gray-900 dark:text-white whitespace-nowrap">${{ number_format($producto->precio_publico, 2) }}</td>
                                    <td class="px-6 py-4"></td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300">Activo</span>
                                    </td>
                                @else
                                    <td class="px-6 py-4 font-mono text-xs font-semibold text-gray-500 dark:text-neutral-400 whitespace-nowrap">{{ $producto->medida }}</td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap w-24">
                                        <span class="w-14 h-8 flex items-center justify-center bg-gray-100/80 dark:bg-white/5 text-gray-900 dark:text-white rounded-lg text-sm font-extrabold mx-auto transition-colors duration-300 group-hover:bg-white dark:group-hover:bg-white/10 shadow-inner sm:shadow-none">{{ $st }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-400 dark:text-neutral-500 font-medium whitespace-nowrap">${{ number_format($producto->costo, 2) }}</td>
                                    <td class="px-6 py-4 font-extrabold text-gray-900 dark:text-white whitespace-nowrap">${{ number_format($producto->precio_publico, 2) }}</td>
                                    <td class="px-6 py-4 font-extrabold text-gray-600 dark:text-gray-400 whitespace-nowrap">${{ number_format($producto->precio_mayoreo, 2) }}</td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if($st == 0)
                                            <button type="button"
                                                    onclick="verDisponibilidad({{ $producto->id }}, '{{ addslashes($producto->marca . ' ' . $producto->medida) }}')"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-bold uppercase bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20 hover:scale-105 transition-all duration-300 cursor-pointer btn-pop">
                                                Agotado
                                            </button>
                                        @elseif($st < $min)
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-bold uppercase bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400">Bajo</span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-bold uppercase bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">OK</span>
                                        @endif
                                    </td>
                                @endif
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $viendoSoloServicios ? 3 : 7 }}" class="px-6 py-16 text-center text-gray-400 dark:text-neutral-500 font-medium row-anim">
                                @if(request('solo_nuevos') == '1')
                                    No llegó mercancía nueva hoy.
                                @else
                                    No se encontraron registros con estos filtros.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($productos->hasPages())
            <div class="p-6 border-t border-gray-100/50 dark:border-white/5 bg-gray-50/30 dark:bg-black/20 rounded-b-3xl">
                {{ $productos->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Modales --}}
<!-- Modal Entrada -->
<div id="modal-entrada" class="fixed inset-0 bg-gray-900/40 dark:bg-black/60 z-[100] hidden items-center justify-center p-4 backdrop-blur-md transition-all duration-300">
    <div class="modal-pop bg-white dark:bg-[#111] rounded-3xl p-8 w-full max-w-lg shadow-[0_20px_60px_rgba(0,0,0,0.15)] dark:shadow-[0_20px_60px_rgba(0,0,0,0.5)] border border-gray-100 dark:border-white/5">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-extrabold text-gray-900 dark:text-white tracking-tight">Registrar Entrada</h3>
            <button onclick="closeModal('modal-entrada')" class="btn-pop text-gray-400 hover:text-gray-900 dark:hover:text-white bg-gray-50 hover:bg-gray-100 dark:bg-white/5 dark:hover:bg-white/10 rounded-full p-2 transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('inventario.entrada.store') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-[11px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-widest mb-2">Producto</label>
                <div class="relative">
                    <select name="producto_id" class="w-full px-4 py-3 bg-gray-50 dark:bg-black/50 border border-gray-200/80 dark:border-white/10 text-gray-900 dark:text-white rounded-xl text-[13px] font-medium cursor-pointer focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all duration-300 appearance-none">
                        @foreach($productos as $p)
                            @if($p->tipo !== 'Servicio')
                                <!-- Se agrega clase para evitar el contraste feo en las opciones nativas del modal -->
                                <option class="bg-gray-50 dark:bg-[#1a1a1a] text-gray-900 dark:text-white" value="{{ $p->id }}">{{ $p->marca }} - {{ $p->medida }}</option>
                            @endif
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-widest mb-2">Cantidad</label>
                    <input type="number" name="cantidad" required class="w-full px-4 py-3 bg-gray-50 dark:bg-black/50 border border-gray-200/80 dark:border-white/10 text-gray-900 dark:text-white rounded-xl text-[13px] font-medium focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all duration-300">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-widest mb-2">Costo Unit. ($)</label>
                    <input type="number" name="costo_unitario" step="0.01" required class="w-full px-4 py-3 bg-gray-50 dark:bg-black/50 border border-gray-200/80 dark:border-white/10 text-gray-900 dark:text-white rounded-xl text-[13px] font-medium focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all duration-300">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-6">
                <button type="button" onclick="closeModal('modal-entrada')" class="btn-pop px-5 py-2.5 bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-200 rounded-xl text-sm font-semibold hover:bg-gray-50 dark:hover:bg-white/5 hover:shadow-lg transition-all duration-300">
                    Cancelar
                </button>
                <button type="submit" class="btn-pop px-6 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-500 transition-all duration-300 shadow-[0_4px_15px_rgba(16,185,129,0.3)] hover:shadow-[0_8px_30px_rgba(16,185,129,0.6)]">
                    Guardar Entrada
                </button>
            </div>
        </form>
    </div>
</div>
@endsection