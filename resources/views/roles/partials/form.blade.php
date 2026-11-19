@php
    $editando = isset($role) && $role !== null;
@endphp

<div class="max-w-2xl mx-auto space-y-6" x-data="{ enviando: false }">

    <a href="{{ route('roles.index') }}"
       class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Volver a roles
    </a>

    @if($errors->any())
        <div class="flex items-start gap-3 p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-xl text-sm font-medium">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>No se guardó. Revisa el campo marcado en rojo.</span>
        </div>
    @endif

    <form action="{{ $editando ? route('roles.update', $role->id) : route('roles.store') }}" method="POST"
          @submit="enviando = true"
          class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-6 sm:p-8">
        @csrf
        @if($editando)
            @method('PUT')
        @endif

        <div class="flex items-center gap-3 pb-4 mb-6 border-b border-gray-100 dark:border-white/10">
            <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </span>
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $editando ? 'Editar rol' : 'Nuevo rol' }}</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $editando ? 'Cambia el nombre del rol; sus empleados lo conservan.' : 'Crea un rol para asignarlo a los empleados.' }}
                </p>
            </div>
        </div>

        <label for="nombre" class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Nombre del rol <span class="text-red-500">*</span></label>
        <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $editando ? $role->nombre : '') }}" required maxlength="50"
               placeholder="Ej. Mecánico jefe, Vendedor, Contador..."
               class="w-full px-3 py-2.5 bg-gray-50 dark:bg-white/[0.03] border {{ $errors->has('nombre') ? 'border-red-400 dark:border-red-500/60' : 'border-gray-200 dark:border-white/10' }} text-gray-900 dark:text-white text-sm font-medium rounded-xl outline-none placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
        @error('nombre') <p class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

        <div class="mt-8 flex flex-col-reverse sm:flex-row justify-end gap-3">
            <a href="{{ route('roles.index') }}"
               class="text-center px-5 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                Cancelar
            </a>
            <button type="submit" :disabled="enviando"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors disabled:opacity-60">
                {{ $editando ? 'Guardar cambios' : 'Guardar rol' }}
            </button>
        </div>
    </form>
</div>
