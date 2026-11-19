@extends('layouts.app')

@section('header_title', 'Empleados inactivos')

@section('content')

<style>[x-cloak] { display: none !important; }</style>

<div class="max-w-7xl mx-auto space-y-6"
     x-data="{
        modalEliminar: false,
        urlEliminar: '',
        nombreEliminar: ''
     }"
     @keydown.escape.window="modalEliminar = false">

    {{-- Aviso de éxito --}}
    @if(session('success'))
        <div class="flex items-start gap-3 p-4 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-xl text-sm font-medium">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Aviso de error --}}
    @if(session('error'))
        <div class="flex items-start gap-3 p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-xl text-sm font-medium">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Personal deshabilitado. Puedes habilitarlo de nuevo o, si no tiene historial, eliminarlo.</p>
        <a href="{{ route('empleados.index') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Volver a empleados
        </a>
    </div>

    {{-- TARJETAS --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Inactivos</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($totalInactivos) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ $busqueda !== '' ? 'que coinciden con la búsqueda' : 'empleados deshabilitados' }}</p>
        </div>
        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Activos</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($totalActivos) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">empleados con acceso</p>
        </div>
    </div>

    {{-- BUSCADOR --}}
    <form method="GET" action="{{ route('empleados.inactivos') }}"
          class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5 sm:p-6">
        <label for="filter_q" class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Buscar empleado inactivo</label>
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                <input type="text" id="filter_q" name="q" value="{{ $busqueda }}" placeholder="Nombre o correo..."
                       class="w-full pl-9 pr-3 py-2.5 bg-gray-50 dark:bg-white/[0.03] border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white text-sm font-medium rounded-xl outline-none placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
            </div>
            <div class="flex gap-3">
                @if($busqueda !== '')
                    <a href="{{ route('empleados.inactivos') }}"
                       class="flex-1 sm:flex-none text-center px-5 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                        Limpiar
                    </a>
                @endif
                <button type="submit"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors">
                    Buscar
                </button>
            </div>
        </div>
    </form>

    {{-- TABLA --}}
    <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/[0.03] text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-white/10">
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Empleado</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Sucursal</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Rol</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700 dark:text-gray-300 divide-y divide-gray-100 dark:divide-white/10">
                    @forelse($empleados as $empleado)
                        @php
                            $esAdminFila  = $empleado->role && $empleado->role->nombre === $rolAdmin;
                            $sinAcceso    = $esAdminFila && ! $puedeGestionarAdmins;
                            $tieneHistorial = in_array($empleado->id, $conHistorial, true);
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors">

                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-sm font-bold bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-gray-400">
                                        {{ mb_strtoupper(mb_substr($empleado->name, 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-white">
                                            {{ $empleado->name }}
                                            <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-500/20">Inactivo</span>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $empleado->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($empleado->sucursal)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10">{{ $empleado->sucursal->nombre }}</span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">Sin asignar</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($empleado->role)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10">{{ $empleado->role->nombre }}</span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">Sin rol</span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @if($sinAcceso)
                                        <span class="text-xs text-gray-400 dark:text-gray-500">Solo un administrador</span>
                                    @else
                                        <form action="{{ route('empleados.toggle', $empleado->id) }}" method="POST"
                                              x-data="{ enviando: false }" @submit="enviando = true">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" :disabled="enviando"
                                                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition-colors disabled:opacity-60">
                                                Habilitar
                                            </button>
                                        </form>

                                        @if($tieneHistorial)
                                            <span class="text-xs text-gray-400 dark:text-gray-500 max-w-[9rem] leading-tight" title="Tiene ventas o movimientos registrados">Con historial: no se elimina</span>
                                        @elseif($empleado->id !== auth()->id())
                                            <button type="button"
                                                    @click="urlEliminar = @js(route('empleados.destroy', $empleado->id)); nombreEliminar = @js($empleado->name); modalEliminar = true"
                                                    class="inline-flex items-center justify-center w-9 h-9 rounded-full text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors" title="Eliminar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 mb-4">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                @if($busqueda !== '')
                                    <p class="text-base font-bold text-gray-800 dark:text-gray-200">Sin resultados</p>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 max-w-sm mx-auto">Ningún empleado inactivo coincide con tu búsqueda.</p>
                                @else
                                    <p class="text-base font-bold text-gray-800 dark:text-gray-200">Sin empleados inactivos</p>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 max-w-sm mx-auto">Todo el personal está activo.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($empleados->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-white/10">
                {{ $empleados->links() }}
            </div>
        @endif
    </div>

    {{-- VENTANA: eliminar empleado --}}
    <div x-show="modalEliminar" x-cloak
         class="fixed inset-0 !mt-0 z-[150] bg-gray-900/50 flex items-center justify-center p-4">
        <div @click.away="modalEliminar = false"
             class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-start gap-4">
                <span class="w-11 h-11 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">¿Eliminar empleado?</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Vas a eliminar a <span class="font-bold text-gray-900 dark:text-white" x-text="nombreEliminar"></span>. Esta acción no se puede deshacer.
                    </p>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Si ya tiene ventas o movimientos registrados no se puede eliminar, para conservar el historial.</p>
                </div>
            </div>
            <form :action="urlEliminar" method="POST" x-data="{ enviando: false }" @submit="enviando = true"
                  class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="modalEliminar = false"
                        class="px-5 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    Cancelar
                </button>
                <button type="submit" :disabled="enviando"
                        class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold text-sm rounded-xl transition-colors disabled:opacity-60">
                    Sí, eliminar
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
