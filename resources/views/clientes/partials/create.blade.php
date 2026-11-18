{{-- Ventana para registrar un cliente nuevo. Se abre desde clientes/index (variable modalNuevoCliente). --}}
@php
    $camposCliente = [
        ['nombre',         'Nombre completo *',  'text',  'sm:col-span-2'],
        ['telefono',       'Teléfono',           'text',  ''],
        ['email',          'Correo electrónico', 'email', ''],
        ['rfc',            'RFC',                'text',  ''],
        ['razon_social',   'Razón social',       'text',  ''],
        ['uso_cfdi',       'Uso CFDI',           'text',  ''],
        ['cp',             'Código postal',      'text',  ''],
        ['regimen_fiscal', 'Régimen fiscal',     'text',  'sm:col-span-2'],
    ];
@endphp

<div x-show="modalNuevoCliente" x-cloak
     class="fixed inset-0 !mt-0 z-50 overflow-y-auto bg-gray-900/50 flex items-start sm:items-center justify-center p-4">

    <div @click.away="modalNuevoCliente = false"
         class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-xl max-w-2xl w-full my-8">

        {{-- Encabezado --}}
        <div class="px-6 py-5 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Registrar nuevo cliente</h3>
            <button type="button" @click="modalNuevoCliente = false"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-full text-gray-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 transition-colors" title="Cerrar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('clientes.store') }}" method="POST" class="p-6 space-y-5" x-data="{ enviando: false }" @submit="enviando = true">
            @csrf
            <input type="hidden" name="_form" value="nuevo">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($camposCliente as [$campo, $etiqueta, $tipo, $ancho])
                    <div class="{{ $ancho }}">
                        <label for="nuevo_{{ $campo }}" class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">{{ $etiqueta }}</label>
                        <input type="{{ $tipo }}" id="nuevo_{{ $campo }}" name="{{ $campo }}" value="{{ old('_form') === 'nuevo' ? old($campo) : '' }}" {{ $campo === 'nombre' ? 'required' : '' }}
                               class="w-full px-3 py-2.5 bg-gray-50 dark:bg-white/[0.03] border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white text-sm font-medium rounded-xl outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                    </div>
                @endforeach

                <div class="sm:col-span-2">
                    <label for="nuevo_is_vip" class="inline-flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" id="nuevo_is_vip" name="is_vip" value="1" {{ old('_form') === 'nuevo' && old('is_vip') ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-gray-300 dark:border-white/20 text-indigo-600 focus:ring-indigo-500/30">
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-200">Marcar como cliente VIP</span>
                    </label>
                </div>
            </div>

            {{-- Errores de validación --}}
            @if($errors->any() && old('_form') === 'nuevo')
                <div class="p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-xl text-sm">
                    <ul class="list-disc list-inside space-y-0.5 font-medium">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Botones --}}
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4 border-t border-gray-100 dark:border-white/10">
                <button type="button" @click="modalNuevoCliente = false"
                        class="px-5 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    Cancelar
                </button>
                <button type="submit" :disabled="enviando"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white font-bold text-sm rounded-xl transition-colors">
                    Guardar cliente
                </button>
            </div>
        </form>
    </div>
</div>
