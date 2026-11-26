@extends('layouts.app')

@section('header_title', 'Roles')

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
        <p class="text-sm text-gray-500 dark:text-gray-400">Los roles son etiquetas para ordenar al personal. Los permisos se dan a cada empleado; solo el rol Administrador General tiene acceso total.</p>
        <div class="flex flex-col sm:flex-row gap-3 shrink-0">
            <a href="{{ route('empleados.index') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver a empleados
            </a>
            <a href="{{ route('roles.create') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo rol
            </a>
        </div>
    </div>

    {{-- TARJETAS --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Roles</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($totalRoles) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">roles creados</p>
        </div>

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Empleados con rol</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($totalUsuarios) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">activos e inactivos</p>
        </div>

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Sin empleados</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($sinUsuarios) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">roles que se pueden eliminar</p>
        </div>
    </div>

    {{-- TABLA --}}
    <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/[0.03] text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-white/10">
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Rol</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Empleados</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700 dark:text-gray-300 divide-y divide-gray-100 dark:divide-white/10">
                    @forelse($roles as $role)
                        @php $protegido = $role->nombre === $rolAdmin; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors">

                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-sm font-bold {{ $protegido ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : 'bg-gray-100 dark:bg-white/5 text-gray-600 dark:text-gray-300' }}">
                                        {{ mb_strtoupper(mb_substr($role->nombre, 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-white">
                                            {{ $role->nombre }}
                                            @if($protegido)
                                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/20">Sistema</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">#{{ str_pad($role->id, 3, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10 tabular-nums">
                                    {{ $role->usuarios_count }} {{ $role->usuarios_count === 1 ? 'empleado' : 'empleados' }}
                                </span>
                                @if(! $protegido && $role->usuarios_count > 0)
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-white/10"
                                          title="Cámbiales el rol a sus empleados para poder eliminarlo">En uso</span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    @if($protegido)
                                        <span class="text-xs text-gray-400 dark:text-gray-500">Protegido</span>
                                    @else
                                        <a href="{{ route('roles.edit', $role->id) }}" title="Editar"
                                           class="inline-flex items-center justify-center w-9 h-9 rounded-full text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>

                                        @if($role->usuarios_count > 0)
                                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-full text-gray-300 dark:text-gray-600 cursor-not-allowed"
                                                  title="No se puede eliminar: tiene empleados. Cámbiales el rol primero." aria-label="No se puede eliminar: tiene empleados">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </span>
                                        @else
                                            <button type="button"
                                                    @click="urlEliminar = @js(route('roles.destroy', $role->id)); nombreEliminar = @js($role->nombre); modalEliminar = true"
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
                            <td colspan="3" class="px-6 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 mb-4">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                </div>
                                <p class="text-base font-bold text-gray-800 dark:text-gray-200">Sin roles</p>
                                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 max-w-sm mx-auto">Crea el primero con el botón "Nuevo rol".</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- VENTANA: eliminar rol --}}
    <div x-show="modalEliminar" x-cloak
         class="fixed inset-0 !mt-0 z-[150] bg-gray-900/50 flex items-center justify-center p-4">
        <div @click.away="modalEliminar = false"
             class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-start gap-4">
                <span class="w-11 h-11 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">¿Eliminar rol?</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Vas a eliminar el rol <span class="font-bold text-gray-900 dark:text-white" x-text="nombreEliminar"></span>. Esta acción no se puede deshacer.
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
