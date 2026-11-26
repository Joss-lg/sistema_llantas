@extends('layouts.app')

@section('header_title', 'Sucursales')

@section('content')

<style>[x-cloak] { display: none !important; }</style>

<div class="max-w-5xl mx-auto space-y-6"
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
        <p class="text-sm text-gray-500 dark:text-gray-400">Una sucursal con empleados, ventas, caja o inventario no se puede eliminar para conservar su historial: se desactiva. Solo se elimina una que nunca se usó.</p>
        <a href="{{ route('sucursales.create') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nueva sucursal
        </a>
    </div>

    {{-- TARJETAS --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2M5 21H3M9 7h1m4 0h1M9 11h1m4 0h1M9 15h1m4 0h1"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Sucursales</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($totalSucursales) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">registradas</p>
        </div>

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Activas</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($activas) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">disponibles para operar</p>
        </div>

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Inactivas</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($inactivas) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">desactivadas</p>
        </div>
    </div>

    {{-- TABLA --}}
    <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/[0.03] text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-white/10">
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Sucursal</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Empleados activos</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Estado</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700 dark:text-gray-300 divide-y divide-gray-100 dark:divide-white/10">
                    @forelse($sucursales as $sucursal)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors">

                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-sm font-bold bg-gray-100 dark:bg-white/5 text-gray-600 dark:text-gray-300">
                                        {{ mb_strtoupper(mb_substr($sucursal->nombre, 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-white">{{ $sucursal->nombre }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">#{{ str_pad($sucursal->id, 3, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10 tabular-nums">
                                    {{ $sucursal->empleados_count }} {{ $sucursal->empleados_count === 1 ? 'empleado' : 'empleados' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($sucursal->activa)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/20">Activa</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-white/10">Inactiva</span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('sucursales.edit', $sucursal->id) }}" title="Editar"
                                       class="inline-flex items-center justify-center w-9 h-9 rounded-full text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    <form action="{{ route('sucursales.toggle', $sucursal->id) }}" method="POST" x-data="{ enviando: false }" @submit="enviando = true">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" :disabled="enviando"
                                                class="px-3 py-1.5 text-xs font-bold rounded-lg border transition-colors disabled:opacity-60
                                                       {{ $sucursal->activa
                                                            ? 'text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-500/20 hover:bg-amber-50 dark:hover:bg-amber-500/10'
                                                            : 'text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-500/20 hover:bg-emerald-50 dark:hover:bg-emerald-500/10' }}">
                                            {{ $sucursal->activa ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>

                                    @if($sucursal->en_uso)
                                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full text-gray-300 dark:text-gray-600 cursor-not-allowed"
                                              title="No se puede eliminar: ya tiene empleados, ventas, caja o inventario. Solo se puede desactivar." aria-label="No se puede eliminar: ya tiene historial">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </span>
                                    @else
                                        <button type="button"
                                                @click="urlEliminar = @js(route('sucursales.destroy', $sucursal->id)); nombreEliminar = @js($sucursal->nombre); modalEliminar = true"
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-full text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors" title="Eliminar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <p class="text-base font-bold text-gray-800 dark:text-gray-200">Sin sucursales</p>
                                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 max-w-sm mx-auto">Crea la primera con el botón "Nueva sucursal".</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- VENTANA: eliminar sucursal --}}
    <div x-show="modalEliminar" x-cloak
         class="fixed inset-0 !mt-0 z-[150] bg-gray-900/50 flex items-center justify-center p-4">
        <div @click.away="modalEliminar = false"
             class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-start gap-4">
                <span class="w-11 h-11 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">¿Eliminar sucursal?</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Vas a eliminar la sucursal <span class="font-bold text-gray-900 dark:text-white" x-text="nombreEliminar"></span>. Esta acción no se puede deshacer.
                    </p>
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
