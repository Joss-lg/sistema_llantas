@extends('layouts.app')

@section('content')
<style>
    ::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }
    * { scrollbar-width: none !important; -ms-overflow-style: none !important; }
    [x-cloak] { display: none !important; }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative"
     x-data="{
        cargado: false,
        modalOpen: false,
        empleadoActual: null,
        empleadoNombre: '',
        permisosEmpleado: [],
        abrirModalPermisos(id, nombre, permisos) {
            this.empleadoActual = id;
            this.empleadoNombre = nombre;
            this.permisosEmpleado = permisos;
            this.modalOpen = true;
        },
        cerrarModalPermisos() {
            this.modalOpen = false;
            this.empleadoActual = null;
        }
     }"
     x-init="setTimeout(() => cargado = true, 50)">

    <div class="relative z-10 space-y-8">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-5 transition-all duration-1000 ease-[cubic-bezier(0.16,1,0.3,1)] transform"
             :class="cargado ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 -translate-y-8 scale-95'">
            <div>
                <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight transition-colors">Gestión de Empleados</h1>
                <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1.5 font-medium transition-colors">Administra el personal, sus sucursales y permisos de acceso.</p>
            </div>

            <div class="flex items-center gap-4 w-full sm:w-auto flex-wrap">
                <a href="{{ route('empleados.inactivos') }}" class="group inline-flex items-center gap-2.5 px-4 py-2.5 bg-white dark:bg-[#121212] border border-gray-200 dark:border-neutral-800/80 text-gray-700 dark:text-neutral-300 rounded-xl text-sm font-bold hover:bg-gray-50 dark:hover:bg-[#1a1a1a] hover:border-gray-300 dark:hover:border-neutral-700 hover:text-gray-900 dark:hover:text-white transition-all duration-300 shadow-sm dark:shadow-none hover:shadow-md dark:hover:shadow-lg dark:hover:shadow-black/50 hover:-translate-y-1">
                    <svg class="w-4 h-4 text-gray-400 dark:text-neutral-500 group-hover:text-gray-600 dark:group-hover:text-neutral-300 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    Empleados Inactivos
                </a>

                <a href="{{ route('roles.index') }}" class="group inline-flex items-center gap-2.5 px-4 py-2.5 bg-white dark:bg-[#121212] border border-gray-200 dark:border-neutral-800/80 text-gray-700 dark:text-neutral-300 rounded-xl text-sm font-bold hover:bg-gray-50 dark:hover:bg-[#1a1a1a] hover:border-gray-300 dark:hover:border-neutral-700 hover:text-gray-900 dark:hover:text-white transition-all duration-300 shadow-sm dark:shadow-none hover:shadow-md dark:hover:shadow-lg dark:hover:shadow-black/50 hover:-translate-y-1">
                    <svg class="w-4 h-4 text-gray-400 dark:text-neutral-500 group-hover:text-gray-600 dark:group-hover:text-neutral-300 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Gestión de Roles
                </a>

                <a href="{{ route('empleados.create') }}" class="group inline-flex items-center gap-2.5 px-5 py-2.5 bg-[#D32030] text-white rounded-xl text-sm font-bold hover:bg-[#E82536] active:scale-95 transition-all duration-300 shadow-[0_4px_14px_rgba(211,32,48,0.25)] dark:shadow-[0_0_15px_rgba(211,32,48,0.3)] hover:shadow-[0_6px_20px_rgba(211,32,48,0.4)] dark:hover:shadow-[0_0_25px_rgba(211,32,48,0.5)] hover:-translate-y-1">
                    <svg class="w-4 h-4 transition-transform duration-500 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nuevo Empleado
                </a>
            </div>
        </div>

        {{-- Alertas --}}
        <div class="transition-all duration-700 ease-out transform delay-150" :class="cargado ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-10'">
            @if(session('success'))
                <div class="bg-emerald-50 dark:bg-[#0f1f17] border border-emerald-200 dark:border-emerald-900/50 p-4 rounded-xl flex items-center gap-3 mb-6 shadow-sm dark:shadow-lg dark:shadow-emerald-900/10 transition-colors">
                    <div class="p-1.5 bg-emerald-100 dark:bg-emerald-900/40 rounded-lg">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-emerald-800 dark:text-emerald-400">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 dark:bg-[#2a1315] border border-red-200 dark:border-red-900/50 p-4 rounded-xl flex items-center gap-3 mb-6 shadow-sm dark:shadow-lg dark:shadow-red-900/10 transition-colors">
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

            <div class="w-full overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden rounded-2xl">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50/80 dark:bg-[#121212] border-b border-gray-200 dark:border-neutral-800/50 transition-colors">
                            <th class="px-6 py-5 text-[10px] font-black text-gray-500 dark:text-neutral-500 uppercase tracking-[0.2em]">Empleado</th>
                            <th class="px-6 py-5 text-[10px] font-black text-gray-500 dark:text-neutral-500 uppercase tracking-[0.2em]">Sucursal</th>
                            <th class="px-6 py-5 text-[10px] font-black text-gray-500 dark:text-neutral-500 uppercase tracking-[0.2em]">Rol</th>
                            <th class="px-6 py-5 text-[10px] font-black text-gray-500 dark:text-neutral-500 uppercase tracking-[0.2em]">Estado</th>
                            <th class="px-6 py-5 text-[10px] font-black text-gray-500 dark:text-neutral-500 uppercase tracking-[0.2em]">Permisos</th>
                            <th class="px-6 py-5 text-[10px] font-black text-gray-500 dark:text-neutral-500 uppercase tracking-[0.2em] text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-neutral-800/30">
                        @forelse($empleados as $empleado)
                            <tr class="group hover:bg-gray-50/50 dark:hover:bg-[#151515] transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-[inset_3px_0_0_0_#D32030] hover:z-10 relative"
                                :class="cargado ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-8'"
                                style="transition-delay: {{ ($loop->index * 60) + 400 }}ms">

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-[#1a1a1a] border border-gray-200 dark:border-neutral-700/50 text-gray-600 dark:text-neutral-300 flex items-center justify-center font-black text-sm shrink-0 group-hover:bg-white dark:group-hover:bg-[#222] group-hover:border-gray-300 dark:group-hover:border-neutral-600 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 shadow-sm dark:shadow-inner">
                                            {{ strtoupper(substr($empleado->name, 0, 1)) }}
                                        </div>
                                        <div class="flex flex-col justify-center">
                                            <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-[#D32030] dark:group-hover:text-[#D32030] transition-colors duration-300">{{ $empleado->name }}</p>
                                            <p class="text-xs font-semibold text-gray-500 dark:text-neutral-500 mt-0.5">{{ $empleado->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-md text-[11px] font-bold bg-gray-100 dark:bg-[#141414] text-gray-600 dark:text-neutral-300 border border-gray-200 dark:border-neutral-800/80 group-hover:border-gray-300 dark:group-hover:border-neutral-700 transition-colors">
                                        {{ $empleado->sucursal ? $empleado->sucursal->nombre : 'Sin asignación' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-md text-[11px] font-bold bg-gray-100 dark:bg-[#141414] text-gray-600 dark:text-neutral-300 border border-gray-200 dark:border-neutral-800/80 group-hover:border-gray-300 dark:group-hover:border-neutral-700 transition-colors">
                                        {{ $empleado->role ? $empleado->role->nombre : ($empleado->rol ? $empleado->rol->nombre : 'Sin rol') }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    @if($empleado->activo)
                                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-emerald-50 dark:bg-[#0a1510] text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/30">
                                            <span class="relative flex h-1.5 w-1.5">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60"></span>
                                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                                            </span>
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-red-50 dark:bg-[#1a0c0e] text-red-700 dark:text-red-400 border border-red-200 dark:border-red-900/30">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500 shadow-[0_0_5px_rgba(239,68,68,0.5)] dark:shadow-[0_0_5px_#ef4444]"></span>
                                            Inactivo
                                        </span>
                                    @endif
                                </td>

                                {{-- Permisos: botón pill "Configurar" --}}
                                <td class="px-6 py-4">
                                    <button type="button"
                                            @click="abrirModalPermisos({{ $empleado->id }}, '{{ addslashes($empleado->name) }}', {{ $empleado->permisos->pluck('id')->values()->toJson() }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-500/20 hover:bg-blue-100 dark:hover:bg-blue-500/20 hover:border-blue-300 dark:hover:border-blue-500/40 transition-all duration-200 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                        Configurar
                                    </button>
                                </td>

                                {{-- Acciones: Editar + Switch Habilitar/Deshabilitar --}}
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">

                                        {{-- Editar --}}
                                        <a href="{{ route('empleados.edit', $empleado->id) }}"
                                           title="Editar"
                                           class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-500/20 hover:bg-blue-100 dark:hover:bg-blue-500/20 hover:border-blue-300 dark:hover:border-blue-500/40 transition-all duration-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        @if($empleado->email !== 'admin@llantas.com')
                                            {{-- Habilitar / Deshabilitar (switch con animación completa antes de enviar) --}}
                                            <form action="{{ route('empleados.toggle', $empleado->id) }}"
                                                  method="POST"
                                                  class="inline-block"
                                                  title="{{ $empleado->activo ? 'Deshabilitar' : 'Habilitar' }}"
                                                  x-data="{ on: {{ $empleado->activo ? 'true' : 'false' }} }"
                                                  @submit.prevent="on = !on; setTimeout(() => $el.submit(), 250)">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="relative inline-flex items-center w-11 h-6 rounded-full transition-colors duration-200 cursor-pointer" :class="on ? 'bg-[#D32030]' : 'bg-gray-200 dark:bg-neutral-700'">
                                                    <span class="inline-block w-5 h-5 bg-white rounded-full shadow-sm transform transition-transform duration-200" :class="on ? 'translate-x-5' : 'translate-x-0.5'"></span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-24 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400 dark:text-neutral-500 transition-all duration-1000 transform" :class="cargado ? 'opacity-100 scale-100' : 'opacity-0 scale-95'" style="transition-delay: 500ms;">
                                        <div class="p-5 bg-gray-50 dark:bg-[#121212] rounded-2xl mb-4 border border-gray-100 dark:border-neutral-800 shadow-inner">
                                            <svg class="w-8 h-8 text-gray-300 dark:opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white tracking-wide">Sin empleados</h3>
                                        <p class="text-xs font-semibold mt-1 text-gray-500 dark:text-neutral-500 dark:opacity-70">No hay registros en la base de datos.</p>
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
    {{-- MODAL: Configurar Permisos    --}}
    {{-- ============================= --}}
    <div x-show="modalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4">

        {{-- Overlay --}}
        <div x-show="modalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="cerrarModalPermisos()"
             class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

        {{-- Contenido --}}
        <div x-show="modalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white dark:bg-[#151515] rounded-2xl border border-gray-200 dark:border-neutral-800 shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col">

            <form :action="`/empleados/${empleadoActual}/permisos`" method="POST" class="flex flex-col max-h-[85vh]">
                @csrf
                @method('PUT')

                {{-- Header del Modal --}}
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-neutral-800 shrink-0">
                    <div>
                        <h2 class="font-bold text-lg text-gray-900 dark:text-white">Configurar Permisos</h2>
                        <p class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">Empleado: <span class="font-semibold text-[#D32030]" x-text="empleadoNombre"></span></p>
                    </div>
                    <button type="button" @click="cerrarModalPermisos()" class="text-gray-400 hover:text-gray-700 dark:hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Cuerpo: Lista de Permisos --}}
                <div class="px-6 py-5 overflow-y-auto grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach(\App\Models\Permiso::all()->groupBy('modulo') as $modulo => $permisos)
                        <div class="bg-gray-50/50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl p-4">
                            <h3 class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-neutral-500 mb-3 border-b border-gray-200 dark:border-neutral-800 pb-2">
                                {{ $modulo }}
                            </h3>
                            <div class="space-y-2.5">
                                @foreach($permisos as $permiso)
                                    <label class="flex items-start gap-2.5 cursor-pointer group">
                                        <div class="relative flex items-center mt-0.5">
                                            <input type="checkbox" name="permisos[]" value="{{ $permiso->id }}"
                                                   :checked="permisosEmpleado.includes({{ $permiso->id }})"
                                                   class="peer appearance-none w-4 h-4 border-2 border-gray-300 dark:border-neutral-600 rounded bg-white dark:bg-[#151515] checked:bg-[#D32030] checked:border-[#D32030] focus:outline-none focus:ring-2 focus:ring-red-500/30 transition-all cursor-pointer">
                                            <svg class="absolute inset-0 w-4 h-4 text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors leading-tight select-none">
                                            {{ $permiso->nombre }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Footer del Modal --}}
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-neutral-800 shrink-0">
                    <button type="button" @click="cerrarModalPermisos()" class="px-5 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-[#151515] border border-gray-300 dark:border-neutral-800 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-800 transition-all duration-200">
                        Cancelar
                    </button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-[#D32030] rounded-xl shadow-lg shadow-red-500/20 hover:bg-[#B91C2C] transition-all duration-200">
                        Guardar Permisos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection