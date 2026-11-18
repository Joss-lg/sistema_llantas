@extends('layouts.app')

@section('header_title', 'Clientes')

@section('content')

<style>[x-cloak] { display: none !important; }</style>

<div class="max-w-7xl mx-auto space-y-6"
     x-data="{
        modalNuevoCliente: @js((bool) session('abrirModalNuevo')),
        modalEditarCliente: @js((bool) session('clienteEditar')),
        clienteEditando: @js(session('clienteEditar')),
        modalEliminarCliente: false,
        urlEliminar: '',
        nombreEliminar: ''
     }"
     @keydown.escape.window="modalNuevoCliente = false; modalEditarCliente = false; modalEliminarCliente = false">

    {{-- Aviso de éxito --}}
    @if(session('success'))
        <div class="flex items-start gap-3 p-4 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-xl text-sm font-medium">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Aviso de error (por ejemplo, un cliente con compras que no se puede eliminar) --}}
    @if(session('error'))
        <div class="flex items-start gap-3 p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-xl text-sm font-medium">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Errores al guardar un cliente (los formularios están en ventanas que se cierran al recargar) --}}
    @if($errors->any())
        <div class="p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-xl text-sm">
            <p class="font-bold mb-1">No se pudo guardar el cliente:</p>
            <ul class="list-disc list-inside space-y-0.5 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">Consulta y administra a tus clientes registrados y su historial de compras.</p>
        <button type="button" @click="modalNuevoCliente = true"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo Cliente
        </button>
    </div>

    {{-- TARJETAS: los números son de TODO el filtro, no solo de la página --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Clientes</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($totalClientes) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">registrados</p>
        </div>

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Con compras</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($clientesConCompras) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">clientes que ya compraron</p>
        </div>

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Ticket promedio</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 tabular-nums">${{ number_format($ticketPromedio, 2) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">por compra de clientes registrados</p>
        </div>

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Clientes VIP</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($totalVip) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">preferenciales</p>
        </div>
    </div>
    <p class="-mt-3 text-xs text-gray-400 dark:text-gray-500">Las tarjetas cuentan a todos los clientes del filtro, no solo a los de esta página.</p>

    {{-- BUSCADOR --}}
    <form method="GET" action="{{ route('clientes.index') }}"
          class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5 sm:p-6">
        <label for="filter_q" class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Buscar cliente</label>
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                <input type="text" id="filter_q" name="q" value="{{ $busqueda }}" placeholder="Nombre, teléfono, email, RFC o razón social..."
                       class="w-full pl-9 pr-3 py-2.5 bg-gray-50 dark:bg-white/[0.03] border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white text-sm font-medium rounded-xl outline-none placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
            </div>
            <div class="flex gap-3">
                @if($busqueda !== '')
                    <a href="{{ route('clientes.index') }}"
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

    {{-- TABLA DE CLIENTES --}}
    <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/[0.03] text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-white/10">
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Cliente</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Contacto</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap">RFC</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Última compra</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap text-right">Total comprado</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700 dark:text-gray-300 divide-y divide-gray-100 dark:divide-white/10">
                    @forelse($clientes as $c)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors">

                            {{-- Cliente --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-sm font-bold {{ $c->is_vip ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' }}">
                                        {{ mb_strtoupper(mb_substr($c->nombre, 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-white">{{ $c->nombre }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            #{{ str_pad($c->id, 5, '0', STR_PAD_LEFT) }}
                                            @if($c->is_vip)
                                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50">VIP</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Contacto --}}
                            <td class="px-5 py-4">
                                <p class="text-gray-800 dark:text-gray-200">{{ $c->telefono ?: '—' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $c->email ?: '—' }}</p>
                            </td>

                            {{-- RFC --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($c->rfc)
                                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $c->rfc }}</span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>

                            {{-- Última compra --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($c->ultima_compra)
                                    {{ \Carbon\Carbon::parse($c->ultima_compra)->format('d/m/Y') }}
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>

                            {{-- Total comprado --}}
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <span class="font-extrabold text-gray-900 dark:text-white tabular-nums">${{ number_format($c->compras_sum ?? 0, 2) }}</span>
                            </td>

                            {{-- Acciones --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('clientes.show', $c->id) }}"
                                       class="inline-flex items-center justify-center w-9 h-9 rounded-full text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-colors" title="Ver detalle">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <button type="button" @click="clienteEditando = @js($c); modalEditarCliente = true"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-full text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition-colors" title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button type="button"
                                            @click="urlEliminar = @js(route('clientes.destroy', $c->id)); nombreEliminar = @js($c->nombre); modalEliminarCliente = true"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-full text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors" title="Eliminar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 mb-4">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                @if($busqueda !== '')
                                    <p class="text-base font-bold text-gray-800 dark:text-gray-200">Sin resultados</p>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 max-w-sm mx-auto">Ningún cliente coincide con tu búsqueda. Prueba con otro nombre, teléfono o RFC.</p>
                                @else
                                    <p class="text-base font-bold text-gray-800 dark:text-gray-200">Sin clientes</p>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 max-w-sm mx-auto">Aún no hay clientes registrados.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if($clientes->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-white/10">
                {{ $clientes->links() }}
            </div>
        @endif
    </div>

    {{-- Ventanas para crear y editar --}}
    @include('clientes.partials.create')
    @include('clientes.partials.edit')

    {{-- VENTANA: eliminar cliente --}}
    <div x-show="modalEliminarCliente" x-cloak
         class="fixed inset-0 z-[150] bg-gray-900/50 flex items-center justify-center p-4">
        <div @click.away="modalEliminarCliente = false"
             class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-start gap-4">
                <span class="w-11 h-11 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">¿Eliminar cliente?</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Vas a eliminar a <span class="font-bold text-gray-900 dark:text-white" x-text="nombreEliminar"></span>. Esta acción no se puede deshacer.
                    </p>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Si el cliente ya tiene compras no se puede eliminar, para conservar el historial de ventas.</p>
                </div>
            </div>
            <form :action="urlEliminar" method="POST" class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="modalEliminarCliente = false"
                        class="px-5 py-2.5 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 font-semibold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    Cancelar
                </button>
                <button type="submit"
                        class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold text-sm rounded-xl transition-colors">
                    Sí, eliminar
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
