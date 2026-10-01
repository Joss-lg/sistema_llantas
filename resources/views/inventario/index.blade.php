@extends('layouts.app')

@section('header_title', 'Inventario y Catálogo')

@section('content')

<style>
    *::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }
    * { -ms-overflow-style: none !important; scrollbar-width: none !important; }
    [x-cloak] { display: none !important; }
    .anim-toast { animation: slideInRight 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .row-anim { opacity: 0; animation: fadeInUp 0.5s ease-out forwards; }
    @keyframes fadeInUp { 0% { transform: translateY(15px); opacity: 0; } 100% { transform: translateY(0); opacity: 1; } }
    .btn-pop { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); will-change: transform, box-shadow; }
    .btn-pop:hover { transform: translateY(-4px) scale(1.02); }
    @keyframes modalPop { 0% { transform: scale(0.95) translateY(10px); opacity: 0; } 100% { transform: scale(1) translateY(0); opacity: 1; } }
    .modal-pop { animation: modalPop 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes fadeBg { 0% { opacity: 0; } 100% { opacity: 1; } }
    .modal-overlay-anim { animation: fadeBg 0.3s ease-out forwards; }
    @keyframes barGrow { from { width: 0; } to { width: var(--target-w); } }
    .bar-grow { animation: barGrow 1.2s cubic-bezier(0.16,1,0.3,1) forwards; animation-delay: 0.4s; }
    /* Borde lateral en hover — sin td extra que desplace columnas */
    tr.border-enstock:hover  { box-shadow: inset 3px 0 0 #34d399; }
    tr.border-bajstock:hover { box-shadow: inset 3px 0 0 #fbbf24; }
    tr.border-sinstock:hover { box-shadow: inset 3px 0 0 #f87171; }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8"
     x-data="{
        cargado: false,
        modalProducto: false,
        modalEntrada: false
     }"
     x-init="setTimeout(() => cargado = true, 50)">

    @if(session('success'))
        <div class="fixed top-6 right-6 z-[100] bg-white/80 dark:bg-emerald-950/80 backdrop-blur-xl border border-emerald-100 dark:border-emerald-900/50 text-emerald-800 dark:text-emerald-400 px-5 py-4 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] flex items-center gap-3 anim-toast">
            <div class="bg-emerald-100 dark:bg-emerald-900/50 p-1.5 rounded-full">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <span class="text-sm font-semibold tracking-wide">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="fixed top-6 right-6 z-[100] bg-white/80 dark:bg-red-950/80 backdrop-blur-xl border border-red-100 dark:border-red-900/50 text-red-800 dark:text-red-400 px-5 py-4 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] flex items-center gap-3 anim-toast">
            <div class="bg-red-100 dark:bg-red-900/50 p-1.5 rounded-full">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <span class="text-sm font-semibold tracking-wide">{{ session('error') }}</span>
        </div>
    @endif

    {{-- ============================
         ENCABEZADO Y BOTONES
    ============================= --}}
    <div class="relative z-[60] flex flex-col md:flex-row justify-between items-start md:items-center gap-6
                transition-all duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-6'">
        <div>
            <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Inventario</h2>
            <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1.5 font-medium">Gestión avanzada del catálogo general de mercancía.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <a href="{{ route('inventario.historial') }}"
               class="btn-pop inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-[#111] border border-gray-200 dark:border-neutral-800 text-gray-600 dark:text-neutral-300 rounded-xl text-sm font-semibold hover:border-[#818CF8]/40 hover:text-[#818CF8] transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Historial
            </a>

            <a href="{{ route('inventario.importar') }}"
               class="btn-pop inline-flex items-center gap-2 px-4 py-2.5 bg-[#818CF8]/10 dark:bg-[#818CF8]/15 border border-[#818CF8]/30 text-[#818CF8] rounded-xl text-sm font-semibold hover:bg-[#818CF8]/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Importar
            </a>

            <button @click="modalProducto = true"
                    class="btn-pop inline-flex items-center gap-2 px-4 py-2.5 bg-[#818CF8]/10 dark:bg-[#818CF8]/15 border border-[#818CF8]/30 text-[#818CF8] rounded-xl text-sm font-semibold hover:bg-[#818CF8]/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo producto
            </button>

            <button @click="modalEntrada = true"
                    class="btn-pop inline-flex items-center gap-2 px-5 py-2.5 bg-[#818CF8] hover:bg-[#6366F1] text-white rounded-xl text-sm font-bold shadow-[0_4px_14px_rgba(129,140,248,0.3)] hover:shadow-[0_6px_20px_rgba(129,140,248,0.45)] transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Registrar entrada
            </button>

            <span class="hidden md:block w-px h-8 bg-gray-200 dark:bg-neutral-800 mx-1"></span>

            {{-- Dropdown Exportar --}}
            <div class="relative" x-data="{ abierto: false }">
                <button @click="abierto = !abierto" @click.away="abierto = false"
                        class="btn-pop inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-[#818CF8] border border-gray-200 dark:border-transparent text-gray-700 dark:text-white rounded-xl text-sm font-bold shadow-sm dark:shadow-[0_4px_14px_rgba(129,140,248,0.25)] transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Exportar
                    <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="abierto ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="abierto" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     class="absolute right-0 mt-2.5 w-52 bg-white dark:bg-[#1a1a1a] rounded-2xl shadow-xl border border-gray-100 dark:border-neutral-800 overflow-hidden z-50 origin-top-right p-1.5 space-y-0.5">
                    <a href="{{ route('inventario.exportar.excel', request()->query()) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm hover:bg-[#818CF8]/8 group transition-colors">
                        <span class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-100 dark:border-emerald-800/30">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                        <div>
                            <p class="font-bold text-gray-900 dark:text-white text-xs">Excel</p>
                            <p class="text-[10px] text-gray-400 font-medium">Hoja de cálculo</p>
                        </div>
                    </a>
                    <a href="{{ route('inventario.exportar.pdf', request()->query()) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm hover:bg-[#818CF8]/8 group transition-colors">
                        <span class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400 flex items-center justify-center shrink-0 border border-red-100 dark:border-red-800/30">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </span>
                        <div>
                            <p class="font-bold text-gray-900 dark:text-white text-xs">PDF</p>
                            <p class="text-[10px] text-gray-400 font-medium">Documento imprimible</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================
         TARJETAS DE RESUMEN
    ============================= --}}
    @php
        $totalProds   = $productos->total() ?? 0;
        $sinStockN    = $sinStock    ?? 0;
        $bajoStockN   = $bajoStock   ?? 0;
        $enStockN     = $enStock     ?? max(0, $totalProds - $sinStockN - $bajoStockN);
        $pctSinStock  = $totalProds > 0 ? round(($sinStockN  / $totalProds) * 100) : 0;
        $pctBajoStock = $totalProds > 0 ? round(($bajoStockN / $totalProds) * 100) : 0;
        $pctEnStock   = $totalProds > 0 ? round(($enStockN   / $totalProds) * 100) : 0;
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4
                transition-all duration-700 delay-100 ease-[cubic-bezier(0.16,1,0.3,1)]"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'">

        {{-- Total --}}
        <div class="bg-white dark:bg-[#0c0c0c] rounded-2xl border border-gray-200 dark:border-neutral-800/60 shadow-sm dark:shadow-2xl p-5 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-[#818CF8]/10 text-[#818CF8] flex items-center justify-center border border-[#818CF8]/20">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <p class="text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-1">Total Productos</p>
            <p class="text-2xl font-black text-gray-900 dark:text-white">{{ number_format($totalProds) }}</p>
            <div class="mt-3 h-1 bg-gray-100 dark:bg-neutral-800 rounded-full overflow-hidden">
                <div class="h-full bg-[#818CF8] rounded-full bar-grow" style="--target-w: 100%; width: 0;"></div>
            </div>
        </div>

        {{-- En Stock --}}
        <div class="bg-white dark:bg-[#0c0c0c] rounded-2xl border border-gray-200 dark:border-neutral-800/60 shadow-sm dark:shadow-2xl p-5 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-emerald-800/30">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/30 px-2 py-0.5 rounded-full">{{ $pctEnStock }}%</span>
            </div>
            <p class="text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-1">En Stock</p>
            <p class="text-2xl font-black text-emerald-700 dark:text-emerald-400">{{ number_format($enStockN) }}</p>
            <div class="mt-3 h-1 bg-gray-100 dark:bg-neutral-800 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full bar-grow" style="--target-w: {{ $pctEnStock }}%; width: 0;"></div>
            </div>
        </div>

        {{-- Bajo Stock --}}
        <div class="bg-white dark:bg-[#0c0c0c] rounded-2xl border border-gray-200 dark:border-neutral-800/60 shadow-sm dark:shadow-2xl p-5 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-100 dark:border-amber-800/30">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <span class="text-[10px] font-black text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800/30 px-2 py-0.5 rounded-full">{{ $pctBajoStock }}%</span>
            </div>
            <p class="text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-1">Bajo Mínimo</p>
            <p class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ number_format($bajoStockN) }}</p>
            <div class="mt-3 h-1 bg-gray-100 dark:bg-neutral-800 rounded-full overflow-hidden">
                <div class="h-full bg-amber-400 rounded-full bar-grow" style="--target-w: {{ $pctBajoStock }}%; width: 0;"></div>
            </div>
        </div>

        {{-- Sin Stock --}}
        <div class="bg-white dark:bg-[#0c0c0c] rounded-2xl border border-gray-200 dark:border-neutral-800/60 shadow-sm dark:shadow-2xl p-5 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400 flex items-center justify-center border border-red-100 dark:border-red-800/30">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
                <span class="text-[10px] font-black text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/30 px-2 py-0.5 rounded-full">{{ $pctSinStock }}%</span>
            </div>
            <p class="text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-1">Sin Stock</p>
            <p class="text-2xl font-black text-red-500 dark:text-red-400">{{ number_format($sinStockN) }}</p>
            <div class="mt-3 h-1 bg-gray-100 dark:bg-neutral-800 rounded-full overflow-hidden">
                <div class="h-full bg-red-400 rounded-full bar-grow" style="--target-w: {{ $pctSinStock }}%; width: 0;"></div>
            </div>
        </div>
    </div>

    {{-- ============================
         MODALES
    ============================= --}}
    {{-- Modal Nuevo Producto --}}
    <div x-show="modalProducto" x-cloak
         class="fixed inset-0 !mt-0 z-[80] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm modal-overlay-anim">
        <div @click.away="modalProducto = false"
             class="bg-white dark:bg-[#151515] rounded-3xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 sm:p-8 shadow-2xl border border-gray-100 dark:border-neutral-800 modal-pop">

            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-neutral-800 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#818CF8]/10 text-[#818CF8] flex items-center justify-center border border-[#818CF8]/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">Registrar Nuevo Producto</h3>
                </div>
                <button @click="modalProducto = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-neutral-800 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('inventario.storeProducto') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-1.5">Tipo de Artículo</label>
                    <input type="text" name="tipo" placeholder="Ej. Llanta, Rin, Accesorio, Servicio" required
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-[#818CF8]/40 focus:border-[#818CF8] outline-none transition-all">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-1.5">Marca</label>
                        <input type="text" name="marca" placeholder="Ej. Firestone" required
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-[#818CF8]/40 focus:border-[#818CF8] outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-1.5">Medida</label>
                        <input type="text" name="medida" placeholder="Ej. 11R-24.5" required
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-[#818CF8]/40 focus:border-[#818CF8] outline-none transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-1.5">Descripción Completa</label>
                    <textarea name="descripcion" placeholder="Descripción detallada del producto..." rows="3"
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-[#818CF8]/40 focus:border-[#818CF8] outline-none transition-all resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-neutral-800">
                    <button type="button" @click="modalProducto = false"
                            class="px-5 py-2.5 text-sm font-semibold text-gray-500 dark:text-neutral-400 hover:text-gray-700 dark:hover:text-white transition-colors">Cancelar</button>
                    <button type="submit"
                            class="px-6 py-2.5 bg-[#818CF8] hover:bg-[#6366F1] text-white text-sm font-bold rounded-xl shadow-[0_4px_14px_rgba(129,140,248,0.3)] transition-all active:scale-95">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Registrar Entrada --}}
    <div x-show="modalEntrada" x-cloak
         class="fixed inset-0 !mt-0 z-[80] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm modal-overlay-anim">
        <div @click.away="modalEntrada = false"
             class="bg-white dark:bg-[#151515] rounded-3xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 sm:p-8 shadow-2xl border border-gray-100 dark:border-neutral-800 modal-pop">

            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-neutral-800 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-emerald-800/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">Registrar Entrada de Stock</h3>
                </div>
                <button @click="modalEntrada = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-neutral-800 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('inventario.storeEntrada') }}" method="POST" class="space-y-4">
                @csrf
                @if(isset($sucursales) && count($sucursales) > 1)
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-1.5">Sucursal que recibe</label>
                        <select name="sucursal_id" required
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-[#818CF8]/40 focus:border-[#818CF8] outline-none transition-all cursor-pointer">
                            @foreach($sucursales as $suc)
                                <option value="{{ $suc->id }}" {{ (request('sucursal_id') ?? auth()->user()->sucursal_id) == $suc->id ? 'selected' : '' }}>{{ $suc->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-1.5">Seleccionar Producto</label>
                    <select name="producto_id" required
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-[#818CF8]/40 focus:border-[#818CF8] outline-none transition-all cursor-pointer">
                        <option value="">-- Selecciona del catálogo --</option>
                        @foreach(\App\Models\Producto::orderBy('descripcion')->get() as $prod)
                            <option value="{{ $prod->id }}">{{ $prod->descripcion }} @if($prod->medida)({{ $prod->medida }})@endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-1.5">Cantidad a ingresar</label>
                        <input type="number" name="cantidad" min="1" placeholder="Ej. 10" required
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-[#818CF8]/40 focus:border-[#818CF8] outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-1.5">Costo Unitario ($)</label>
                        <input type="number" step="0.01" min="0" name="costo_unitario" placeholder="0.00" required
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-[#818CF8]/40 focus:border-[#818CF8] outline-none transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-1.5">Precio al Público ($)</label>
                        <input type="number" step="0.01" min="0" name="precio_publico" placeholder="0.00"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-[#818CF8]/40 focus:border-[#818CF8] outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-1.5">Precio Mayoreo ($)</label>
                        <input type="number" step="0.01" min="0" name="precio_mayoreo" placeholder="0.00"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-[#818CF8]/40 focus:border-[#818CF8] outline-none transition-all">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-neutral-800">
                    <button type="button" @click="modalEntrada = false"
                            class="px-5 py-2.5 text-sm font-semibold text-gray-500 dark:text-neutral-400 hover:text-gray-700 dark:hover:text-white transition-colors">Cancelar</button>
                    <button type="submit"
                            class="px-6 py-2.5 bg-[#818CF8] hover:bg-[#6366F1] text-white text-sm font-bold rounded-xl shadow-[0_4px_14px_rgba(129,140,248,0.3)] transition-all active:scale-95">Confirmar Entrada</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================
         FILTROS
    ============================= --}}
    <form id="filter-form" method="GET" action="{{ route('inventario.index') }}"
          class="bg-white dark:bg-[#0c0c0c] rounded-2xl border border-gray-200 dark:border-neutral-800/60 shadow-sm dark:shadow-2xl p-5 sm:p-6 space-y-5
                 transition-all duration-700 delay-200 ease-[cubic-bezier(0.16,1,0.3,1)]"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'">

        @if(request('solo_nuevos') == '1')
            <input type="hidden" name="solo_nuevos" value="1">
        @endif

        <p class="text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.2em]">Filtrar resultados</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            {{-- Búsqueda --}}
            <div>
                <label class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-2">Búsqueda</label>
                <div class="relative">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Medida, marca..."
                           class="bg-gray-50 dark:bg-[#141414] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-neutral-600 text-sm rounded-xl focus:ring-2 focus:ring-[#818CF8]/40 focus:border-[#818CF8] block w-full pl-4 pr-9 py-2.5 outline-none transition-all">
                    <svg class="w-4 h-4 text-gray-400 absolute right-3 top-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            {{-- Estado de Stock --}}
            <div x-data="{
                    abierto: false,
                    opciones: [
                        { value: '', label: 'Todos los niveles' },
                        { value: 'sin_stock', label: '🔴 Sin Stock' },
                        { value: 'bajo_stock', label: '🟡 Bajo Mínimo' },
                        { value: 'ok', label: '🟢 Stock OK' }
                    ],
                    seleccionado: '{{ request('stock_status', '') }}'
                }" class="relative">
                <label class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-2">Estado del Stock</label>
                <input type="hidden" name="stock_status" :value="seleccionado">
                <button type="button" @click="abierto = !abierto" @click.away="abierto = false"
                        class="bg-gray-50 dark:bg-[#141414] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white text-sm rounded-xl flex justify-between items-center w-full px-4 py-2.5 outline-none cursor-pointer transition-all focus:ring-2 focus:ring-[#818CF8]/40">
                    <span x-text="opciones.find(o => o.value === seleccionado)?.label || 'Todos los niveles'"></span>
                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="abierto ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="abierto" x-cloak x-transition
                     class="absolute z-50 w-full mt-1.5 py-1 bg-white dark:bg-[#1a1a1a] border border-gray-100 dark:border-neutral-800 rounded-xl shadow-xl overflow-hidden">
                    <template x-for="opcion in opciones" :key="opcion.value">
                        <div @click="seleccionado = opcion.value; abierto = false; $nextTick(() => document.getElementById('filter-form').submit())"
                             class="px-4 py-2.5 text-sm font-medium cursor-pointer transition-colors"
                             :class="seleccionado === opcion.value ? 'bg-[#818CF8]/10 text-[#818CF8] font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5'">
                            <span x-text="opcion.label"></span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Filtrar por Marca --}}
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
                <label class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-2">Marca</label>
                <input type="hidden" name="marca_filtro" :value="seleccionado">
                <button type="button" @click="abierto = !abierto" @click.away="abierto = false"
                        class="bg-gray-50 dark:bg-[#141414] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white text-sm rounded-xl flex justify-between items-center w-full px-4 py-2.5 outline-none cursor-pointer transition-all">
                    <span x-text="opciones.find(o => o.value === seleccionado)?.label || 'Todas las marcas'" class="truncate mr-2"></span>
                    <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" :class="abierto ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="abierto" x-cloak x-transition
                     class="absolute z-50 w-full mt-1.5 py-1 max-h-52 overflow-y-auto bg-white dark:bg-[#1a1a1a] border border-gray-100 dark:border-neutral-800 rounded-xl shadow-xl">
                    <template x-for="opcion in opciones" :key="opcion.value">
                        <div @click="seleccionado = opcion.value; abierto = false; $nextTick(() => document.getElementById('filter-form').submit())"
                             class="px-4 py-2.5 text-sm font-medium cursor-pointer transition-colors"
                             :class="seleccionado === opcion.value ? 'bg-[#818CF8]/10 text-[#818CF8] font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5'">
                            <span x-text="opcion.label"></span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Ordenar --}}
            <div x-data="{
                    abierto: false,
                    opciones: [
                        { value: '', label: 'Por Defecto' },
                        { value: 'publico_mayor', label: 'P. Público: Mayor → Menor' },
                        { value: 'publico_menor', label: 'P. Público: Menor → Mayor' },
                        { value: 'mayoreo_mayor', label: 'P. Mayoreo: Mayor → Menor' },
                        { value: 'mayoreo_menor', label: 'P. Mayoreo: Menor → Mayor' },
                        { value: 'costo_mayor', label: 'Costo: Mayor → Menor' },
                        { value: 'costo_menor', label: 'Costo: Menor → Mayor' }
                    ],
                    seleccionado: '{{ request('ordenar_precio', '') }}'
                }" class="relative">
                <label class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-2">Ordenar</label>
                <input type="hidden" name="ordenar_precio" :value="seleccionado">
                <button type="button" @click="abierto = !abierto" @click.away="abierto = false"
                        class="bg-gray-50 dark:bg-[#141414] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white text-sm rounded-xl flex justify-between items-center w-full px-4 py-2.5 outline-none cursor-pointer transition-all">
                    <span x-text="opciones.find(o => o.value === seleccionado)?.label || 'Por Defecto'" class="truncate mr-2"></span>
                    <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" :class="abierto ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="abierto" x-cloak x-transition
                     class="absolute z-50 w-full mt-1.5 py-1 bg-white dark:bg-[#1a1a1a] border border-gray-100 dark:border-neutral-800 rounded-xl shadow-xl overflow-hidden">
                    <template x-for="opcion in opciones" :key="opcion.value">
                        <div @click="seleccionado = opcion.value; abierto = false; $nextTick(() => document.getElementById('filter-form').submit())"
                             class="px-4 py-2.5 text-sm font-medium cursor-pointer transition-colors"
                             :class="seleccionado === opcion.value ? 'bg-[#818CF8]/10 text-[#818CF8] font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5'">
                            <span x-text="opcion.label"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-neutral-800/50 flex flex-col md:flex-row gap-5 md:items-center">
            {{-- Chips Sucursal --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mr-1">Sucursal:</span>
                <label class="relative block cursor-pointer">
                    <input type="radio" name="sucursal_id" value="" onchange="this.form.submit()" class="peer absolute inset-0 opacity-0 cursor-pointer z-10" {{ !request()->filled('sucursal_id') ? 'checked' : '' }}>
                    <div class="px-3.5 py-1.5 rounded-full text-xs font-bold border transition-all peer-checked:bg-gray-900 peer-checked:border-gray-900 peer-checked:text-white dark:peer-checked:bg-white dark:peer-checked:text-black bg-white dark:bg-[#141414] border-gray-200 dark:border-neutral-800 text-gray-600 dark:text-gray-400">Todas</div>
                </label>
                @foreach($sucursales ?? [] as $suc)
                    <label class="relative block cursor-pointer">
                        <input type="radio" name="sucursal_id" value="{{ $suc->id }}" onchange="this.form.submit()" class="peer absolute inset-0 opacity-0 cursor-pointer z-10" {{ request('sucursal_id') == $suc->id ? 'checked' : '' }}>
                        <div class="px-3.5 py-1.5 rounded-full text-xs font-bold border transition-all peer-checked:bg-gray-900 peer-checked:border-gray-900 peer-checked:text-white dark:peer-checked:bg-white dark:peer-checked:text-black bg-white dark:bg-[#141414] border-gray-200 dark:border-neutral-800 text-gray-600 dark:text-gray-400">{{ $suc->nombre }}</div>
                    </label>
                @endforeach
            </div>

            {{-- Chips Categoría --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mr-1">Categoría:</span>
                @foreach(['Todos', 'Llanta', 'Rin', 'Accesorio', 'Servicio'] as $tipo)
                    <label class="relative block cursor-pointer">
                        <input type="radio" name="tipo" value="{{ $tipo }}" onchange="this.form.submit()" class="peer absolute inset-0 opacity-0 cursor-pointer z-10" {{ request('tipo', 'Todos') == $tipo ? 'checked' : '' }}>
                        <div class="px-3.5 py-1.5 rounded-full text-xs font-bold border transition-all peer-checked:bg-[#818CF8] peer-checked:border-[#818CF8] peer-checked:text-white bg-white dark:bg-[#141414] border-gray-200 dark:border-neutral-800 text-gray-600 dark:text-gray-400">{{ $tipo }}</div>
                    </label>
                @endforeach

                @php $totalNuevos = count($productosNuevosHoy ?? []); @endphp
                <a href="{{ request('solo_nuevos') == '1' ? route('inventario.index') : route('inventario.index', ['solo_nuevos' => '1']) }}"
                   class="px-3.5 py-1.5 rounded-full text-xs font-bold border transition-all inline-flex items-center gap-1.5
                   {{ request('solo_nuevos') == '1' ? 'bg-[#818CF8] border-[#818CF8] text-white' : 'bg-[#818CF8]/10 dark:bg-[#818CF8]/10 border-[#818CF8]/30 text-[#818CF8]' }}">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 16a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 7.323V16h2a1 1 0 110 2H7a1 1 0 110-2h2V7.323L6.237 8.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 16a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.616a1 1 0 01.894-1.79l1.599.8L9 4.323V3a1 1 0 011-1z"/></svg>
                    Llegó hoy
                    @if($totalNuevos > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ request('solo_nuevos') == '1' ? 'bg-white text-[#818CF8]' : 'bg-[#818CF8] text-white' }}">{{ $totalNuevos }}</span>
                    @endif
                </a>
            </div>
        </div>

        @if(request()->hasAny(['sucursal_id','tipo','q','stock_status','marca_filtro','ordenar_precio','solo_nuevos']))
            <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-neutral-800/50 gap-4">
                <button type="submit"
                        class="px-6 py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold rounded-xl text-xs transition-all hover:opacity-90 active:scale-95">Aplicar Filtros</button>
                <a href="{{ route('inventario.index') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-red-500 hover:text-red-600 transition-colors uppercase tracking-wider">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    Restablecer vista
                </a>
            </div>
        @endif
    </form>

    {{-- ============================
         TABLA DE PRODUCTOS
    ============================= --}}
    <div class="bg-white dark:bg-[#0c0c0c] rounded-2xl border border-gray-200 dark:border-neutral-800/60 shadow-sm dark:shadow-2xl overflow-hidden
                transition-all duration-700 delay-300 ease-[cubic-bezier(0.16,1,0.3,1)]"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'">

        <div class="overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            @php
                $viendoSoloServicios = request('tipo') === 'Servicio';
                $nuevosHoy = $productosNuevosHoy ?? [];
            @endphp

            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-gray-50/80 dark:bg-[#111111] border-b border-gray-200 dark:border-neutral-800/50">
                        @if($viendoSoloServicios)
                            <th class="px-6 py-5 text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.2em]">Servicio de Taller</th>
                            <th class="px-6 py-5 text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.2em]">Precio al Público</th>
                            <th class="px-6 py-5 text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.2em] text-center">Estado</th>
                        @else
                            <th class="px-6 py-5 text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.2em]">Producto</th>
                            <th class="px-6 py-5 text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.2em]">Medida</th>
                            <th class="px-6 py-5 text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.2em] text-center">Stock</th>
                            <th class="px-6 py-5 text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.2em]">Costo Compra</th>
                            <th class="px-6 py-5 text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.2em]">P. Público</th>
                            <th class="px-6 py-5 text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.2em]">P. Mayoreo</th>
                            <th class="px-6 py-5 text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.2em] text-center">Estado</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-neutral-800/30">
                    @forelse($productos as $index => $producto)
                        @php
                            $st      = $producto->stock_cantidad ?? 0;
                            $min     = $producto->stock_minimo ?? 5;
                            $esNuevo = in_array($producto->id, $nuevosHoy);

                            if ($st <= 0) {
                                $estadoColor   = ['bg' => 'bg-red-50 dark:bg-red-900/15', 'text' => 'text-red-600 dark:text-red-400', 'border' => 'border-red-100 dark:border-red-800/30', 'dot' => 'bg-red-500', 'label' => 'Agotado'];
                                $borderClass   = 'border-sinstock';
                            } elseif ($st <= $min) {
                                $estadoColor   = ['bg' => 'bg-amber-50 dark:bg-amber-900/15', 'text' => 'text-amber-600 dark:text-amber-400', 'border' => 'border-amber-100 dark:border-amber-800/30', 'dot' => 'bg-amber-400', 'label' => 'Bajo Stock'];
                                $borderClass   = 'border-bajstock';
                            } else {
                                $estadoColor   = ['bg' => 'bg-emerald-50 dark:bg-emerald-900/15', 'text' => 'text-emerald-600 dark:text-emerald-400', 'border' => 'border-emerald-100 dark:border-emerald-800/30', 'dot' => 'bg-emerald-500', 'label' => 'En Stock'];
                                $borderClass   = 'border-enstock';
                            }
                        @endphp

                        {{-- ▼ Ya NO hay <td> extra para el borde — se usa box-shadow en el <tr> --}}
                        <tr class="group row-anim {{ $borderClass }} hover:bg-gray-50/60 dark:hover:bg-[#111] transition-all duration-200 {{ $esNuevo ? 'bg-[#818CF8]/5 dark:bg-[#818CF8]/8' : '' }}"
                            style="animation-delay: {{ $index * 35 }}ms">

                            @if($viendoSoloServicios)
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-[#818CF8]/10 text-[#818CF8] border border-[#818CF8]/20 flex items-center justify-center shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </div>
                                        <span class="font-bold text-gray-900 dark:text-white text-sm group-hover:text-[#818CF8] transition-colors">{{ $producto->descripcion }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-black text-gray-900 dark:text-white text-sm">${{ number_format($producto->precio_publico, 2) }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[10px] font-black bg-[#818CF8]/10 text-[#818CF8] border border-[#818CF8]/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#818CF8]"></span>Disponible
                                    </span>
                                </td>
                            @else
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg {{ $estadoColor['bg'] }} {{ $estadoColor['text'] }} border {{ $estadoColor['border'] }} flex items-center justify-center shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 dark:text-white text-sm group-hover:text-[#818CF8] transition-colors">{{ $producto->descripcion }}</p>
                                            <p class="text-[11px] text-gray-400 dark:text-neutral-500 font-medium mt-0.5">
                                                {{ $producto->tipo ?? '' }}@if(!empty($producto->marca) && $producto->marca !== $producto->descripcion) · {{ $producto->marca }}@endif
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-500 dark:text-neutral-400">{{ $producto->medida ?? '—' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm font-black min-w-[56px] {{ $estadoColor['bg'] }} {{ $estadoColor['text'] }} border {{ $estadoColor['border'] }}">
                                        {{ $st }} uds
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-500 dark:text-neutral-400">${{ number_format($producto->costo, 2) }}</td>
                                <td class="px-6 py-4 text-sm font-black text-gray-900 dark:text-white">${{ number_format($producto->precio_publico, 2) }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-[#818CF8]">${{ number_format($producto->precio_mayoreo, 2) }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[10px] font-black {{ $estadoColor['bg'] }} {{ $estadoColor['text'] }} border {{ $estadoColor['border'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $estadoColor['dot'] }}"></span>
                                        {{ $estadoColor['label'] }}
                                    </span>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $viendoSoloServicios ? 3 : 7 }}" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-50 dark:bg-[#141414] rounded-2xl border border-gray-100 dark:border-neutral-800 flex items-center justify-center mb-4 shadow-inner">
                                        <svg class="w-7 h-7 text-gray-300 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                    <p class="text-sm font-black text-gray-900 dark:text-white">Sin productos</p>
                                    <p class="text-xs font-semibold text-gray-400 dark:text-neutral-500 mt-1 max-w-xs">No se encontraron productos con los filtros actuales.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($productos, 'links'))
            <div class="px-6 py-4 border-t border-gray-100 dark:border-neutral-800/50 bg-gray-50/40 dark:bg-[#0a0a0a]">
                {{ $productos->links() }}
            </div>
        @endif
    </div>

</div>
@endsection