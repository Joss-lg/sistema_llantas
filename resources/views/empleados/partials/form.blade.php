@php
    $editando     = $empleado !== null;
    $esPrincipal  = $esPrincipal ?? false;
    $esYo         = $esYo ?? false;
    $hayOld       = old('_form') !== null; // si volvió de un error, se respeta lo que la persona escribió

    $seleccion = $hayOld
        ? array_map('intval', (array) old('permisos', []))
        : ($editando ? $empleado->permisos->pluck('id')->map(fn ($id) => (int) $id)->all() : []);

    $activoMarcado = $hayOld ? old('activo') !== null : ($editando ? (bool) $empleado->activo : true);
    $rolActual     = old('rol_id', $editando ? $empleado->rol_id : '');
    $rolInicial    = $rolActual === '' ? '' : (int) $rolActual;
    $idsRolAdmin   = $roles->where('nombre', 'Administrador General')->pluck('id')->map(fn ($id) => (int) $id)->values();

    $campo = 'w-full px-3 py-2.5 bg-gray-50 dark:bg-white/[0.03] border text-gray-900 dark:text-white text-sm font-medium rounded-xl outline-none placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed';
    $borde = fn (string $nombre) => $errors->has($nombre) ? 'border-red-400 dark:border-red-500/60' : 'border-gray-200 dark:border-white/10';
@endphp

