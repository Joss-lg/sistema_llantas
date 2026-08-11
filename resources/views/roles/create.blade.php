@extends('layouts.app')

@section('content')
<!-- Contenedor principal con el estado de carga y contexto relativo para el fondo -->
<div class="max-w-2xl mx-auto transition-colors duration-300 relative px-4 py-6" x-data="{ cargado: false }" x-init="setTimeout(() => cargado = true, 50)">

    {{-- Fondo animado tipo Aurora --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0 opacity-20 dark:opacity-10 transition-opacity duration-500" aria-hidden="true">
        <div class="absolute -top-[160px] -left-[80px] w-[420px] h-[420px] rounded-full bg-[#D32030] blur-[90px] animate-[pulse_16s_ease-in-out_infinite]"></div>
        <div class="absolute top-[40px] -right-[140px] w-[420px] h-[420px] rounded-full bg-blue-500 blur-[90px] animate-[pulse_20s_ease-in-out_infinite]" style="animation-delay: -6s;"></div>
    </div>

    <!-- Contenido principal que va por encima del fondo (z-10) -->
    <div class="relative z-10 space-y-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 transform transition-all duration-700 ease-out"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white transition-colors">Nuevo Rol</h1>
                <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1 transition-colors">Registra un nuevo rol para asignar a los usuarios del sistema.</p>
            </div>
            <a href="{{ route('roles.index') }}" class="group inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-[#151515] border border-gray-300 dark:border-neutral-800 shadow-sm hover:bg-gray-50 dark:hover:bg-neutral-800 transition-all duration-200 active:scale-95">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Volver</span>
            </a>
        </div>

        {{-- Alertas de Error --}}
        @if($errors->any())
            <div class="flex items-start gap-3 p-4 mb-6 text-sm text-red-800 bg-red-50 border-l-4 border-red-500 dark:bg-red-900/20 dark:text-red-400 dark:border-red-500 rounded-r-xl shadow-sm transform transition-all duration-700 ease-out delay-100"
                 :class="cargado ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-10'">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="space-y-1">
                    @foreach($errors->all() as $error)
                        <p class="font-medium">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Formulario --}}
        <div class="bg-white dark:bg-[#151515] border border-gray-200 dark:border-neutral-800 rounded-2xl shadow-sm p-6 md:p-8 transform transition-all duration-700 ease-out delay-200"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf

                <div class="mb-8 space-y-1.5">
                    <label for="nombre" class="block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-neutral-400 transition-colors">
                        Nombre del Rol <span class="text-[#D32030] dark:text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="nombre" 
                           name="nombre" 
                           value="{{ old('nombre') }}" 
                           required 
                           placeholder="Ej. Mecánico Jefe, Vendedor, Contador..." 
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-neutral-800 bg-gray-50 dark:bg-[#0A0A0A] text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-neutral-600 text-sm focus:outline-none focus:ring-2 focus:ring-[#D32030] transition-all duration-200 shadow-sm hover:border-gray-400 dark:hover:border-neutral-700">
                </div>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-neutral-800 transition-colors">
                    <a href="{{ route('roles.index') }}" class="px-5 py-2.5 rounded-xl font-semibold text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-[#151515] border border-gray-300 dark:border-neutral-800 shadow-sm hover:bg-gray-50 dark:hover:bg-neutral-800 transition-all duration-200 active:scale-95">
                        Cancelar
                    </a>
                    <button type="submit" class="group relative overflow-hidden px-6 py-2.5 rounded-xl font-semibold text-sm text-white bg-[#D32030] shadow-lg shadow-red-500/20 hover:bg-[#B91C2C] hover:shadow-xl hover:shadow-red-500/30 hover:-translate-y-0.5 transition-all duration-200 active:scale-95">
                        <span class="absolute top-0 left-[-100%] w-1/2 h-full bg-gradient-to-r from-transparent via-white/20 to-transparent skew-x-[-20deg] group-hover:left-[200%] transition-all duration-700 ease-out"></span>
                        Guardar Rol
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection