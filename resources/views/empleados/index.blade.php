@extends('layouts.app')

@section('header_title', 'Empleados')

@section('content')

<style>[x-cloak] { display: none !important; }</style>

<div class="max-w-7xl mx-auto space-y-6"
     x-data="{
        modalPermisos: false,
        urlPermisos: '',
        empleadoNombre: '',
        permisosEmpleado: [],
        abrirPermisos(url, nombre, permisos) {
            this.urlPermisos = url;
            this.empleadoNombre = nombre;
            this.permisosEmpleado = permisos;
            this.modalPermisos = true;
        }
     }"
     @keydown.escape.window="modalPermisos = false">

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
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Administra al personal, sus sucursales, roles y permisos de acceso.</p>
        <div class="flex flex-col sm:flex-row gap-3 shrink-0">
            <a href="{{ route('empleados.inactivos') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                Empleados inactivos
            </a>
            <a href="{{ route('roles.index') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                Gestión de roles
            </a>
            <a href="{{ route('empleados.create') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo empleado
            </a>
        </div>
    </div>

    {{-- TARJETAS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

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

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Administradores</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($totalAdmins) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">con acceso total</p>
        </div>

        <a href="{{ route('empleados.inactivos') }}"
           class="block bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5 hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Inactivos</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($totalInactivos) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">deshabilitados (ver lista)</p>
        </a>

        <a href="{{ route('roles.index') }}"
           class="block bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5 hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Roles</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($totalRoles) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">roles creados (ver lista)</p>
        </a>
    </div>
    <p class="-mt-3 text-xs text-gray-400 dark:text-gray-500">Activos y administradores cuentan a todos los empleados del filtro, no solo a los de esta página.</p>

    {{-- BUSCADOR --}}
    <form method="GET" action="{{ route('empleados.index') }}"
          class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5 sm:p-6">
        <label for="filter_q" class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Buscar empleado</label>
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                <input type="text" id="filter_q" name="q" value="{{ $busqueda }}" placeholder="Nombre o correo..."
                       class="w-full pl-9 pr-3 py-2.5 bg-gray-50 dark:bg-white/[0.03] border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white text-sm font-medium rounded-xl outline-none placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
            </div>
            <div class="flex gap-3">
                @if($busqueda !== '')
                    <a href="{{ route('empleados.index') }}"
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
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Permisos</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700 dark:text-gray-300 divide-y divide-gray-100 dark:divide-white/10">
                    @forelse($empleados as $empleado)
                        @php
                            $esPrincipal  = $empleado->email === $adminPrincipal;
                            $esYo         = $empleado->id === auth()->id();
                            $esAdminFila  = $empleado->role && $empleado->role->nombre === $rolAdmin;
                            $sinAcceso    = $esAdminFila && ! $puedeGestionarAdmins; // no es administrador y la fila es de uno
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors">

                            {{-- Empleado --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-sm font-bold {{ $esAdminFila ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : 'bg-gray-100 dark:bg-white/5 text-gray-600 dark:text-gray-300' }}">
                                        {{ mb_strtoupper(mb_substr($empleado->name, 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-white">
                                            {{ $empleado->name }}
                                            @if($esYo)
                                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/20">Tú</span>
                                            @endif
                                            @if($esPrincipal)
                                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/20">Principal</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $empleado->email }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Sucursal --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($empleado->sucursal)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10">{{ $empleado->sucursal->nombre }}</span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">Sin asignar</span>
                                @endif
                            </td>

                            {{-- Rol --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($empleado->role)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $esAdminFila ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-500/20' : 'bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-white/10' }}">{{ $empleado->role->nombre }}</span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">Sin rol</span>
                                @endif
                            </td>

                            {{-- Permisos --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($esAdminFila)
                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Acceso total</span>
                                @elseif($sinAcceso)
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                @else
                                    <button type="button"
                                            @click="abrirPermisos(@js(route('empleados.permisos.update', $empleado->id)), @js($empleado->name), @js($empleado->permisos->pluck('id')->values()))"
                                            class="inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-xs rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        Configurar
                                        <span class="tabular-nums text-gray-400 dark:text-gray-500">({{ $empleado->permisos->count() }})</span>
                                    </button>
                                @endif
                            </td>

                            {{-- Acciones --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    @if($sinAcceso)
                                        <span class="text-xs text-gray-400 dark:text-gray-500">Solo un administrador</span>
                                    @else
                                        <a href="{{ route('empleados.edit', $empleado->id) }}" title="Editar"
                                           class="inline-flex items-center justify-center w-9 h-9 rounded-full text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>

                                        @if(! $esPrincipal && ! $esYo)
                                            <form action="{{ route('empleados.toggle', $empleado->id) }}" method="POST"
                                                  x-data="{ enviando: false }" @submit="enviando = true">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" :disabled="enviando" title="Deshabilitar"
                                                        class="inline-flex items-center justify-center w-9 h-9 rounded-full text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-colors disabled:opacity-50">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 mb-4">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                @if($busqueda !== '')
                                    <p class="text-base font-bold text-gray-800 dark:text-gray-200">Sin resultados</p>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 max-w-sm mx-auto">Ningún empleado activo coincide con tu búsqueda.</p>
                                @else
                                    <p class="text-base font-bold text-gray-800 dark:text-gray-200">Sin empleados activos</p>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 max-w-sm mx-auto">Registra al primer empleado con el botón "Nuevo empleado".</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if($empleados->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-white/10">
                {{ $empleados->links() }}
            </div>
        @endif
    </div>

    {{-- VENTANA: configurar permisos --}}
    <div x-show="modalPermisos" x-cloak
         class="fixed inset-0 !mt-0 z-50 bg-gray-900/50 flex items-center justify-center p-4">
        <div @click.away="modalPermisos = false"
             class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-xl w-full max-w-3xl max-h-[90vh] flex flex-col">
            <form :action="urlPermisos" method="POST" x-data="{ enviando: false }" @submit="enviando = true"
                  class="flex flex-col min-h-0 flex-1">
                @csrf
                @method('PUT')

                <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-gray-100 dark:border-white/10 shrink-0">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Configurar permisos</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Empleado: <span class="font-semibold text-gray-900 dark:text-white" x-text="empleadoNombre"></span></p>
                    </div>
                    <button type="button" @click="modalPermisos = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors" title="Cerrar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-4 overflow-y-auto flex-1 min-h-0">
                    @foreach($permisosGrouped as $modulo => $permisos)
                        <div x-data="{
                                moduloIds: @js($permisos->pluck('id')->values()),
                                get todos() { return this.moduloIds.length > 0 && this.moduloIds.every(id => permisosEmpleado.includes(id)); },
                                alternarTodos() {
                                    if (this.todos) {
                                        permisosEmpleado = permisosEmpleado.filter(id => !this.moduloIds.includes(id));
                                    } else {
                                        permisosEmpleado = [...permisosEmpleado, ...this.moduloIds.filter(id => !permisosEmpleado.includes(id))];
                                    }
                                }
                             }"
                             class="border border-gray-200 dark:border-white/10 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100 dark:border-white/10">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $modulo }}</h4>
                                <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 cursor-pointer select-none">
                                    Todos
                                    <input type="checkbox" @change="alternarTodos()" :checked="todos" class="w-4 h-4 rounded accent-indigo-600">
                                </label>
                            </div>
                            <div class="space-y-2">
                                @foreach($permisos as $permiso)
                                    <label class="flex items-start gap-2.5 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                        <input type="checkbox" name="permisos[]" value="{{ $permiso->id }}"
                                               x-model.number="permisosEmpleado"
                                               class="w-4 h-4 mt-0.5 rounded accent-indigo-600 shrink-0">
                                        <span class="leading-tight">{{ $permiso->nombre }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-white/10 shrink-0">
                    <button type="button" @click="modalPermisos = false"
                            class="px-5 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="enviando"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors disabled:opacity-60">
                        Guardar permisos
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
