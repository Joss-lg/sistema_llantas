@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 transition-colors duration-300 relative px-4 py-6" x-data="{ cargado: false }" x-init="setTimeout(() => cargado = true, 50)">

    {{-- Encabezado --}}
    <div class="transform transition-all duration-700 ease-out"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'">
        <a href="{{ route('empleados.index') }}" class="group inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 dark:text-neutral-400 hover:text-[#D32030] dark:hover:text-red-500 transition-colors">
            <svg class="w-4 h-4 transition-transform duration-200 group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Volver a Empleados
        </a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2 transition-colors">Editar Empleado: <span class="text-[#D32030]">{{ $empleado->name }}</span></h1>
        <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">Para modificar sus permisos, usa el botón <span class="font-semibold">"Configurar"</span> en el listado de empleados.</p>
    </div>

    <form action="{{ route('empleados.update', $empleado->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Tarjeta: Datos Personales y Acceso --}}
        <div class="bg-white dark:bg-[#151515] rounded-2xl border border-gray-200 dark:border-neutral-800 p-6 shadow-sm transform transition-all duration-700 ease-out delay-100"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">

            <h2 class="font-bold text-lg text-gray-800 dark:text-gray-100 border-b border-gray-100 dark:border-neutral-800 pb-3 mb-5 transition-colors">Información Básica</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Nombre Completo --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-neutral-400 uppercase tracking-wide transition-colors">Nombre Completo</label>
                    <input type="text" name="name" value="{{ old('name', $empleado->name) }}" required 
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-[#D32030] outline-none transition-all duration-200 shadow-sm hover:border-gray-300 dark:hover:border-neutral-700">
                    @error('name') <span class="text-xs text-[#D32030] dark:text-red-500 font-medium">{{ $message }}</span> @enderror
                </div>

                {{-- Correo Electrónico --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-neutral-400 uppercase tracking-wide transition-colors">Correo Electrónico</label>
                    <input type="email" name="email" value="{{ old('email', $empleado->email) }}" required 
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-[#D32030] outline-none transition-all duration-200 shadow-sm hover:border-gray-300 dark:hover:border-neutral-700">
                    @error('email') <span class="text-xs text-[#D32030] dark:text-red-500 font-medium">{{ $message }}</span> @enderror
                </div>

                {{-- Contraseña (Edición) --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-neutral-400 uppercase tracking-wide transition-colors">
                        Nueva Contraseña <span class="text-gray-400 dark:text-neutral-500 font-normal normal-case tracking-normal ml-1">(dejar en blanco para mantener la actual)</span>
                    </label>
                    <input type="password" name="password" 
                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-[#D32030] outline-none transition-all duration-200 shadow-sm hover:border-gray-300 dark:hover:border-neutral-700">
                    @error('password') <span class="text-xs text-[#D32030] dark:text-red-500 font-medium">{{ $message }}</span> @enderror
                </div>

                {{-- Sucursal Asignada --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-neutral-400 uppercase tracking-wide transition-colors">Sucursal Asignada</label>
                    <div class="relative group">
                        <select name="sucursal_id" required 
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-[#D32030] outline-none transition-all duration-200 shadow-sm appearance-none cursor-pointer hover:border-gray-300 dark:hover:border-neutral-700">
                            @foreach($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}" {{ old('sucursal_id', $empleado->sucursal_id) == $sucursal->id ? 'selected' : '' }}>
                                    {{ $sucursal->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-neutral-500 pointer-events-none transition-transform duration-200 group-focus-within:rotate-180 group-focus-within:text-[#D32030]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                    </div>
                </div>

                {{-- Rol --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-neutral-400 uppercase tracking-wide transition-colors">Rol</label>
                    <div class="relative group">
                        <select name="rol_id" required 
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-gray-100 rounded-xl text-sm focus:ring-2 focus:ring-[#D32030] outline-none transition-all duration-200 shadow-sm appearance-none cursor-pointer hover:border-gray-300 dark:hover:border-neutral-700">
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id }}" {{ old('rol_id', $empleado->rol_id) == $rol->id ? 'selected' : '' }}>
                                    {{ $rol->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-neutral-500 pointer-events-none transition-transform duration-200 group-focus-within:rotate-180 group-focus-within:text-[#D32030]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                    </div>
                </div>

                {{-- Switch Activo --}}
                <div class="flex items-center pt-5">
                    <label class="relative inline-flex items-center cursor-pointer group">
                        <input type="checkbox" name="activo" value="1" {{ $empleado->activo ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 dark:bg-neutral-800 rounded-full peer peer-focus:ring-2 peer-focus:ring-red-300 dark:peer-focus:ring-red-900/50 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#D32030] shadow-inner transition-colors duration-200"></div>
                        <span class="ml-3 text-sm font-semibold text-gray-700 dark:text-gray-200 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">Usuario Activo</span>
                    </label>
                </div>

            </div>
        </div>

        {{-- Acciones Finales --}}
        <div class="flex justify-end gap-3 transform transition-all duration-700 ease-out delay-200"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-5'">

            <a href="{{ route('empleados.index') }}" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-[#151515] border border-gray-300 dark:border-neutral-800 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-800 transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-0.5 active:scale-95">
                Cancelar
            </a>

            <button type="submit" class="group relative overflow-hidden inline-flex items-center justify-center px-6 py-2.5 text-sm font-semibold text-white bg-[#D32030] rounded-xl shadow-lg shadow-red-500/20 hover:bg-[#B91C2C] hover:shadow-xl hover:shadow-red-500/30 hover:-translate-y-0.5 active:scale-95 transition-all duration-200">
                <span class="absolute top-0 left-[-100%] w-1/2 h-full bg-gradient-to-r from-transparent via-white/20 to-transparent skew-x-[-20deg] group-hover:left-[200%] transition-all duration-700 ease-out"></span>
                Actualizar Empleado
            </button>
        </div>
    </form>
</div>
@endsection