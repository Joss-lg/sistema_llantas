@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">

    {{-- Header con acciones --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Gestión de Roles</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Administra los roles y niveles de acceso para el personal.</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Botón Volver a Empleados --}}
            <a href="{{ route('empleados.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Volver a Empleados</span>
            </a>

            {{-- Botón Nuevo Rol --}}
            <a href="{{ route('roles.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-sm text-white bg-red-600 hover:bg-red-700 shadow-md transition active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Nuevo Rol</span>
            </a>
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 mb-6 text-sm text-green-800 bg-green-50 border-l-4 border-green-600 dark:bg-green-950/40 dark:text-green-400 dark:border-green-500 rounded-lg shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 p-4 mb-6 text-sm text-red-800 bg-red-50 border-l-4 border-red-600 dark:bg-red-950/40 dark:text-red-400 dark:border-red-500 rounded-lg shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Tabla de Roles --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 font-mono">ID</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 font-mono">Nombre del Rol</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 font-mono">Usuarios Asignados</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 font-mono text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($roles as $role)
                    <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/30 transition">
                        <td class="px-6 py-4 text-xs font-mono text-gray-500 dark:text-gray-400">
                            #{{ $role->id }}
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">
                            {{ $role->nombre }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400">
                                {{ $role->usuarios_count }} usuarios
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-right space-x-3">
                            @if($role->id !== 1 && $role->nombre !== 'Administrador General')
                                <a href="{{ route('roles.edit', $role->id) }}" class="font-semibold text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                                    Editar
                                </a>

                                <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro que deseas eliminar este rol?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-semibold text-xs text-red-600 dark:text-red-400 hover:underline">
                                        Eliminar
                                    </button>
                                </form>
                            @else
                                <span class="text-xs italic text-gray-400 dark:text-gray-500">Protegido</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <circle cx="12" cy="12" r="8"/>
                                    <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span>No hay roles registrados aún.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection