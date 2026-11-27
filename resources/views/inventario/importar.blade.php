@extends('layouts.app')

@section('header_title', 'Importar Inventario')

@section('content')

<style>[x-cloak] { display: none !important; }</style>

@php
    $paso      = isset($vista) ? 2 : 1;
    $btnGris   = 'px-5 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors';
    $tarjeta   = 'bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm';
    $titulos   = 'bg-gray-50 dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400';
@endphp

<div class="{{ isset($vista) ? 'max-w-5xl' : 'max-w-2xl' }} mx-auto space-y-6"
     x-data="{ archivoSeleccionado: false, nombreArchivo: '', modo: @js(old('modo', 'sumar')) }">

    {{-- Encabezado --}}
    <div>
        <a href="{{ route('inventario.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Volver al inventario
        </a>
        <p class="text-sm text-gray-500 dark:text-gray-400">Carga o actualiza tu catálogo de productos desde Excel.</p>
    </div>

    {{-- Avisos --}}
    @if(session('error'))
        <div class="flex items-start gap-3 p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-xl text-sm font-medium">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="flex items-start gap-3 p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-xl text-sm">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="font-bold mb-1">Problema con la importación:</p>
                <ul class="space-y-1 font-medium">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Pasos --}}
    <div class="flex items-center gap-3">
        @foreach([1 => 'Selecciona el archivo', 2 => 'Revisa la vista previa', 3 => 'Confirma'] as $n => $texto)
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold shrink-0 {{ $n <= $paso ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-white/10 text-gray-400 dark:text-gray-500' }}">{{ $n }}</span>
                <span class="text-xs font-bold hidden sm:inline {{ $n <= $paso ? 'text-gray-800 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500' }}">{{ $texto }}</span>
            </div>
            @if($n < 3)
                <div class="flex-1 h-px {{ $n < $paso ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-white/10' }}"></div>
            @endif
        @endforeach
    </div>

    @if(isset($vista))
        {{-- ============================================ --}}
        {{-- VISTA PREVIA (todavía no se guardó nada)     --}}
        {{-- ============================================ --}}

        {{-- Resumen --}}
        <div class="{{ $tarjeta }} p-6">
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-5">
                Todavía no se guardó nada. Revisa lo que se va a importar de
                <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $vista['archivo'] }}</span>
                en la sucursal <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $vista['sucursal'] }}</span>.
            </p>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="rounded-xl border border-gray-200 dark:border-white/10 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Filas leídas</p>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1 tabular-nums">{{ number_format($vista['total']) }}</p>
                </div>
                <div class="rounded-xl border border-emerald-200 dark:border-emerald-500/20 bg-emerald-50 dark:bg-emerald-500/10 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Se importarán</p>
                    <p class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-300 mt-1 tabular-nums">{{ number_format($vista['ok']) }}</p>
                    <p class="text-xs font-semibold text-emerald-700/80 dark:text-emerald-400/80 mt-0.5">{{ number_format($vista['nuevos']) }} nuevos · {{ number_format($vista['actualizados']) }} existentes</p>
                </div>
                <div class="rounded-xl border border-red-200 dark:border-red-500/20 bg-red-50 dark:bg-red-500/10 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-red-700 dark:text-red-400">Con error (se saltan)</p>
                    <p class="text-2xl font-extrabold text-red-700 dark:text-red-300 mt-1 tabular-nums">{{ number_format($vista['con_error']) }}</p>
                </div>
                <div class="rounded-xl border border-amber-200 dark:border-amber-500/20 bg-amber-50 dark:bg-amber-500/10 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Con aviso</p>
                    <p class="text-2xl font-extrabold text-amber-700 dark:text-amber-300 mt-1 tabular-nums">{{ number_format($vista['con_aviso']) }}</p>
                </div>
            </div>

            <div class="mt-5 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                <p>
                    <span class="font-bold text-gray-900 dark:text-white">Modo:</span>
                    @if($vista['modo'] === 'reemplazar')
                        Reemplazar. El stock queda igual al número del archivo y se registra la diferencia en el historial.
                    @else
                        Sumar. Las piezas del archivo se suman al stock actual y se registra una entrada en el historial.
                    @endif
                </p>
                <p>
                    <span class="font-bold text-gray-900 dark:text-white">Columnas detectadas:</span>
                    {{ implode(', ', $vista['columnas']) }}
                </p>
                @if(count($vista['faltantes']) > 0)
                    <p>
                        <span class="font-bold text-gray-900 dark:text-white">No vienen en el archivo:</span>
                        {{ implode(', ', $vista['faltantes']) }}. Lo que ya tienen los productos se conserva.
                    </p>
                @endif
                @if($vista['sin_stock'] > 0)
                    <p>
                        <span class="font-bold text-gray-900 dark:text-white">Stock vacío:</span>
                        {{ number_format($vista['sin_stock']) }} {{ $vista['sin_stock'] == 1 ? 'fila no trae stock, así que su stock no se toca.' : 'filas no traen stock, así que su stock no se toca.' }}
                        {{ number_format($vista['con_stock']) }} {{ $vista['con_stock'] == 1 ? 'fila sí trae' : 'filas sí traen' }} stock.
                    </p>
                @endif
            </div>
        </div>

        {{-- Problemas --}}
        @if($vista['problemas_total'] > 0)
            <div class="{{ $tarjeta }} overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Filas con problemas</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Rojo: se salta la fila. Amarillo: se importa, pero revísala.
                        @if($vista['problemas_total'] > count($vista['problemas']))
                            Mostrando {{ count($vista['problemas']) }} de {{ number_format($vista['problemas_total']) }}.
                        @endif
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="{{ $titulos }}">
                            <tr>
                                <th class="px-5 py-3">Fila</th>
                                <th class="px-5 py-3">Producto</th>
                                <th class="px-5 py-3">Problema</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                            @foreach($vista['problemas'] as $f)
                                <tr>
                                    <td class="px-5 py-3 font-bold text-gray-500 dark:text-gray-400 tabular-nums">{{ $f['linea'] }}</td>
                                    <td class="px-5 py-3 font-semibold text-gray-800 dark:text-gray-200">
                                        {{ $f['marca'] !== '' ? $f['marca'] : '(sin marca)' }}
                                        <span class="text-gray-400 dark:text-gray-500">· {{ $f['medida'] !== '' ? $f['medida'] : '(sin medida)' }}</span>
                                    </td>
                                    <td class="px-5 py-3 space-y-1">
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
            <div class="{{ $tarjeta }} overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Así se importarán las primeras filas</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Muestra de {{ count($vista['muestra']) }} de {{ number_format($vista['ok']) }} {{ $vista['ok'] == 1 ? 'fila correcta' : 'filas correctas' }}.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="{{ $titulos }}">
                            <tr>
                                <th class="px-5 py-3">Marca</th>
                                <th class="px-5 py-3">Medida</th>
                                <th class="px-5 py-3 text-right">Precio público</th>
                                <th class="px-5 py-3 text-right">Stock</th>
                                <th class="px-5 py-3">Producto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                            @foreach($vista['muestra'] as $f)
                                <tr>
                                    <td class="px-5 py-3 font-semibold text-gray-800 dark:text-gray-200">{{ $f['marca'] }}</td>
                                    <td class="px-5 py-3 font-semibold text-gray-600 dark:text-gray-300">{{ $f['medida'] }}</td>
                                    <td class="px-5 py-3 text-right font-semibold text-gray-800 dark:text-gray-200 tabular-nums">
                                        @if($f['precio_publico'] !== null)
                                            ${{ number_format($f['precio_publico'], 2) }}
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">se conserva</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right font-semibold text-gray-800 dark:text-gray-200 tabular-nums">
                                        @if($f['stock'] !== null)
                                            {{ number_format($f['stock']) }}
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">no se toca</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        @if($f['existe'])
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300">Existente</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">Nuevo</span>
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
        <form action="{{ route('inventario.procesar') }}" method="POST" class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3" x-data="{ enviando: false }" @submit="enviando = true">
            @csrf
            <button type="submit" name="accion" value="cancelar" class="{{ $btnGris }}">Cancelar</button>
            @if($vista['ok'] === 0)
                <button type="button" disabled
                        class="px-7 py-2.5 bg-gray-200 dark:bg-white/10 text-gray-400 dark:text-gray-500 font-bold text-sm rounded-xl cursor-not-allowed">
                    No hay filas correctas para importar
                </button>
            @else
                <button type="submit" name="accion" value="confirmar" :disabled="enviando"
                        class="inline-flex items-center justify-center px-7 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl transition-colors">
                    Confirmar e importar {{ number_format($vista['ok']) }} {{ $vista['ok'] == 1 ? 'fila' : 'filas' }}
                </button>
            @endif
        </form>
    @else
        {{-- ============================================ --}}
        {{-- FORMULARIO PARA SUBIR EL ARCHIVO             --}}
        {{-- ============================================ --}}
        <div class="{{ $tarjeta }} overflow-hidden">
            <form action="{{ route('inventario.procesar') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Sucursal --}}
                @if(isset($sucursales) && count($sucursales) > 0)
                    <div class="px-6 pt-6">
                        <div class="bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 rounded-xl p-4">
                            <label for="sucursal_id" class="block text-[11px] font-bold text-indigo-700 dark:text-indigo-300 uppercase tracking-wider mb-2">Sucursal destino del stock</label>
                            <select name="sucursal_id" id="sucursal_id"
                                    class="w-full px-3 py-2.5 bg-white dark:bg-white/[0.03] border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white text-sm font-medium rounded-xl outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 cursor-pointer">
                                @foreach($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}" @selected(old('sucursal_id') == $sucursal->id)>{{ $sucursal->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">El stock del archivo se asignará a esta sucursal.</p>
                        </div>
                    </div>
                @endif

                {{-- Modo de stock --}}
                <div class="px-6 pt-5">
                    <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">¿Qué hacer con el stock del archivo?</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="cursor-pointer rounded-xl border p-4 transition-colors"
                               :class="modo === 'sumar' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10' : 'border-gray-200 dark:border-white/10'">
                            <input type="radio" name="modo" value="sumar" x-model="modo" class="sr-only">
                            <span class="block text-sm font-bold text-gray-900 dark:text-white">Sumar al stock actual</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">Para cuando llega mercancía. Las piezas del archivo se suman a lo que ya hay.</span>
                        </label>
                        <label class="cursor-pointer rounded-xl border p-4 transition-colors"
                               :class="modo === 'reemplazar' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10' : 'border-gray-200 dark:border-white/10'">
                            <input type="radio" name="modo" value="reemplazar" x-model="modo" class="sr-only">
                            <span class="block text-sm font-bold text-gray-900 dark:text-white">Reemplazar el stock</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">Para cuando cuentas el inventario físico. El stock queda igual al archivo.</span>
                        </label>
                    </div>
                    <p x-show="modo === 'reemplazar'" x-cloak class="text-xs font-semibold text-amber-600 dark:text-amber-400 mt-2">
                        Cuidado: si el archivo trae menos piezas que el sistema, se registra una salida por la diferencia.
                    </p>
                </div>

                {{-- Zona para soltar el archivo --}}
                <div class="p-6">
                    <div id="dropzone"
                         x-on:dragover.prevent="$el.classList.add('border-indigo-500', 'bg-indigo-50')"
                         x-on:dragleave.prevent="$el.classList.remove('border-indigo-500', 'bg-indigo-50')"
                         x-on:drop.prevent="
                             $el.classList.remove('border-indigo-500', 'bg-indigo-50');
                             let f = $event.dataTransfer.files[0];
                             if (f) { archivoSeleccionado = true; nombreArchivo = f.name; document.getElementById('file-input').files = $event.dataTransfer.files; }
                         "
                         class="relative border-2 border-dashed rounded-2xl p-8 sm:p-10 flex flex-col items-center justify-center cursor-pointer transition-colors"
                         :class="archivoSeleccionado
                             ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-500/10'
                             : 'border-gray-300 dark:border-white/15 bg-gray-50 dark:bg-white/[0.02] hover:border-indigo-400'">

                        <input type="file" name="archivo_excel" id="file-input" accept=".xlsx,.xls,.csv" required
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                               x-on:change="
                                   let f = $event.target.files[0];
                                   if (f) { archivoSeleccionado = true; nombreArchivo = f.name; }
                                   else { archivoSeleccionado = false; nombreArchivo = ''; }
                               ">

                        <span class="w-14 h-14 rounded-2xl flex items-center justify-center mb-4"
                              :class="archivoSeleccionado ? 'bg-indigo-600 text-white' : 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400'">
                            <svg x-show="! archivoSeleccionado" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <svg x-show="archivoSeleccionado" x-cloak class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>

                        {{-- Sin archivo --}}
                        <div x-show="! archivoSeleccionado" class="text-center">
                            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Arrastra tu archivo aquí</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">.xlsx, .xls o .csv, máximo 10 MB</p>

                            <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-xl p-4 mb-5 text-left">
                                <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Para identificar el producto</p>
                                <div class="flex flex-wrap gap-1.5 mb-3">
                                    @foreach(['MARCA', 'MEDIDA', 'o DESCRIPCIÓN'] as $col)
                                        <code class="bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/20 px-2.5 py-1 rounded-lg text-[11px] font-bold">{{ $col }}</code>
                                    @endforeach
                                </div>
                                <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Datos (al menos uno)</p>
                                <div class="flex flex-wrap gap-1.5 mb-3">
                                    @foreach(['PRECIO PÚBLICO', 'MAYOREO', 'COSTO', 'STOCK'] as $col)
                                        <code class="bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/20 px-2.5 py-1 rounded-lg text-[11px] font-bold">{{ $col }}</code>
                                    @endforeach
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Los encabezados pueden estar en cualquier fila. Lo que el archivo no traiga no se borra: se conserva lo que ya tiene cada producto. Un STOCK vacío no cambia el stock.
                                </p>
                            </div>

                            <span class="inline-block px-6 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl">Seleccionar archivo</span>
                        </div>

                        {{-- Con archivo --}}
                        <div x-show="archivoSeleccionado" x-cloak class="text-center">
                            <p class="text-base font-bold text-gray-900 dark:text-white mb-1">Archivo listo</p>
                            <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 mb-3 break-all" x-text="nombreArchivo"></p>
                            <span class="inline-block px-4 py-1.5 text-xs font-bold text-gray-600 dark:text-gray-300 bg-white dark:bg-white/10 rounded-lg border border-gray-200 dark:border-white/10">Haz clic para cambiar el archivo</span>
                        </div>
                    </div>
                </div>

                {{-- Pie --}}
                <div class="flex justify-end gap-3 px-6 pb-6 pt-5 border-t border-gray-100 dark:border-white/10">
                    <a href="{{ route('inventario.index') }}" class="{{ $btnGris }}">Cancelar</a>
                    <button type="submit" :disabled="! archivoSeleccionado"
                            class="inline-flex items-center gap-2 px-7 py-2.5 text-sm font-bold rounded-xl transition-colors"
                            :class="archivoSeleccionado ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : 'bg-gray-200 dark:bg-white/10 text-gray-400 dark:text-gray-500 cursor-not-allowed'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Revisar archivo
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection
