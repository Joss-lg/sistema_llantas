@extends('layouts.app')

@section('content')
{{-- Estilos para ocultar CUALQUIER barra de desplazamiento y Animaciones Custom --}}
<style>
    ::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }
    * { scrollbar-width: none !important; -ms-overflow-style: none !important; }

    /* Animación de Pop (Rebote) para los iconos de los checkboxes */
    @keyframes popIn {
        0% { transform: scale(0.5); opacity: 0; }
        70% { transform: scale(1.2); opacity: 1; }
        100% { transform: scale(1); opacity: 1; }
    }
    .animate-pop-in { animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
</style>

<div class="max-w-5xl mx-auto space-y-6 transition-colors duration-300 relative px-4 py-8 min-h-[80vh] overflow-hidden" 
     x-data="{ 
        cargado: false,
        permisosSeleccionados: {{ json_encode(array_map('intval', old('permisos', $empleado->permisos->pluck('id')->toArray() ?? []))) }}
     }" 
     x-init="setTimeout(() => cargado = true, 50)">

    {{-- Fondo Decorativo --}}
    <div class="absolute inset-0 pointer-events-none z-0 transition-opacity duration-1000 delay-300" 
         :class="cargado ? 'opacity-100' : 'opacity-0'" aria-hidden="true">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px] dark:opacity-0"></div>
        <div class="hidden dark:block opacity-10">
            <div class="absolute -top-[100px] -left-[100px] w-[300px] h-[300px] rounded-full bg-[#818CF8] blur-[80px] animate-[pulse_10s_ease-in-out_infinite]"></div>
            <div class="absolute bottom-[40px] -right-[100px] w-[300px] h-[300px] rounded-full bg-[#818CF8] blur-[80px] animate-[pulse_14s_ease-in-out_infinite]" style="animation-delay: -5s;"></div>
        </div>
    </div>

    {{-- Encabezado --}}
    <div class="relative z-10 transform transition-all duration-700 ease-out"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'">
        <a href="{{ route('empleados.index') }}" class="group inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 dark:text-neutral-400 hover:text-[#818CF8] dark:hover:text-[#818CF8] transition-colors bg-white/50 dark:bg-[#121212]/50 px-3 py-1.5 rounded-lg backdrop-blur-sm border border-gray-200 dark:border-neutral-800">
            <svg class="w-4 h-4 transition-transform duration-200 group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Volver a Empleados
        </a>
        <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white mt-4 transition-colors tracking-tight">Editar Empleado: <span class="text-[#818CF8]">{{ $empleado->name }}</span></h1>
        <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1 font-medium">Actualiza los datos personales, accesos y permisos del usuario.</p>
    </div>

    <form action="{{ route('empleados.update', $empleado->id) }}" method="POST" class="space-y-6 relative z-10">
        @csrf
        @method('PUT')

        {{-- Tarjeta 1: Datos Personales y Acceso --}}
        <div class="bg-white/90 dark:bg-[#0c0c0c]/90 backdrop-blur-xl rounded-2xl border border-gray-200/80 dark:border-neutral-800/80 p-6 sm:p-8 shadow-lg dark:shadow-2xl transform transition-all duration-1000 ease-[cubic-bezier(0.16,1,0.3,1)] delay-100 group/card hover:shadow-xl dark:hover:shadow-[#818CF8]/5 hover:border-gray-300 dark:hover:border-neutral-700"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">

            <h2 class="font-bold text-lg text-gray-800 dark:text-gray-100 border-b border-gray-100 dark:border-neutral-800/60 pb-4 mb-6 transition-colors flex items-center gap-3">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-[#818CF8]/10 dark:bg-[#818CF8]/10 text-[#818CF8] shadow-inner">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </span>
                Información Básica
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Nombre Completo --}}
                <div class="space-y-2 group/input">
                    <label class="block text-xs font-bold text-gray-600 dark:text-neutral-400 uppercase tracking-wider transition-colors group-focus-within/input:text-[#818CF8]">Nombre Completo</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within/input:text-[#818CF8] transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                        </div>
                        <input type="text" name="name" value="{{ old('name', $empleado->name) }}" required 
                               class="w-full pl-11 pr-4 py-3 bg-gray-50/50 dark:bg-[#121212] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white rounded-xl text-sm outline-none transition-all duration-300 focus:bg-white dark:focus:bg-[#151515] focus:border-[#818CF8] focus:ring-4 focus:ring-[#818CF8]/20 focus:-translate-y-1 focus:shadow-lg focus:shadow-[#818CF8]/10 hover:border-gray-300 dark:hover:border-neutral-700">
                    </div>
                    @error('name') <span class="text-xs text-[#818CF8] font-medium animate-pulse">{{ $message }}</span> @enderror
                </div>

                {{-- Correo Electrónico --}}
                <div class="space-y-2 group/input">
                    <label class="block text-xs font-bold text-gray-600 dark:text-neutral-400 uppercase tracking-wider transition-colors group-focus-within/input:text-[#818CF8]">Correo Electrónico</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within/input:text-[#818CF8] transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <input type="email" name="email" value="{{ old('email', $empleado->email) }}" required 
                               class="w-full pl-11 pr-4 py-3 bg-gray-50/50 dark:bg-[#121212] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white rounded-xl text-sm outline-none transition-all duration-300 focus:bg-white dark:focus:bg-[#151515] focus:border-[#818CF8] focus:ring-4 focus:ring-[#818CF8]/20 focus:-translate-y-1 focus:shadow-lg focus:shadow-[#818CF8]/10 hover:border-gray-300 dark:hover:border-neutral-700">
                    </div>
                    @error('email') <span class="text-xs text-[#818CF8] font-medium animate-pulse">{{ $message }}</span> @enderror
                </div>

                {{-- Contraseña (Edición) --}}
                <div class="space-y-2 group/input">
                    <label class="block text-xs font-bold text-gray-600 dark:text-neutral-400 uppercase tracking-wider transition-colors group-focus-within/input:text-[#818CF8]">
                        Nueva Contraseña <span class="text-gray-400 dark:text-neutral-500 font-normal normal-case tracking-normal ml-1">(opcional)</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within/input:text-[#818CF8] transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input type="password" name="password" placeholder="Dejar en blanco para mantener actual"
                               class="w-full pl-11 pr-4 py-3 bg-gray-50/50 dark:bg-[#121212] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white rounded-xl text-sm outline-none transition-all duration-300 focus:bg-white dark:focus:bg-[#151515] focus:border-[#818CF8] focus:ring-4 focus:ring-[#818CF8]/20 focus:-translate-y-1 focus:shadow-lg focus:shadow-[#818CF8]/10 hover:border-gray-300 dark:hover:border-neutral-700">
                    </div>
                    @error('password') <span class="text-xs text-[#818CF8] font-medium animate-pulse">{{ $message }}</span> @enderror
                </div>

                {{-- Sucursal Asignada --}}
                <div class="space-y-2 group/input">
                    <label class="block text-xs font-bold text-gray-600 dark:text-neutral-400 uppercase tracking-wider transition-colors group-focus-within/input:text-[#818CF8]">Sucursal Asignada</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within/input:text-[#818CF8] transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <select name="sucursal_id" required 
                                class="w-full pl-11 pr-10 py-3 bg-gray-50/50 dark:bg-[#121212] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white rounded-xl text-sm outline-none transition-all duration-300 appearance-none cursor-pointer focus:bg-white dark:focus:bg-[#151515] focus:border-[#818CF8] focus:ring-4 focus:ring-[#818CF8]/20 focus:-translate-y-1 focus:shadow-lg focus:shadow-[#818CF8]/10 hover:border-gray-300 dark:hover:border-neutral-700">
                            @foreach($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}" {{ old('sucursal_id', $empleado->sucursal_id) == $sucursal->id ? 'selected' : '' }} class="bg-white dark:bg-[#121212] text-gray-900 dark:text-white font-medium py-2">
                                    {{ $sucursal->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none transition-transform duration-300 group-focus-within/input:rotate-180 group-focus-within/input:text-[#818CF8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                {{-- Rol --}}
                <div class="space-y-2 group/input">
                    <label class="block text-xs font-bold text-gray-600 dark:text-neutral-400 uppercase tracking-wider transition-colors group-focus-within/input:text-[#818CF8]">Rol del Sistema</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within/input:text-[#818CF8] transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <select name="rol_id" required 
                                class="w-full pl-11 pr-10 py-3 bg-gray-50/50 dark:bg-[#121212] border border-gray-200 dark:border-neutral-800 text-gray-900 dark:text-white rounded-xl text-sm outline-none transition-all duration-300 appearance-none cursor-pointer focus:bg-white dark:focus:bg-[#151515] focus:border-[#818CF8] focus:ring-4 focus:ring-[#818CF8]/20 focus:-translate-y-1 focus:shadow-lg focus:shadow-[#818CF8]/10 hover:border-gray-300 dark:hover:border-neutral-700">
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id }}" {{ old('rol_id', $empleado->rol_id) == $rol->id ? 'selected' : '' }} class="bg-white dark:bg-[#121212] text-gray-900 dark:text-white font-medium py-2">
                                    {{ $rol->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none transition-transform duration-300 group-focus-within/input:rotate-180 group-focus-within/input:text-[#818CF8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                {{-- Switch Activo --}}
                <div class="flex items-center pt-6">
                    <label class="relative inline-flex items-center cursor-pointer group hover:scale-105 transition-transform duration-300">
                        <input type="checkbox" name="activo" value="1" {{ $empleado->activo ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-12 h-6 bg-gray-300 dark:bg-neutral-800 rounded-full peer peer-focus:ring-4 peer-focus:ring-[#818CF8]/50 dark:peer-focus:ring-[#818CF8]/40 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#818CF8] peer-checked:shadow-[0_0_12px_rgba(129,140,248,0.6)] shadow-inner transition-all duration-300"></div>
                        <span class="ml-3 text-sm font-bold text-gray-700 dark:text-gray-200 group-hover:text-[#818CF8] dark:group-hover:text-white transition-colors">Usuario Activo en el Sistema</span>
                    </label>
                </div>

            </div>
        </div>

        {{-- Tarjeta 2: Matriz de Permisos --}}
        <div class="bg-white/90 dark:bg-[#0c0c0c]/90 backdrop-blur-xl rounded-2xl border border-gray-200/80 dark:border-neutral-800/80 p-6 sm:p-8 shadow-lg dark:shadow-2xl transform transition-all duration-1000 ease-[cubic-bezier(0.16,1,0.3,1)] delay-300 group/card hover:shadow-xl dark:hover:shadow-[#818CF8]/5 hover:border-gray-300 dark:hover:border-neutral-700"
             :class="cargado ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 translate-y-12 scale-95'">
            
            <h2 class="font-bold text-lg text-gray-800 dark:text-gray-100 border-b border-gray-100 dark:border-neutral-800/60 pb-4 mb-6 transition-colors flex items-center gap-3">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 dark:bg-neutral-800 text-gray-600 dark:text-gray-300 shadow-inner">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </span>
                Permisos Específicos por Módulo
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @if(isset($permisosGrouped))
                    @foreach($permisosGrouped as $modulo => $permisos)
                        @php $delayModulo = $loop->index * 100; @endphp
                        
                        {{-- Mini-tarjeta con efecto ZOOM, Flotante y Alpine JS --}}
                        <div class="bg-gray-50/80 dark:bg-[#121212] border border-gray-200 dark:border-neutral-800 rounded-xl p-5 transition-all duration-300 hover:scale-[1.03] hover:-translate-y-1 hover:border-[#818CF8]/50 dark:hover:border-[#818CF8]/50 hover:shadow-xl dark:hover:shadow-black/60 hover:bg-white dark:hover:bg-[#151515] transform ease-out group/modulo"
                             style="transition-delay: {{ $delayModulo }}ms"
                             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
                             x-data="{
                                moduloIds: {{ $permisos->pluck('id')->toJson() }},
                                get todosSeleccionados() {
                                    return this.moduloIds.length > 0 && this.moduloIds.every(id => permisosSeleccionados.includes(id));
                                },
                                toggleTodos() {
                                    if (this.todosSeleccionados) {
                                        permisosSeleccionados = permisosSeleccionados.filter(id => !this.moduloIds.includes(id));
                                    } else {
                                        let nuevos = this.moduloIds.filter(id => !permisosSeleccionados.includes(id));
                                        permisosSeleccionados = [...permisosSeleccionados, ...nuevos];
                                    }
                                }
                             }">
                            
                            {{-- Encabezado del Módulo y Switch "Todos" --}}
                            <div class="flex items-center justify-between mb-4 border-b border-gray-200 dark:border-neutral-800/60 pb-2">
                                <h3 class="text-[11px] font-black uppercase tracking-[0.15em] text-gray-500 dark:text-neutral-500 transition-colors group-hover/modulo:text-[#818CF8] group-hover/modulo:border-[#818CF8]/30">
                                    {{ $modulo }}
                                </h3>
                                
                                <label class="flex items-center gap-1.5 cursor-pointer group/todos">
                                    <span class="text-[9px] font-bold uppercase tracking-wider text-gray-400 dark:text-neutral-500 group-hover/todos:text-[#818CF8] transition-colors select-none">Todos</span>
                                    <div class="relative flex items-center">
                                        <input type="checkbox"
                                               @change="toggleTodos()"
                                               :checked="todosSeleccionados"
                                               class="peer appearance-none w-3.5 h-3.5 border-2 border-gray-300 dark:border-neutral-600 rounded-sm bg-white dark:bg-[#1a1a1a] checked:bg-[#818CF8] checked:border-[#818CF8] focus:outline-none transition-all cursor-pointer group-hover/todos:border-[#818CF8]/50">
                                        <svg class="absolute inset-0 w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="space-y-3.5">
                                @foreach($permisos as $permiso)
                                    <label class="flex items-start gap-3 cursor-pointer group/check">
                                        <div class="relative flex items-center mt-0.5">
                                            <input type="checkbox" name="permisos[]" value="{{ $permiso->id }}" 
                                                   x-model.number="permisosSeleccionados"
                                                   class="peer appearance-none w-4 h-4 border-2 border-gray-300 dark:border-neutral-600 rounded bg-white dark:bg-[#1a1a1a] checked:bg-[#818CF8] checked:border-[#818CF8] checked:shadow-[0_0_10px_rgba(129,140,248,0.5)] focus:outline-none focus:ring-4 focus:ring-[#818CF8]/30 transition-all duration-200 cursor-pointer group-hover/check:border-[#818CF8]/50">
                                            <svg class="absolute inset-0 w-4 h-4 text-white opacity-0 peer-checked:opacity-100 peer-checked:animate-pop-in pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-600 dark:text-neutral-400 group-hover/check:text-gray-900 dark:group-hover/check:text-white transition-colors leading-tight select-none">
                                            {{ $permiso->nombre }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-span-full py-8 text-center text-gray-500 dark:text-neutral-400 text-sm">
                        <p>Los permisos no están disponibles. Asegúrate de enviar la variable <code class="bg-gray-100 dark:bg-neutral-800 px-1 py-0.5 rounded text-[#818CF8]">$permisosGrouped</code> desde tu controlador.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Acciones Finales --}}
        <div class="flex justify-end gap-3 transform transition-all duration-700 ease-out delay-200"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-5'">

            <a href="{{ route('empleados.index') }}" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-[#151515] border border-gray-300 dark:border-neutral-800 rounded-xl hover:bg-gray-50 dark:hover:bg-neutral-800 transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-0.5 active:scale-95">
                Cancelar
            </a>

            <button type="submit" class="group relative overflow-hidden inline-flex items-center justify-center px-6 py-2.5 text-sm font-semibold text-white bg-[#818CF8] rounded-xl shadow-lg shadow-[#818CF8]/20 hover:bg-[#6366F1] hover:shadow-xl hover:shadow-[#818CF8]/30 hover:-translate-y-0.5 active:scale-95 transition-all duration-200">
                <span class="absolute top-0 left-[-100%] w-1/2 h-full bg-gradient-to-r from-transparent via-white/20 to-transparent skew-x-[-20deg] group-hover:left-[200%] transition-all duration-700 ease-out"></span>
                Actualizar Empleado
            </button>
        </div>
    </form>
</div>
@endsection