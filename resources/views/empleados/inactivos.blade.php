@extends('layouts.app')

@section('content')
<style>
    [x-cloak] { display: none !important; }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative"
     x-data="{
        cargado: false,
        deleteModalOpen: false,
        empleadoActual: null,
        empleadoNombre: '',
        abrirModalEliminar(id, nombre) {
            this.empleadoActual = id;
            this.empleadoNombre = nombre;
            this.deleteModalOpen = true;
        },
        cerrarModalEliminar() {
            this.deleteModalOpen = false;
            this.empleadoActual = null;
        }
     }"
     x-init="setTimeout(() => cargado = true, 50)">

    <div class="relative z-10 space-y-8">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-5 transition-all duration-1000 ease-[cubic-bezier(0.16,1,0.3,1)] transform"
             :class="cargado ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 -translate-y-8 scale-95'">
            <div>
                <a href="{{ route('empleados.index') }}" class="group inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 dark:text-neutral-400 hover:text-[#D32030] dark:hover:text-red-500 transition-colors mb-2">
                    <svg class="w-4 h-4 transition-transform duration-200 group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Volver a Empleados
                </a>
                <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight transition-colors">Empleados Inactivos</h1>
                <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1.5 font-medium transition-colors">Personal deshabilitado. Puedes habilitarlo de nuevo o eliminarlo permanentemente.</p>
            </div>
        </div>

        {{-- Alertas --}}
        <div class="transition-all duration-700 ease-out transform delay-150" :class="cargado ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-10'">
            @if(session('success'))
                <div class="bg-emerald-50 dark:bg-[#0f1f17] border border-emerald-200 dark:border-emerald-900/50 p-4 rounded-xl flex items-center gap-3 mb-6 shadow-sm transition-colors">
                    <div class="p-1.5 bg-emerald-100 dark:bg-emerald-900/40 rounded-lg">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-emerald-800 dark:text-emerald-400">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 dark:bg-[#2a1315] border border-red-200 dark:border-red-900/50 p-4 rounded-xl flex items-center gap-3 mb-6 shadow-sm transition-colors">
                    <div class="p-1.5 bg-red-100 dark:bg-red-900/40 rounded-lg">
                        <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-red-800 dark:text-red-400">{{ session('error') }}</span>
                </div>
            @endif
        </div>

        {{-- Tabla --}}
        <div class="bg-white dark:bg-[#0c0c0c] rounded-2xl border border-gray-200 dark:border-neutral-800/60 shadow-sm dark:shadow-2xl transition-all duration-1000 ease-[cubic-bezier(0.16,1,0.3,1)] transform delay-300"
             :class="cargado ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 translate-y-12 scale-95'">

            <div class="w-full overflow-x-auto rounded-2xl">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50/80 dark:bg-[#121212] border-b border-gray-200 dark:border-neutral-800/50 transition-colors">
                            <th class="px-6 py-5 text-[10px] font-black text-gray-500 dark:text-neutral-500 uppercase tracking-[0.2em]">Empleado</th>
                            <th class="px-6 py-5 text-[10px] font-black text-gray-500 dark:text-neutral-500 uppercase tracking-[0.2em]">Sucursal</th>
                            <th class="px-6 py-5 text-[10px] font-black text-gray-500 dark:text-neutral-500 uppercase tracking-[0.2em]">Rol</th>
                            <th class="px-6 py-5 text-[10px] font-black text-gray-500 dark:text-neutral-500 uppercase tracking-[0.2em] text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-neutral-800/30">
                        @forelse($empleados as $empleado)
                            <tr class="group hover:bg-gray-50/50 dark:hover:bg-[#151515] transition-all duration-300">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-[#1a1a1a] border border-gray-200 dark:border-neutral-700/50 text-gray-600 dark:text-neutral-300 flex items-center justify-center font-black text-sm shrink-0">
                                            {{ strtoupper(substr($empleado->name, 0, 1)) }}
                                        </div>
                                        <div class="flex flex-col justify-center">
                                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $empleado->name }}</p>
                                            <p class="text-xs font-semibold text-gray-500 dark:text-neutral-500 mt-0.5">{{ $empleado->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-md text-[11px] font-bold bg-gray-100 dark:bg-[#141414] text-gray-600 dark:text-neutral-300 border border-gray-200 dark:border-neutral-800/80">
                                        {{ $empleado->sucursal ? $empleado->sucursal->nombre : 'Sin asignación' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-md text-[11px] font-bold bg-gray-100 dark:bg-[#141414] text-gray-600 dark:text-neutral-300 border border-gray-200 dark:border-neutral-800/80">
                                        {{ $empleado->role ? $empleado->role->nombre : ($empleado->rol ? $empleado->rol->nombre : 'Sin rol') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">

                                        {{-- Habilitar (switch con animación completa antes de enviar) --}}
                                        <form action="{{ route('empleados.toggle', $empleado->id) }}"
                                              method="POST"
                                              class="inline-block"
                                              title="Habilitar"
                                              x-data="{ on: false }"
                                              @submit.prevent="on = !on; setTimeout(() => $el.submit(), 250)">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="relative inline-flex items-center w-11 h-6 rounded-full transition-colors duration-200 cursor-pointer" :class="on ? 'bg-[#D32030]' : 'bg-gray-200 dark:bg-neutral-700'">
                                                <span class="inline-block w-5 h-5 bg-white rounded-full shadow-sm transform transition-transform duration-200" :class="on ? 'translate-x-5' : 'translate-x-0.5'"></span>
                                            </button>
                                        </form>

                                        @if($empleado->email !== 'admin@llantas.com')
                                            {{-- Eliminar (abre modal de confirmación) --}}
                                            <button type="button"
                                                    @click="abrirModalEliminar({{ $empleado->id }}, '{{ addslashes($empleado->name) }}')"
                                                    title="Eliminar"
                                                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/20 hover:bg-red-100 dark:hover:bg-red-500/20 hover:border-red-300 dark:hover:border-red-500/40 transition-all duration-200 cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-24 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400 dark:text-neutral-500">
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white tracking-wide">Sin empleados inactivos</h3>
                                        <p class="text-xs font-semibold mt-1 text-gray-500 dark:text-neutral-500 dark:opacity-70">Todos tus empleados están activos actualmente.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ============================= --}}
    {{-- MODAL: Confirmar Eliminación  --}}
    {{-- ============================= --}}
    <div x-show="deleteModalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4">

        {{-- Overlay --}}
        <div x-show="deleteModalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="cerrarModalEliminar()"
             class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

        {{-- Contenido --}}
        <div x-show="deleteModalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white dark:bg-[#151515] rounded-2xl border border-gray-200 dark:border-neutral-800 shadow-2xl w-full max-w-md">

            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="shrink-0 w-12 h-12 rounded-full bg-red-50 dark:bg-red-500/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m0 3.75h.007v.008H12v-.008zM10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                        </svg>
                    </div>
                    <div class="flex-1 pt-1">
                        <h2 class="font-bold text-lg text-gray-900 dark:text-white">Eliminar empleado</h2>
                        <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">
                            ¿Seguro que deseas eliminar a <span class="font-semibold text-gray-900 dark:text-white" x-text="empleadoNombre"></span>? Esta acción no se puede deshacer.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-neutral-800">
                <button type="button" @click="cerrarModalEliminar()" class="px-5 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-[#151515] border border-gray-300 dark:border-neutral-800 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-800 transition-all duration-200">
                    Cancelar
                </button>
                <form :action="`/empleados/${empleadoActual}`" method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-[#D32030] rounded-xl shadow-lg shadow-red-500/20 hover:bg-[#B91C2C] transition-all duration-200">
                        Sí, eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection