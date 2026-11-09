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

@php $paso = isset($vista) ? 2 : 1; @endphp

<div class="{{ isset($vista) ? 'max-w-5xl' : 'max-w-2xl' }} mx-auto px-4 sm:px-6 py-8 space-y-6"
     x-data="{ cargado: false, archivoSeleccionado: false, nombreArchivo: '', modo: '{{ old('modo', 'sumar') }}' }"
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
        @foreach([1 => 'Selecciona el archivo', 2 => 'Revisa la vista previa', 3 => 'Confirma'] as $n => $texto)
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black {{ $n <= $paso ? 'bg-[#818CF8] text-white' : 'bg-gray-100 dark:bg-neutral-800 text-gray-400 dark:text-neutral-500' }}">{{ $n }}</div>
                <span class="text-xs font-bold {{ $n <= $paso ? 'text-gray-700 dark:text-neutral-300' : 'text-gray-400 dark:text-neutral-500' }}">{{ $texto }}</span>
            </div>
            @if($n < 3)
                <div class="flex-1 h-px {{ $n < $paso ? 'bg-[#818CF8]' : 'bg-gray-200 dark:bg-neutral-800' }}"></div>
            @endif
        @endforeach
    </div>

    @if(isset($vista))
        {{-- ============================================ --}}
        {{-- VISTA PREVIA (todavía no se guardó nada)     --}}
        {{-- ============================================ --}}
        <div class="space-y-6">

            {{-- Resumen --}}
            <div class="bg-white dark:bg-[#0c0c0c] rounded-2xl border border-gray-200 dark:border-neutral-800/60 shadow-sm p-6">
                <p class="text-xs font-bold text-gray-500 dark:text-neutral-400 mb-4">
                    Todavía no se guardó nada. Revisa lo que se va a importar de
                    <span class="text-[#818CF8]">{{ $vista['archivo'] }}</span>
                    en la sucursal <span class="text-[#818CF8]">{{ $vista['sucursal'] }}</span>.
                </p>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="rounded-xl border border-gray-100 dark:border-neutral-800 p-4">
                        <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-neutral-500">Filas leídas</p>
                        <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($vista['total']) }}</p>
                    </div>
                    <div class="rounded-xl border border-emerald-200 dark:border-emerald-900/50 bg-emerald-50 dark:bg-emerald-950/20 p-4">
                        <p class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Se importarán</p>
                        <p class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mt-1">{{ number_format($vista['ok']) }}</p>
                        <p class="text-[11px] font-semibold text-emerald-600/80 dark:text-emerald-400/80 mt-0.5">{{ number_format($vista['nuevos']) }} nuevos · {{ number_format($vista['actualizados']) }} existentes</p>
                    </div>
                    <div class="rounded-xl border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-950/20 p-4">
                        <p class="text-[10px] font-black uppercase tracking-wider text-red-600 dark:text-red-400">Con error (se saltan)</p>
                        <p class="text-2xl font-black text-red-700 dark:text-red-300 mt-1">{{ number_format($vista['con_error']) }}</p>
                    </div>
                    <div class="rounded-xl border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-950/20 p-4">
                        <p class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Con aviso</p>
                        <p class="text-2xl font-black text-amber-700 dark:text-amber-300 mt-1">{{ number_format($vista['con_aviso']) }}</p>
                    </div>
                </div>

                <div class="mt-5 space-y-2 text-xs font-semibold text-gray-600 dark:text-neutral-300">
                    <p>
                        <span class="font-black text-gray-800 dark:text-white">Modo:</span>
                        @if($vista['modo'] === 'reemplazar')
                            Reemplazar. El stock queda igual al número del archivo y se registra la diferencia en el historial.
                        @else
                            Sumar. Las piezas del archivo se suman al stock actual y se registra una entrada en el historial.
                        @endif
                    </p>
                    <p>
                        <span class="font-black text-gray-800 dark:text-white">Columnas detectadas:</span>
                        {{ implode(', ', $vista['columnas']) }}
                    </p>
                    @if(count($vista['faltantes']) > 0)
                        <p>
                            <span class="font-black text-gray-800 dark:text-white">No vienen en el archivo:</span>
                            {{ implode(', ', $vista['faltantes']) }}. Lo que ya tienen los productos se conserva.
                        </p>
                    @endif
                    @if($vista['sin_stock'] > 0)
                        <p>
                            <span class="font-black text-gray-800 dark:text-white">Stock vacío:</span>
                            {{ number_format($vista['sin_stock']) }} filas no traen stock, así que su stock no se toca.
                            {{ number_format($vista['con_stock']) }} filas sí traen stock.
                        </p>
                    @endif
                </div>
            </div>

            {{-- Problemas --}}
            @if($vista['problemas_total'] > 0)
                <div class="bg-white dark:bg-[#0c0c0c] rounded-2xl border border-gray-200 dark:border-neutral-800/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-neutral-800/50">
                        <h2 class="text-sm font-black text-gray-900 dark:text-white">Filas con problemas</h2>
                        <p class="text-xs text-gray-500 dark:text-neutral-400 font-medium mt-0.5">
                            Rojo: se salta la fila. Amarillo: se importa, pero revísala.
                            @if($vista['problemas_total'] > count($vista['problemas']))
                                Mostrando {{ count($vista['problemas']) }} de {{ number_format($vista['problemas_total']) }}.
                            @endif
                        </p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-[#080808] text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-neutral-500">
                                <tr>
                                    <th class="px-4 py-3">Fila</th>
                                    <th class="px-4 py-3">Producto</th>
                                    <th class="px-4 py-3">Problema</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-neutral-800/50">
                                @foreach($vista['problemas'] as $f)
                                    <tr>
                                        <td class="px-4 py-3 font-bold text-gray-500 dark:text-neutral-400">{{ $f['linea'] }}</td>
                                        <td class="px-4 py-3 font-semibold text-gray-800 dark:text-neutral-200">
                                            {{ $f['marca'] !== '' ? $f['marca'] : '(sin marca)' }}
                                            <span class="text-gray-400 dark:text-neutral-500">· {{ $f['medida'] !== '' ? $f['medida'] : '(sin medida)' }}</span>
                                        </td>
                                        <td class="px-4 py-3 space-y-1">
                                            @foreach($f['errores'] as $msg)
                                                <span class="block text-red-700 dark:text-red-300 font-bold">{{ $msg }}</span>
                                            @endforeach
                                            @foreach($f['avisos'] as $msg)
                                                <span class="block text-amber-700 dark:text-amber-300 font-semibold">{{ $msg }}</span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Muestra de filas buenas --}}
            @if(count($vista['muestra']) > 0)
                <div class="bg-white dark:bg-[#0c0c0c] rounded-2xl border border-gray-200 dark:border-neutral-800/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-neutral-800/50">
                        <h2 class="text-sm font-black text-gray-900 dark:text-white">Así se importarán las primeras filas</h2>
                        <p class="text-xs text-gray-500 dark:text-neutral-400 font-medium mt-0.5">Muestra de {{ count($vista['muestra']) }} de {{ number_format($vista['ok']) }} filas correctas.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-[#080808] text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-neutral-500">
                                <tr>
                                    <th class="px-4 py-3">Marca</th>
                                    <th class="px-4 py-3">Medida</th>
                                    <th class="px-4 py-3 text-right">Precio público</th>
                                    <th class="px-4 py-3 text-right">Stock</th>
                                    <th class="px-4 py-3">Producto</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-neutral-800/50">
                                @foreach($vista['muestra'] as $f)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-gray-800 dark:text-neutral-200">{{ $f['marca'] }}</td>
                                        <td class="px-4 py-3 font-semibold text-gray-600 dark:text-neutral-300">{{ $f['medida'] }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-neutral-200">
                                            @if($f['precio_publico'] !== null)
                                                ${{ number_format($f['precio_publico'], 2) }}
                                            @else
                                                <span class="text-gray-400 dark:text-neutral-500">se conserva</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-neutral-200">
                                            @if($f['stock'] !== null)
                                                {{ number_format($f['stock']) }}
                                            @else
                                                <span class="text-gray-400 dark:text-neutral-500">no se toca</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($f['existe'])
                                                <span class="px-2 py-0.5 rounded-md bg-gray-100 dark:bg-neutral-800 text-gray-600 dark:text-neutral-300 font-bold">Existente</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-bold">Nuevo</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Confirmar o cancelar --}}
            <form action="{{ route('inventario.procesar') }}" method="POST" class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                @csrf
                <button type="submit" name="accion" value="cancelar"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-600 dark:text-neutral-300 bg-gray-100 dark:bg-[#1a1a1a] border border-gray-200 dark:border-neutral-800 rounded-xl hover:bg-gray-200 dark:hover:bg-[#222] transition-all">
                    Cancelar
                </button>
                <button type="submit" name="accion" value="confirmar"
                        @if($vista['ok'] === 0) disabled @endif
                        class="inline-flex items-center justify-center gap-2 px-7 py-2.5 text-white text-sm font-bold rounded-xl active:scale-95 transition-all {{ $vista['ok'] === 0 ? 'bg-gray-200 dark:bg-neutral-800 text-gray-400 dark:text-neutral-500 cursor-not-allowed' : 'bg-[#818CF8] hover:bg-[#6366F1] shadow-[0_4px_14px_rgba(129,140,248,0.3)] cursor-pointer' }}">
                    Confirmar e importar {{ number_format($vista['ok']) }} filas
                </button>
            </form>
        </div>
    @else
        {{-- ============================================ --}}
        {{-- FORMULARIO PARA SUBIR EL ARCHIVO             --}}
        {{-- ============================================ --}}
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

                {{-- Modo de stock --}}
                <div class="px-6 pt-4">
                    <p class="block text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-2">¿Qué hacer con el stock del archivo?</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="cursor-pointer rounded-xl border p-3.5 transition-all"
                               :class="modo === 'sumar' ? 'border-[#818CF8] bg-[#818CF8]/5' : 'border-gray-200 dark:border-neutral-800'">
                            <input type="radio" name="modo" value="sumar" x-model="modo" class="sr-only">
                            <span class="block text-sm font-black text-gray-900 dark:text-white">Sumar al stock actual</span>
                            <span class="block text-xs font-medium text-gray-500 dark:text-neutral-400 mt-1">Para cuando llega mercancía. Las piezas del archivo se suman a lo que ya hay.</span>
                        </label>
                        <label class="cursor-pointer rounded-xl border p-3.5 transition-all"
                               :class="modo === 'reemplazar' ? 'border-[#818CF8] bg-[#818CF8]/5' : 'border-gray-200 dark:border-neutral-800'">
                            <input type="radio" name="modo" value="reemplazar" x-model="modo" class="sr-only">
                            <span class="block text-sm font-black text-gray-900 dark:text-white">Reemplazar el stock</span>
                            <span class="block text-xs font-medium text-gray-500 dark:text-neutral-400 mt-1">Para cuando cuentas el inventario físico. El stock queda igual al archivo.</span>
                        </label>
                    </div>
                    <p x-show="modo === 'reemplazar'" x-cloak class="text-xs font-semibold text-amber-600 dark:text-amber-400 mt-2">
                        Cuidado: si el archivo trae menos piezas que el sistema, se registra una salida por la diferencia.
                    </p>
                </div>

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

                                {{-- Columnas del archivo --}}
                                <div class="bg-white dark:bg-[#141414] border border-gray-100 dark:border-neutral-800 rounded-xl p-3.5 mb-5 text-left">
                                    <p class="text-[9px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.2em] mb-2">Para identificar el producto</p>
                                    <div class="flex flex-wrap gap-1.5 mb-3">
                                        @foreach(['MARCA','MEDIDA','o DESCRIPCIÓN'] as $col)
                                            <code class="bg-[#818CF8]/10 text-[#818CF8] border border-[#818CF8]/20 px-2.5 py-1 rounded-lg text-[10px] font-black">{{ $col }}</code>
                                        @endforeach
                                    </div>
                                    <p class="text-[9px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.2em] mb-2">Datos (al menos uno)</p>
                                    <div class="flex flex-wrap gap-1.5 mb-3">
                                        @foreach(['PRECIO PÚBLICO','MAYOREO','COSTO','STOCK'] as $col)
                                            <code class="bg-[#818CF8]/10 text-[#818CF8] border border-[#818CF8]/20 px-2.5 py-1 rounded-lg text-[10px] font-black">{{ $col }}</code>
                                        @endforeach
                                    </div>
                                    <p class="text-[11px] font-medium text-gray-500 dark:text-neutral-400">
                                        Los encabezados pueden estar en cualquier fila. Lo que el archivo no traiga no se borra: se conserva lo que ya tiene cada producto. Un STOCK vacío no cambia el stock.
                                    </p>
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
                       class="px-5 py-2.5 text-sm font-semibold text-gray-600 dark:text-neutral-300 bg-gray-100 dark:bg-[#1a1a1a] border border-gray-200 dark:border-neutral-800 rounded-xl hover:bg-gray-200 dark:hover:bg-[#222] transition-all">                        Cancelar
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
                        Revisar archivo
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection