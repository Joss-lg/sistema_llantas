{{-- resources/views/clientes/partials/modal-editar-cliente.blade.php --}}
<div x-show="modalEditarCliente"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
     style="display: none;">

    <div @click.away="modalEditarCliente = false"
         class="bg-white dark:bg-[#151515] rounded-2xl max-w-2xl w-full border border-gray-200 dark:border-neutral-800 shadow-2xl overflow-hidden transform transition-all">

        <div class="px-6 py-4 border-b border-gray-100 dark:border-neutral-800 flex justify-between items-center bg-gray-50/50 dark:bg-[#0A0A0A]">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Editar Cliente</h3>
            <button type="button" @click="modalEditarCliente = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white text-xl font-bold">&times;</button>
        </div>

        <template x-if="clienteEditando">
            <form :action="'/clientes/' + clienteEditando.id" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Nombre Completo *</label>
                        <input type="text" name="nombre" required x-model="clienteEditando.nombre"
                               class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#D32030] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Teléfono</label>
                        <input type="text" name="telefono" x-model="clienteEditando.telefono"
                               class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#D32030] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Correo Electrónico</label>
                        <input type="email" name="email" x-model="clienteEditando.email"
                               class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#D32030] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">RFC</label>
                        <input type="text" name="rfc" x-model="clienteEditando.rfc"
                               class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#D32030] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Razón Social</label>
                        <input type="text" name="razon_social" x-model="clienteEditando.razon_social"
                               class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#D32030] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Uso CFDI</label>
                        <input type="text" name="uso_cfdi" x-model="clienteEditando.uso_cfdi"
                               class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#D32030] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Código Postal</label>
                        <input type="text" name="cp" x-model="clienteEditando.cp"
                               class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#D32030] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Régimen Fiscal</label>
                        <input type="text" name="regimen_fiscal" x-model="clienteEditando.regimen_fiscal"
                               class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#D32030] focus:outline-none">
                    </div>

                    <div class="md:col-span-2 flex items-center gap-2 pt-2">
                        <input type="checkbox" name="is_vip" value="1" x-model="clienteEditando.is_vip"
                               class="w-4 h-4 rounded border-gray-300 text-[#D32030] focus:ring-[#D32030]">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Marcar como cliente VIP</label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-neutral-800">
                    <button type="button" @click="modalEditarCliente = false" class="px-5 py-2.5 text-sm font-semibold text-gray-600 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-800 rounded-xl">Cancelar</button>
                    <button type="submit" class="px-5 py-2.5 bg-[#D32030] hover:bg-[#B91C2C] text-white rounded-xl text-sm font-semibold shadow-lg shadow-red-500/20">Guardar Cambios</button>
                </div>
            </form>
        </template>
    </div>
</div>