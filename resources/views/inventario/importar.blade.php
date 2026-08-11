@extends('layouts.app')

@section('header_title', 'Importar Inventario')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('inventario.index') }}" class="text-gray-500 hover:text-emerald-600 flex items-center transition-colors font-medium">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Volver al inventario
        </a>
    </div>

    {{-- Alertas de Errores Generales y de Validación --}}
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-red-700 text-sm font-medium">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg shadow-sm">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <h4 class="text-red-800 text-sm font-bold mb-1">Hubo un problema con la importación:</h4>
                    <ul class="list-disc list-inside text-red-700 text-sm font-medium">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Importar Catálogo e Inventario</h2>
            <p class="text-gray-600 mt-1 font-medium">Carga o actualiza tus productos desde un archivo de Excel</p>
            <p class="text-gray-400 text-sm">Formatos soportados: .xlsx, .xls, .csv (máximo 10MB)</p> 
        </div>

        <form action="{{ route('inventario.procesar') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            {{-- Selección de Sucursal si el usuario administra más de una --}}
            @if(isset($sucursales) && count($sucursales) > 0)
                <div class="mb-6 bg-emerald-50/50 p-4 rounded-xl border border-emerald-100">
                    <label for="sucursal_id" class="block text-sm font-bold text-gray-700 mb-2">
                        Sucursal destino del Stock:
                    </label>
                    <select name="sucursal_id" id="sucursal_id" class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 text-gray-800 font-medium focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                        @foreach($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}" {{ old('sucursal_id') == $sucursal->id ? 'selected' : '' }}>
                                {{ $sucursal->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">El stock especificado en el archivo se asignará a esta sucursal.</p>
                </div>
            @endif

            <div id="dropzone" class="relative border-2 border-dashed border-gray-300 rounded-2xl p-10 flex flex-col items-center justify-center bg-gray-50 hover:bg-emerald-50 hover:border-emerald-300 transition-all duration-300 group cursor-pointer">
                
                <input type="file" name="archivo_excel" id="file-input" accept=".xlsx, .xls, .csv" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                
                <div class="bg-emerald-50 text-emerald-600 p-4 rounded-full mb-4 group-hover:bg-emerald-100 transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>

                <h3 class="text-lg font-bold text-gray-800 mb-2">Arrastra y suelta tu Excel aquí</h3>
                
                {{-- Columnas corregidas según el modelo de tu sistema --}}
                <div class="bg-white border border-gray-200 rounded-lg p-3 my-2 text-center max-w-md shadow-xs">
                    <p class="text-xs text-gray-500 mb-1 font-bold uppercase tracking-wider">Columnas requeridas en la Fila 1:</p>
                    <div class="flex flex-wrap justify-center gap-1">
                        <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs font-mono font-bold">MARCA</span>
                        <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs font-mono font-bold">MEDIDA</span>
                        <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs font-mono font-bold">DESCRIPCIÓN</span>
                        <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs font-mono font-bold">PRECIO PÚBLICO</span>
                        <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs font-mono font-bold">STOCK</span>
                    </div>
                </div>
                
                <div class="flex items-center gap-4 w-full max-w-[200px] my-3">
                    <div class="h-px bg-gray-200 flex-1"></div>
                    <span class="text-gray-400 text-xs font-bold">O</span>
                    <div class="h-px bg-gray-200 flex-1"></div>
                </div>
                
                <button type="button" class="bg-emerald-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-emerald-700 transition-colors shadow-sm pointer-events-none">
                    Buscar archivo Excel
                </button>
                
                <div id="file-name-container" class="mt-4 hidden items-center text-sm font-medium text-emerald-700 bg-emerald-100 px-4 py-2 rounded-lg border border-emerald-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span id="file-name"></span>
                </div>
            </div>

            <div class="mt-8 flex justify-end border-t border-gray-100 pt-6">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3 rounded-xl font-medium transition-colors shadow-md flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Procesar e Importar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const fileInput = document.getElementById('file-input');
    const fileNameContainer = document.getElementById('file-name-container');
    const fileNameDisplay = document.getElementById('file-name');
    const dropzone = document.getElementById('dropzone');

    fileInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            fileNameDisplay.textContent = this.files[0].name;
            fileNameContainer.classList.remove('hidden');
            fileNameContainer.classList.add('flex');
            
            dropzone.classList.remove('border-gray-300', 'bg-gray-50');
            dropzone.classList.add('border-emerald-400', 'bg-emerald-50');
        } else {
            fileNameContainer.classList.add('hidden');
            fileNameContainer.classList.remove('flex');
            dropzone.classList.add('border-gray-300', 'bg-gray-50');
            dropzone.classList.remove('border-emerald-400', 'bg-emerald-50');
        }
    });
</script>
@endsection