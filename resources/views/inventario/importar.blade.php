@extends('layouts.app')

@section('header_title', 'Importar Inventario')

@section('content')
<style>
    ::-webkit-scrollbar { display: none !important; }
    * { scrollbar-width: none !important; }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50%       { transform: translateY(-6px); }
    }
    .float-anim { animation: float 3s ease-in-out infinite; }

    @keyframes pulse-ring {
        0%   { transform: scale(1);   opacity: 0.4; }
        100% { transform: scale(1.6); opacity: 0; }
    }
    .pulse-ring { animation: pulse-ring 2s ease-out infinite; }
</style>

<div class="max-w-2xl mx-auto px-4 sm:px-6 py-8 space-y-6"
     x-data="{ cargado: false, archivoSeleccionado: false, nombreArchivo: '' }"
     x-init="setTimeout(() => cargado = true, 50)">

    {{-- Header --}}
    <div class="transition-all duration-1000 ease-[cubic-bezier(0.16,1,0.3,1)] transform"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-8'">
        <a href="{{ route('inventario.index') }}" class="group inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 dark:text-neutral-400 hover:text-[#818CF8] transition-colors mb-3">
            <svg class="w-4 h-4 transition-transform duration-200 group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Volver al inventario
        </a>
        <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Importar Inventario</h1>
        <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1.5 font-medium">Carga o actualiza tu catálogo de productos desde Excel.</p>
    </div>

    {{-- Alertas --}}
    @if(session('error'))
        <div class="bg-red-50 dark:bg-[#2a1315] border border-red-200 dark:border-red-900/50 p-4 rounded-xl flex items-start gap-3 shadow-sm"
             :class="cargado ? 'opacity-100' : 'opacity-0'" style="transition: opacity 0.5s ease 0.1s;">
            <div class="p-1.5 bg-red-100 dark:bg-red-900/40 rounded-lg shrink-0 mt-0.5">
                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <span class="text-sm font-bold text-red-800 dark:text-red-400">{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 dark:bg-[#2a1315] border border-red-200 dark:border-red-900/50 p-4 rounded-xl shadow-sm"
             :class="cargado ? 'opacity-100' : 'opacity-0'" style="transition: opacity 0.5s ease 0.1s;">
            <div class="flex items-start gap-3">
                <div class="p-1.5 bg-red-100 dark:bg-red-900/40 rounded-lg shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-black text-red-800 dark:text-red-400 mb-1.5">Problema con la importación:</p>
                    <ul class="space-y-1">
                        @foreach($errors->all() as $error)
                            <li class="text-xs font-semibold text-red-700 dark:text-red-300 flex items-center gap-1.5">
                                <span class="w-1 h-1 rounded-full bg-red-400 shrink-0"></span>{{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Pasos --}}
    <div class="flex items-center gap-3 transition-all duration-700 delay-100"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black"
                 :class="archivoSeleccionado ? 'bg-[#818CF8] text-white' : 'bg-[#818CF8] text-white'">1</div>
            <span class="text-xs font-bold text-gray-700 dark:text-neutral-300">Selecciona el archivo</span>
        </div>
        <div class="flex-1 h-px" :class="archivoSeleccionado ? 'bg-[#818CF8]' : 'bg-gray-200 dark:bg-neutral-800'"
             style="transition: background 0.4s ease;"></div>
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black transition-all duration-300"
                 :class="archivoSeleccionado ? 'bg-[#818CF8] text-white' : 'bg-gray-100 dark:bg-neutral-800 text-gray-400 dark:text-neutral-500'">2</div>
            <span class="text-xs font-bold transition-colors duration-300"
                  :class="archivoSeleccionado ? 'text-gray-700 dark:text-neutral-300' : 'text-gray-400 dark:text-neutral-500'">Procesa e importa</span>
        </div>
    </div>

    {{-- Tarjeta principal --}}
    <div class="bg-white dark:bg-[#0c0c0c] rounded-2xl border border-gray-200 dark:border-neutral-800/60 shadow-sm dark:shadow-2xl overflow-hidden transition-all duration-1000 ease-[cubic-bezier(0.16,1,0.3,1)] transform delay-150"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'">

        <form action="{{ route('inventario.procesar') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Sucursal --}}
            @if(isset($sucursales) && count($sucursales) > 0)
                <div class="px-6 pt-6">
                    <div class="bg-[#818CF8]/5 dark:bg-[#818CF8]/5 border border-[#818CF8]/20 rounded-xl p-4">
                        <label for="sucursal_id" class="block text-[10px] font-black text-[#818CF8] uppercase tracking-[0.15em] mb-2">
                            Sucursal destino del stock
                        </label>
                        <select name="sucursal_id" id="sucursal_id"
                                class="w-full bg-white dark:bg-[#141414] border border-gray-200 dark:border-neutral-800 rounded-xl px-4 py-2.5 text-gray-800 dark:text-white text-sm font-semibold focus:ring-2 focus:ring-[#818CF8]/40 focus:border-[#818CF8] outline-none transition-all cursor-pointer">
                            @foreach($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}" {{ old('sucursal_id') == $sucursal->id ? 'selected' : '' }}>
                                    {{ $sucursal->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 dark:text-neutral-500 font-medium mt-1.5">El stock del archivo se asignará a esta sucursal.</p>
                    </div>
                </div>
            @endif

            {{-- Dropzone --}}
            <div class="p-6">
                <div id="dropzone"
                     x-on:dragover.prevent="$el.classList.add('border-[#818CF8]', 'bg-[#818CF8]/5')"
                     x-on:dragleave.prevent="$el.classList.remove('border-[#818CF8]', 'bg-[#818CF8]/5')"
                     x-on:drop.prevent="
                         $el.classList.remove('border-[#818CF8]', 'bg-[#818CF8]/5');
                         let f = $event.dataTransfer.files[0];
                         if(f){ archivoSeleccionado=true; nombreArchivo=f.name; document.getElementById('file-input').files = $event.dataTransfer.files; }
                     "
                     class="relative border-2 border-dashed rounded-2xl p-10 flex flex-col items-center justify-center cursor-pointer transition-all duration-300"
                     :class="archivoSeleccionado
                         ? 'border-[#818CF8] bg-[#818CF8]/5 dark:bg-[#818CF8]/5'
                         : 'border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-[#080808] hover:border-[#818CF8]/50 hover:bg-[#818CF8]/3'">

                    <input type="file" name="archivo_excel" id="file-input" accept=".xlsx,.xls,.csv" required
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                           x-on:change="
                               let f = $event.target.files[0];
                               if(f){ archivoSeleccionado=true; nombreArchivo=f.name; }
                               else { archivoSeleccionado=false; nombreArchivo=''; }
                           ">

                    {{-- Ícono animado --}}
                    <div class="relative mb-6">
                        {{-- Anillo pulsante (solo cuando no hay archivo) --}}
                        <div x-show="!archivoSeleccionado"
                             class="absolute inset-0 rounded-2xl bg-[#818CF8]/20 pulse-ring"></div>

                        <div class="relative w-20 h-20 rounded-2xl flex items-center justify-center transition-all duration-500"
                             :class="archivoSeleccionado
                                 ? 'bg-[#818CF8] shadow-[0_8px_30px_rgba(129,140,248,0.4)]'
                                 : 'bg-[#818CF8]/10 border border-[#818CF8]/20 float-anim'">
                            <template x-if="!archivoSeleccionado">
                                <svg class="w-9 h-9 text-[#818CF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                            </template>
                            <template x-if="archivoSeleccionado">
                                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </template>
                        </div>
                    </div>

                    {{-- Texto principal --}}
                    <template x-if="!archivoSeleccionado">
                        <div class="text-center">
                            <h3 class="text-lg font-black text-gray-800 dark:text-white mb-1">Arrastra tu archivo aquí</h3>
                            <p class="text-sm text-gray-400 dark:text-neutral-500 font-medium mb-5">.xlsx, .xls o .csv — máximo 10 MB</p>

                            {{-- Columnas requeridas --}}
                            <div class="bg-white dark:bg-[#141414] border border-gray-100 dark:border-neutral-800 rounded-xl p-3.5 mb-5">
                                <p class="text-[9px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.2em] mb-2.5">Columnas requeridas en Fila 1</p>
                                <div class="flex flex-wrap justify-center gap-1.5">
                                    @foreach(['MARCA','MEDIDA','DESCRIPCIÓN','PRECIO PÚBLICO','STOCK'] as $col)
                                        <code class="bg-[#818CF8]/10 text-[#818CF8] border border-[#818CF8]/20 px-2.5 py-1 rounded-lg text-[10px] font-black">{{ $col }}</code>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex items-center gap-3 mb-5">
                                <div class="h-px bg-gray-100 dark:bg-neutral-800 flex-1"></div>
                                <span class="text-[10px] font-black text-gray-300 dark:text-neutral-600 uppercase tracking-wider">o</span>
                                <div class="h-px bg-gray-100 dark:bg-neutral-800 flex-1"></div>
                            </div>

                            <button type="button"
                                    class="pointer-events-none px-6 py-2.5 bg-[#818CF8] text-white text-sm font-bold rounded-xl shadow-[0_4px_14px_rgba(129,140,248,0.3)]">
                                Seleccionar archivo
                            </button>
                        </div>
                    </template>

                    {{-- Archivo seleccionado --}}
                    <template x-if="archivoSeleccionado">
                        <div class="text-center">
                            <p class="text-base font-black text-gray-900 dark:text-white mb-1">¡Archivo listo!</p>
                            <p class="text-sm font-bold text-[#818CF8] mb-4" x-text="nombreArchivo"></p>
                            <button type="button"
                                    class="pointer-events-none px-4 py-1.5 text-xs font-bold text-gray-500 dark:text-neutral-400 bg-gray-100 dark:bg-neutral-800 rounded-lg border border-gray-200 dark:border-neutral-700">
                                Haz clic para cambiar el archivo
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex justify-end gap-3 px-6 pb-6 pt-2 border-t border-gray-100 dark:border-neutral-800/50">
                <a href="{{ route('inventario.index') }}"
                   class="px-5 py-2.5 text-sm font-semibold text-gray-600 dark:text-neutral-300 bg-gray-100 dark:bg-[#1a1a1a] border border-gray-200 dark:border-neutral-800 rounded-xl hover:bg-gray-200 dark:hover:bg-[#222] transition-all">
                    Cancelar
                </a>
                <button type="submit"
                        :disabled="!archivoSeleccionado"
                        :class="archivoSeleccionado
                            ? 'bg-[#818CF8] hover:bg-[#6366F1] shadow-[0_4px_14px_rgba(129,140,248,0.3)] hover:shadow-[0_6px_20px_rgba(129,140,248,0.4)] cursor-pointer'
                            : 'bg-gray-200 dark:bg-neutral-800 text-gray-400 dark:text-neutral-500 cursor-not-allowed'"
                        class="inline-flex items-center gap-2 px-7 py-2.5 text-white text-sm font-bold rounded-xl active:scale-95 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Procesar e Importar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection