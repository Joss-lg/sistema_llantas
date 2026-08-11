@extends('layouts.app')

@section('content')

<style>
    @keyframes rowIn {
        from { opacity: 0; transform: translateX(-6px); }
        to { opacity: 1; transform: translateX(0); }
    }
    .row-anim { animation: rowIn 0.35s ease-out both; opacity: 0; }
    
    @media (prefers-reduced-motion: reduce) {
        .row-anim { animation: none !important; opacity: 1 !important; }
    }
</style>

<!-- Contenedor principal con el estado de carga y contexto relativo para el fondo -->
<div class="max-w-5xl mx-auto transition-colors duration-300 relative px-4 py-6" x-data="{ cargado: false }" x-init="setTimeout(() => cargado = true, 50)">

    {{-- Fondo animado tipo Aurora --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0 opacity-20 dark:opacity-10 transition-opacity duration-500" aria-hidden="true">
        <div class="absolute -top-[160px] -left-[80px] w-[420px] h-[420px] rounded-full bg-[#D32030] blur-[90px] animate-[pulse_16s_ease-in-out_infinite]"></div>
        <div class="absolute top-[40px] -right-[140px] w-[420px] h-[420px] rounded-full bg-blue-500 blur-[90px] animate-[pulse_20s_ease-in-out_infinite]" style="animation-delay: -6s;"></div>
    </div>

    <!-- Contenido principal que va por encima del fondo (z-10) -->
    <div class="relative z-10 space-y-6">

        {{-- Header con acciones --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 transform transition-all duration-700 ease-out"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white transition-colors">Gestión de Roles</h1>
                <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1 transition-colors">Administra los roles y niveles de acceso para el personal.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                {{-- Botón Volver a Empleados --}}
                <a href="{{ route('empleados.index') }}" class="group inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-[#151515] border border-gray-300 dark:border-neutral-800 shadow-sm hover:bg-gray-50 dark:hover:bg-neutral-800 transition-all duration-200 active:scale-95">
                    <svg class="w-5 h-5 text-gray-400 dark:text-neutral-500 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Volver a Empleados</span>
                </a>

                {{-- Botón Nuevo Rol --}}
                <a href="{{ route('roles.create') }}" class="group inline-flex items-center gap-2 px-5 py-2.5 bg-[#D32030] text-white rounded-xl text-sm font-semibold hover:bg-[#B91C2C] active:scale-95 transition-all duration-200 shadow-lg shadow-red-500/20 hover:shadow-xl hover:shadow-red-500/30 hover:-translate-y-0.5">
                    <svg class="w-5 h-5 transition-transform duration-300 group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Nuevo Rol</span>
                </a>
            </div>
        </div>

        {{-- Alertas Animadas --}}
        <div class="transform transition-all duration-700 ease-out delay-100" :class="cargado ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-10'">
            @if(session('success'))
                <div class="bg-emerald-50 dark:bg-emerald-900/20 border-l-4 border-emerald-500 dark:border-emerald-500 p-4 rounded-r-xl shadow-sm flex items-center gap-3 mb-4">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="text-sm font-medium text-emerald-800 dark:text-emerald-300">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 dark:border-red-500 p-4 rounded-r-xl shadow-sm flex items-center gap-3 mb-4">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span class="text-sm font-medium text-red-800 dark:text-red-300">{{ session('error') }}</span>
                </div>
            @endif
        </div>

        {{-- Tabla de Roles --}}
        <div class="bg-white dark:bg-[#151515] border border-gray-200 dark:border-neutral-800 rounded-2xl shadow-sm overflow-hidden transform transition-all duration-700 ease-out delay-200"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
             
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-[#0A0A0A] border-b border-gray-100 dark:border-neutral-800 transition-colors">
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-neutral-500 font-mono">ID</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-neutral-500 font-mono">Nombre del Rol</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-neutral-500 font-mono">Usuarios Asignados</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-neutral-500 font-mono text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                        @forelse($roles as $role)
                            @php
                                $delayFila = $loop->index * 0.05; // Cascadas dinámicas para las filas
                            @endphp
                            <tr class="group hover:bg-gray-50/70 dark:hover:bg-neutral-800/40 transition-colors duration-200 relative row-anim" style="animation-delay: {{ $delayFila + 0.3 }}s">
                                
                                {{-- Borde lateral rojo on hover --}}
                                <span class="absolute left-0 top-0 h-full w-0.5 bg-[#D32030] scale-y-0 group-hover:scale-y-100 origin-center transition-transform duration-200"></span>
                                
                                <td class="px-6 py-4 text-xs font-mono text-gray-400 dark:text-neutral-500 transition-colors">
                                    #{{ str_pad($role->id, 3, '0', STR_PAD_LEFT) }}
                                </td>
                                
                                <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-gray-100 transition-colors">
                                    {{ $role->nombre }}
                                    @if($role->id === 1 || $role->nombre === 'Administrador General')
                                        <span class="ml-2 inline-flex items-center gap-1 text-[9px] font-bold text-[#D32030] dark:text-red-500 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-900/50 px-1.5 py-0.5 rounded-full uppercase tracking-wider">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            Sistema
                                        </span>
                                    @endif
                                </td>
                                
                                <td class="px-6 py-4 text-sm">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-900/50 transition-transform duration-200 group-hover:-translate-y-0.5">
                                        {{ $role->usuarios_count }} usuarios
                                    </span>
                                </td>
                                
                                <td class="px-6 py-4 text-sm text-right">
                                    <div class="flex items-center justify-end gap-4">
                                        @if($role->id !== 1 && $role->nombre !== 'Administrador General')
                                            <a href="{{ route('roles.edit', $role->id) }}" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors relative after:absolute after:bottom-0 after:left-0 after:w-full after:h-[1px] after:bg-current after:scale-x-0 hover:after:scale-x-100 after:transition-transform after:origin-left">
                                                Editar
                                            </a>

                                            <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro que deseas eliminar este rol?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-semibold text-[#D32030] dark:text-red-500 hover:text-red-800 dark:hover:text-red-400 transition-colors relative after:absolute after:bottom-0 after:left-0 after:w-full after:h-[1px] after:bg-current after:scale-x-0 hover:after:scale-x-100 after:transition-transform after:origin-left cursor-pointer">
                                                    Eliminar
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs font-medium italic text-gray-400 dark:text-neutral-600 cursor-not-allowed select-none">
                                                Protegido
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400 dark:text-neutral-500">
                                        <svg class="w-12 h-12 mb-3 opacity-50 animate-[spin_12s_linear_infinite]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <circle cx="12" cy="12" r="8"/>
                                            <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                        <p class="text-sm font-medium">No hay roles registrados aún.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection