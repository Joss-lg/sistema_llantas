{{-- resources/views/clientes/partials/modal-nuevo-cliente.blade.php --}}
<div x-show="modalNuevoCliente"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 backdrop-blur-none"
     x-transition:enter-end="opacity-100 backdrop-blur-md"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 backdrop-blur-md"
     x-transition:leave-end="opacity-0 backdrop-blur-none"
     class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4 sm:p-6"
     style="display: none;">

    <div x-show="modalNuevoCliente"
         @click.away="modalNuevoCliente = false"
         x-transition:enter="transition ease-out duration-300 delay-75"
         x-transition:enter-start="opacity-0 translate-y-8 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="bg-white dark:bg-zinc-900 rounded-3xl max-w-2xl w-full ring-1 ring-slate-900/5 dark:ring-white/10 shadow-2xl overflow-hidden transform transition-all flex flex-col max-h-full">

        {{-- Encabezado del Modal --}}
        <div class="px-8 py-6 border-b border-slate-100 dark:border-zinc-800/60 flex justify-between items-center bg-white dark:bg-zinc-900 shrink-0">
            <h3 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                Registrar Nuevo <span class="text-[#818CF8]">Cliente</span>
            </h3>
            <button type="button" @click="modalNuevoCliente = false" class="p-2 text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-xl transition-all duration-200 focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Formulario con scroll interno si es necesario --}}
        <div class="overflow-y-auto custom-scrollbar p-8">
            <form action="{{ route('clientes.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                    
                    {{-- Input Nombre --}}
                    <div class="md:col-span-2 group">
                        <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors group-focus-within:text-[#818CF8]">Nombre Completo *</label>
                        <input type="text" name="nombre" required value="{{ old('nombre') }}"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-950/50 border-0 ring-1 ring-inset ring-slate-200 dark:ring-white/10 rounded-xl text-sm font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#818CF8] focus:bg-white dark:focus:bg-zinc-900 hover:bg-slate-100 dark:hover:bg-zinc-900 transition-all duration-300">
                    </div>

                    {{-- Input Teléfono --}}
                    <div class="group">
                        <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors group-focus-within:text-[#818CF8]">Teléfono</label>
                        <input type="text" name="telefono" value="{{ old('telefono') }}"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-950/50 border-0 ring-1 ring-inset ring-slate-200 dark:ring-white/10 rounded-xl text-sm font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#818CF8] focus:bg-white dark:focus:bg-zinc-900 hover:bg-slate-100 dark:hover:bg-zinc-900 transition-all duration-300">
                    </div>

                    {{-- Input Email --}}
                    <div class="group">
                        <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors group-focus-within:text-[#818CF8]">Correo Electrónico</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-950/50 border-0 ring-1 ring-inset ring-slate-200 dark:ring-white/10 rounded-xl text-sm font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#818CF8] focus:bg-white dark:focus:bg-zinc-900 hover:bg-slate-100 dark:hover:bg-zinc-900 transition-all duration-300">
                    </div>

                    {{-- Input RFC --}}
                    <div class="group">
                        <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors group-focus-within:text-[#818CF8]">RFC</label>
                        <input type="text" name="rfc" value="{{ old('rfc') }}"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-950/50 border-0 ring-1 ring-inset ring-slate-200 dark:ring-white/10 rounded-xl text-sm font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#818CF8] focus:bg-white dark:focus:bg-zinc-900 hover:bg-slate-100 dark:hover:bg-zinc-900 transition-all duration-300">
                    </div>

                    {{-- Input Razón Social --}}
                    <div class="group">
                        <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors group-focus-within:text-[#818CF8]">Razón Social</label>
                        <input type="text" name="razon_social" value="{{ old('razon_social') }}"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-950/50 border-0 ring-1 ring-inset ring-slate-200 dark:ring-white/10 rounded-xl text-sm font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#818CF8] focus:bg-white dark:focus:bg-zinc-900 hover:bg-slate-100 dark:hover:bg-zinc-900 transition-all duration-300">
                    </div>

                    {{-- Input Uso CFDI --}}
                    <div class="group">
                        <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors group-focus-within:text-[#818CF8]">Uso CFDI</label>
                        <input type="text" name="uso_cfdi" value="{{ old('uso_cfdi') }}"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-950/50 border-0 ring-1 ring-inset ring-slate-200 dark:ring-white/10 rounded-xl text-sm font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#818CF8] focus:bg-white dark:focus:bg-zinc-900 hover:bg-slate-100 dark:hover:bg-zinc-900 transition-all duration-300">
                    </div>

                    {{-- Input Código Postal --}}
                    <div class="group">
                        <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors group-focus-within:text-[#818CF8]">Código Postal</label>
                        <input type="text" name="cp" value="{{ old('cp') }}"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-950/50 border-0 ring-1 ring-inset ring-slate-200 dark:ring-white/10 rounded-xl text-sm font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#818CF8] focus:bg-white dark:focus:bg-zinc-900 hover:bg-slate-100 dark:hover:bg-zinc-900 transition-all duration-300">
                    </div>

                    {{-- Input Régimen Fiscal --}}
                    <div class="md:col-span-2 group">
                        <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors group-focus-within:text-[#818CF8]">Régimen Fiscal</label>
                        <input type="text" name="regimen_fiscal" value="{{ old('regimen_fiscal') }}"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-zinc-950/50 border-0 ring-1 ring-inset ring-slate-200 dark:ring-white/10 rounded-xl text-sm font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#818CF8] focus:bg-white dark:focus:bg-zinc-900 hover:bg-slate-100 dark:hover:bg-zinc-900 transition-all duration-300">
                    </div>

                    {{-- Checkbox VIP Premium --}}
                    <div class="md:col-span-2 pt-2">
                        <label for="is_vip_nuevo" class="flex items-center gap-3 cursor-pointer group w-fit">
                            <div class="relative flex items-center justify-center">
                                <input type="checkbox" name="is_vip" id="is_vip_nuevo" value="1" {{ old('is_vip') ? 'checked' : '' }}
                                       class="peer appearance-none w-5 h-5 rounded-md border border-slate-300 dark:border-zinc-700 checked:bg-[#818CF8] checked:border-[#818CF8] focus:outline-none focus:ring-2 focus:ring-[#818CF8]/30 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-900 transition-all cursor-pointer">
                                <svg class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-sm font-bold text-slate-600 dark:text-slate-300 group-hover:text-[#818CF8] dark:group-hover:text-[#818CF8] transition-colors">Marcar como cliente VIP</span>
                        </label>
                    </div>
                </div>

                {{-- Errores de Validación (Estilo Soft Error) --}}
                @if ($errors->any())
                    <div class="p-4 bg-rose-50/80 dark:bg-rose-900/10 backdrop-blur-md ring-1 ring-rose-500/20 rounded-xl text-rose-700 dark:text-rose-400 text-sm shadow-sm animate-[fadeUpIn_0.3s_ease-out]">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 shrink-0 mt-0.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <ul class="list-none space-y-1 mt-0.5">
                                @foreach ($errors->all() as $error)
                                    <li class="font-bold">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- Footer / Acciones --}}
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-6 mt-2 border-t border-slate-100 dark:border-zinc-800/60 shrink-0">
                    <button type="button" @click="modalNuevoCliente = false" class="px-6 py-2.5 text-sm font-bold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-xl transition-all duration-300">
                        Cancelar
                    </button>
                    <button type="submit" class="group relative inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-[#818CF8] hover:bg-[#6366F1] text-white rounded-xl text-sm font-bold transition-all duration-300 hover:-translate-y-0.5 shadow-[0_8px_20px_rgba(129,140,248,0.25)] hover:shadow-[0_12px_25px_rgba(129,140,248,0.35)] overflow-hidden">
                        <!-- Brillo en el botón -->
                        <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                        <span>Guardar Cliente</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>