@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Editar Rol</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Modifica la información del rol #{{ $role->id }}.</p>
        </div>
        <a href="{{ route('roles.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition active:scale-95">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Volver</span>
        </a>
    </div>

    {{-- Alertas de Error --}}
    @if($errors->any())
        <div class="flex items-start gap-3 p-4 mb-6 text-sm text-red-800 bg-red-50 border-l-4 border-red-600 dark:bg-red-950/40 dark:text-red-400 dark:border-red-500 rounded-lg shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="space-y-1">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Formulario --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm p-6 md:p-8">
        <form action="{{ route('roles.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="nombre" class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 tracking-wider mb-2">
                    Nombre del Rol <span class="text-red-600 dark:text-red-500">*</span>
                </label>
                <input type="text" 
                       id="nombre" 
                       name="nombre" 
                       value="{{ old('nombre', $role->nombre) }}" 
                       required 
                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:outline-none focus:border-red-600 focus:ring-2 focus:ring-red-600/20 dark:focus:ring-red-500/20 transition">
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('roles.index') }}" class="px-4 py-2.5 rounded-xl font-semibold text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Cancelar
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl font-semibold text-sm text-white bg-red-600 hover:bg-red-700 shadow-md transition active:scale-95">
                    Actualizar Rol
                </button>
            </div>
        </form>
    </div>

</div>
@endsection