<div class="max-w-4xl mx-auto space-y-6"
     x-data="{
        permisosSeleccionados: @js($seleccion),
        rolId: @js($rolInicial),
        idsRolAdmin: @js($idsRolAdmin),
        get esAdmin() { return this.idsRolAdmin.includes(this.rolId); },
        enviando: false
     }">

    {{-- Volver --}}
    <a href="{{ route('empleados.index') }}"
       class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Volver a empleados
    </a>

    {{-- Aviso general cuando hay errores --}}
    @if($errors->any())
        <div class="flex items-start gap-3 p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-xl text-sm font-medium">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>No se guardó. Revisa los campos marcados en rojo.</span>
        </div>
    @endif

    <form action="{{ $editando ? route('empleados.update', $empleado->id) : route('empleados.store') }}" method="POST"
          class="space-y-6" @submit="enviando = true">
        @csrf
        @if($editando)
            @method('PUT')
        @endif
        <input type="hidden" name="_form" value="1">

        {{-- DATOS DEL EMPLEADO --}}
        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-6 sm:p-8">
            <div class="flex items-center gap-3 pb-4 mb-6 border-b border-gray-100 dark:border-white/10">
                <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </span>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Datos del empleado</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Nombre --}}
                <div>
                    <label for="name" class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Nombre completo <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $editando ? $empleado->name : '') }}" required maxlength="255"
                           placeholder="Ej. Laura Pérez" class="{{ $campo }} {{ $borde('name') }}">
                    @error('name') <p class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Correo --}}
                <div>
                    <label for="email" class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Correo electrónico <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email', $editando ? $empleado->email : '') }}" required maxlength="255"
                           placeholder="correo@ejemplo.com" @disabled($esPrincipal) class="{{ $campo }} {{ $borde('email') }}">
                    @error('email') <p class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Contraseña --}}
                <div>
                    <label for="password" class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                        Contraseña @unless($editando)<span class="text-red-500">*</span>@endunless
                    </label>
                    <input type="password" id="password" name="password" autocomplete="new-password" @required(! $editando)
                           placeholder="{{ $editando ? 'Dejar en blanco para conservar la actual' : 'Mínimo 8 caracteres' }}"
                           class="{{ $campo }} {{ $borde('password') }}">
                    @error('password') <p class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Sucursal --}}
                <div>
                    <label for="sucursal_id" class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Sucursal <span class="text-red-500">*</span></label>
                    <select id="sucursal_id" name="sucursal_id" required class="{{ $campo }} {{ $borde('sucursal_id') }}">
                        <option value="">Elige una sucursal</option>
                        @foreach($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}" @selected((string) old('sucursal_id', $editando ? $empleado->sucursal_id : '') === (string) $sucursal->id)>{{ $sucursal->nombre }}</option>
                        @endforeach
                    </select>
                    @error('sucursal_id') <p class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Rol --}}
                <div>
                    <label for="rol_id" class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Rol <span class="text-red-500">*</span></label>
                    <select id="rol_id" name="rol_id" required x-model.number="rolId" @disabled($esPrincipal) class="{{ $campo }} {{ $borde('rol_id') }}">
                        <option value="">Elige un rol</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}" @selected((string) $rolActual === (string) $rol->id)>{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                    @error('rol_id') <p class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Estado --}}
                <div>
                    <span class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Estado</span>
                    <label class="flex items-center gap-3 px-3 py-2.5 border border-gray-200 dark:border-white/10 rounded-xl {{ ($esPrincipal || $esYo) ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer' }}">
                        <input type="checkbox" name="activo" value="1" class="w-4 h-4 rounded accent-indigo-600"
                               @checked($activoMarcado || $esPrincipal || $esYo) @disabled($esPrincipal || $esYo)>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Cuenta activa (puede entrar al sistema)</span>
                    </label>
                </div>
            </div>

            {{-- Explicaciones de lo que está bloqueado --}}
            @if($esPrincipal)
                <p class="mt-5 text-xs text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl px-4 py-3">
                    El Administrador Principal no puede cambiar su correo ni su rol, y siempre queda activo. Su nombre, contraseña y sucursal sí se pueden cambiar.
                </p>
            @elseif($esYo)
                <p class="mt-5 text-xs text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl px-4 py-3">
                    No puedes deshabilitar tu propia cuenta.
                </p>
            @endif
        </div>

        {{-- PERMISOS --}}
        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-6 sm:p-8">
            <div class="flex items-center gap-3 pb-4 mb-6 border-b border-gray-100 dark:border-white/10">
                <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </span>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Permisos de acceso</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Marca lo que esta persona puede ver y hacer en el sistema.</p>
                </div>
            </div>

            {{-- El Administrador General tiene acceso total --}}
            <p x-show="esAdmin" x-cloak
               class="text-sm text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 rounded-xl px-4 py-3">
                El rol Administrador General tiene acceso total al sistema y no necesita permisos individuales.
            </p>

            <div x-show="! esAdmin" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($permisosGrouped as $modulo => $permisos)
                    <div x-data="{
                            moduloIds: @js($permisos->pluck('id')->map(fn ($id) => (int) $id)->values()),
                            get todos() { return this.moduloIds.length > 0 && this.moduloIds.every(id => permisosSeleccionados.includes(id)); },
                            alternarTodos() {
                                if (this.todos) {
                                    permisosSeleccionados = permisosSeleccionados.filter(id => !this.moduloIds.includes(id));
                                } else {
                                    permisosSeleccionados = [...permisosSeleccionados, ...this.moduloIds.filter(id => !permisosSeleccionados.includes(id))];
                                }
                            }
                         }"
                         class="border border-gray-200 dark:border-white/10 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100 dark:border-white/10">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $modulo }}</h3>
                            <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 cursor-pointer select-none">
                                Todos
                                <input type="checkbox" @change="alternarTodos()" :checked="todos" class="w-4 h-4 rounded accent-indigo-600">
                            </label>
                        </div>
                        <div class="space-y-2">
                            @foreach($permisos as $permiso)
                                <label class="flex items-start gap-2.5 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input type="checkbox" name="permisos[]" value="{{ $permiso->id }}"
                                           x-model.number="permisosSeleccionados"
                                           class="w-4 h-4 mt-0.5 rounded accent-indigo-600 shrink-0">
                                    <span class="leading-tight">{{ $permiso->nombre }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @error('permisos.*') <p class="mt-3 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        {{-- BOTONES --}}
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3">
            <a href="{{ route('empleados.index') }}"
               class="text-center px-5 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                Cancelar
            </a>
            <button type="submit" :disabled="enviando"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors disabled:opacity-60">
                {{ $editando ? 'Guardar cambios' : 'Registrar empleado' }}
            </button>
        </div>
    </form>
</div>
