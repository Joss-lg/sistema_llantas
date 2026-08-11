@extends('layouts.app')

@section('header_title', 'Clientes')

@section('content')

{{-- ============================================
    Keyframes, animaciones premium y ocultamiento de scroll
============================================= --}}
<style>
    @keyframes fadeUpIn {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulseVipSoft {
        0%, 100% { box-shadow: 0 0 0 0 rgba(211,32,48,0.2); }
        50%      { box-shadow: 0 0 0 6px rgba(211,32,48,0); }
    }
    .row-anim { animation: fadeUpIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) both; opacity: 0; }
    .vip-badge { animation: pulseVipSoft 2.5s cubic-bezier(0.4, 0, 0.6, 1) infinite; }

    /* ELIMINACIÓN GLOBAL DE BARRAS DE SCROLL (Adiós a la raya horrible) */
    *::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
        background: transparent !important;
    }
    * {
        scrollbar-width: none !important; 
        -ms-overflow-style: none !important; 
    }

    @media (prefers-reduced-motion: reduce) {
        .row-anim, .vip-badge { animation: none !important; opacity: 1 !important; transform: none !important; }
    }
</style>

<!-- Contenedor principal: Mayor espaciado y fondo fluido -->
<div class="max-w-7xl mx-auto space-y-8 py-6 transition-colors duration-500"
     x-data="{
        cargado: false,
        modalNuevoCliente: {{ session('abrirModalNuevo') ? 'true' : 'false' }},
        modalEditarCliente: {{ session('clienteEditar') ? 'true' : 'false' }},
        clienteEditando: @js(session('clienteEditar'))
     }"
     x-init="setTimeout(() => cargado = true, 50)">

    {{-- Alertas de Sesión - Estilo flotante premium --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50/80 dark:bg-emerald-900/10 backdrop-blur-md ring-1 ring-emerald-500/20 rounded-2xl text-emerald-700 dark:text-emerald-400 text-sm font-medium flex items-center justify-between shadow-sm row-anim">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="p-1 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition-colors opacity-70 hover:opacity-100">&times;</button>
        </div>
    @endif

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 transform transition-all duration-1000 ease-out"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'">
        <div class="space-y-1">
            <h2 class="text-3xl sm:text-4xl font-light tracking-tight text-zinc-900 dark:text-white transition-colors">
                Base de Datos de <span class="font-semibold">Clientes</span>
            </h2>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 transition-colors">Gestiona y consulta el historial de tus clientes registrados con facilidad.</p>
        </div>
        
        <button type="button"
                @click="modalNuevoCliente = true"
                class="group relative inline-flex items-center justify-center gap-2 px-6 py-3 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 rounded-2xl text-sm font-medium transition-all duration-300 hover:scale-[1.02] active:scale-95 shadow-[0_8px_20px_rgb(0,0,0,0.12)] dark:shadow-[0_8px_20px_rgba(255,255,255,0.1)] hover:shadow-[0_12px_25px_rgb(0,0,0,0.2)] overflow-hidden cursor-pointer">
            <!-- Brillo sutil en hover -->
            <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
            
            <svg class="w-4 h-4 transition-transform duration-500 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Nuevo cliente</span>
        </button>
    </div>

    {{-- Métricas Animadas en Cascada (Diseño de Tarjetas Glass/Soft) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1 -->
        <div class="bg-white dark:bg-zinc-900/80 backdrop-blur-xl rounded-3xl p-7 ring-1 ring-zinc-900/5 dark:ring-white/5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all duration-500 hover:-translate-y-1.5 hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)] dark:hover:shadow-[0_20px_40px_rgba(0,0,0,0.4)] delay-75 transform group relative overflow-hidden"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'">
            <div class="absolute top-0 right-0 p-6 opacity-0 group-hover:opacity-10 transition-opacity duration-500">
                <svg class="w-16 h-16 text-zinc-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <p class="text-[10px] text-zinc-400 dark:text-zinc-500 font-bold uppercase tracking-widest transition-colors relative z-10">Total Clientes</p>
            <p class="text-4xl font-light tracking-tight text-zinc-900 dark:text-white mt-3 transition-colors relative z-10">{{ number_format($clientes->total()) }}</p>
            <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium mt-4 flex items-center gap-1.5 relative z-10">
                <span class="bg-emerald-100 dark:bg-emerald-500/20 p-0.5 rounded-full"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg></span> 
                Registrados
            </p>
        </div>

        <!-- Card 2 -->
        <div class="bg-white dark:bg-zinc-900/80 backdrop-blur-xl rounded-3xl p-7 ring-1 ring-zinc-900/5 dark:ring-white/5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all duration-500 hover:-translate-y-1.5 hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)] dark:hover:shadow-[0_20px_40px_rgba(0,0,0,0.4)] delay-150 transform group relative overflow-hidden"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'">
            <p class="text-[10px] text-zinc-400 dark:text-zinc-500 font-bold uppercase tracking-widest transition-colors relative z-10">Ticket Promedio</p>
            <p class="text-4xl font-light tracking-tight text-zinc-900 dark:text-white mt-3 transition-colors relative z-10">$342.50</p>
            <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium mt-4 flex items-center gap-1.5 relative z-10">
                <span class="bg-emerald-100 dark:bg-emerald-500/20 p-0.5 rounded-full"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg></span> 
                Promedio
            </p>
        </div>

        <!-- Card 3 -->
        <div class="bg-white dark:bg-zinc-900/80 backdrop-blur-xl rounded-3xl p-7 ring-1 ring-zinc-900/5 dark:ring-white/5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all duration-500 hover:-translate-y-1.5 hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)] dark:hover:shadow-[0_20px_40px_rgba(0,0,0,0.4)] delay-200 transform group relative overflow-hidden"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'">
            <p class="text-[10px] text-zinc-400 dark:text-zinc-500 font-bold uppercase tracking-widest transition-colors relative z-10">Retención</p>
            <p class="text-4xl font-light tracking-tight text-zinc-900 dark:text-white mt-3 transition-colors relative z-10">78%</p>
            <p class="text-xs text-rose-500 dark:text-rose-400 font-medium mt-4 flex items-center gap-1.5 relative z-10">
                <span class="bg-rose-100 dark:bg-rose-500/20 p-0.5 rounded-full"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg></span> 
                Clientes Activos
            </p>
        </div>

        <!-- Card 4 (Destacada VIP) -->
        <div class="bg-gradient-to-br from-zinc-900 to-black dark:from-zinc-950 dark:to-[#0a0a0a] rounded-3xl p-7 ring-1 ring-white/10 shadow-[0_8px_30px_rgba(0,0,0,0.2)] transition-all duration-500 hover:-translate-y-1.5 hover:shadow-[0_20px_40px_rgba(211,32,48,0.15)] delay-300 transform group relative overflow-hidden"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'">
             <!-- Brillo rojo sutil de fondo -->
            <div class="absolute -right-10 -top-10 w-32 h-32 bg-[#D32030] rounded-full blur-[60px] opacity-20 group-hover:opacity-40 transition-opacity duration-700"></div>
            <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-widest relative z-10">Clientes VIP</p>
            <p class="text-4xl font-light tracking-tight text-white mt-3 relative z-10">{{ $clientes->where('is_vip', true)->count() }}</p>
            <p class="text-xs text-zinc-400 font-medium mt-4 relative z-10 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-[#D32030] shadow-[0_0_8px_#D32030]"></span> Preferenciales
            </p>
        </div>
    </div>

    {{-- Contenedor de la Tabla Principal --}}
    <div class="bg-white dark:bg-zinc-900/80 backdrop-blur-xl rounded-3xl ring-1 ring-zinc-900/5 dark:ring-white/5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden transition-all duration-1000 ease-out delay-500 transform"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-16'">

        {{-- Buscador Premium --}}
        <div class="p-6 border-b border-zinc-100 dark:border-zinc-800/60 transition-colors">
            <div class="relative group max-w-xl">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-zinc-400 group-focus-within:text-zinc-900 dark:group-focus-within:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" placeholder="Buscar por nombre, teléfono, email..."
                       class="w-full pl-12 pr-4 py-3 bg-zinc-50/50 dark:bg-zinc-950/50 border-0 ring-1 ring-zinc-200/50 dark:ring-white/10
                              rounded-2xl text-sm text-zinc-800 dark:text-zinc-100 placeholder-zinc-400
                              focus:outline-none focus:ring-2 focus:ring-zinc-900 dark:focus:ring-white focus:bg-white dark:focus:bg-zinc-900
                              transition-all duration-300 shadow-sm">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-zinc-50/50 dark:bg-zinc-800/20 text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest border-b border-zinc-100 dark:border-zinc-800/60 transition-colors">
                        <th class="px-8 py-5 font-semibold">Nombre</th>
                        <th class="px-6 py-5 font-semibold">Teléfono</th>
                        <th class="px-6 py-5 font-semibold">Email</th>
                        <th class="px-6 py-5 font-semibold">Última Compra</th>
                        <th class="px-6 py-5 font-semibold">Total</th>
                        <th class="px-8 py-5 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50 dark:divide-zinc-800/40">
                    @forelse($clientes as $c)
                        @php
                            $delayFila = $loop->index * 0.06;
                        @endphp
                        <tr class="group hover:bg-zinc-50/80 dark:hover:bg-zinc-800/30 transition-all duration-300 row-anim" style="animation-delay: {{ $delayFila + 0.6 }}s">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <!-- Avatar tipo Squircle (Premium) -->
                                    <div class="relative flex items-center justify-center w-11 h-11 rounded-[14px] {{ $c->is_vip ? 'bg-[#D32030] text-white shadow-lg shadow-red-500/20 vip-badge' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 ring-1 ring-inset ring-zinc-900/5 dark:ring-white/10' }} font-medium text-sm shrink-0 transition-transform duration-300 group-hover:scale-105 group-hover:rotate-3">
                                        {{ strtoupper(substr($c->nombre, 0, 1)) }}
                                        @if($c->is_vip)
                                            <span class="absolute -top-1 -right-1 w-3 h-3 bg-white dark:bg-zinc-900 rounded-full flex items-center justify-center">
                                                <span class="w-2 h-2 bg-yellow-400 rounded-full"></span>
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 transition-colors">
                                            {{ $c->nombre }}
                                        </p>
                                        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5 tracking-wide transition-colors">ID: #{{ str_pad($c->id, 5, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-sm text-zinc-600 dark:text-zinc-400 transition-colors">{{ $c->telefono ?? '—' }}</td>
                            <td class="px-6 py-5 text-sm text-zinc-600 dark:text-zinc-400 transition-colors">{{ $c->email ?? '—' }}</td>
                            <td class="px-6 py-5">
                                @if($c->ultima_compra)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 ring-1 ring-inset ring-zinc-900/5 dark:ring-white/5 transition-colors">
                                        {{ \Carbon\Carbon::parse($c->ultima_compra)->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-sm text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-5 text-sm font-semibold text-zinc-900 dark:text-white transition-colors">
                                ${{ number_format($c->compras_sum ?? 0, 2) }}
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300 focus-within:opacity-100">
                                    <a href="{{ route('clientes.show', $c->id) }}" 
                                       class="p-2 text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-xl transition-all duration-200" title="Ver Detalles">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <button type="button"
                                            @click="clienteEditando = @js($c); modalEditarCliente = true"
                                            class="p-2 text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-xl transition-all duration-200" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <form action="{{ route('clientes.destroy', $c->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Seguro que deseas eliminar este cliente?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-zinc-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-xl transition-all duration-200" title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                                <!-- Icono de puntos para móviles cuando los botones están ocultos -->
                                <div class="flex justify-end lg:hidden group-hover:hidden">
                                     <svg class="w-5 h-5 text-zinc-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-zinc-50 dark:bg-zinc-800/50 mb-4">
                                    <svg class="w-8 h-8 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <p class="text-zinc-500 dark:text-zinc-400 text-sm font-medium">No se encontraron clientes registrados en el sistema.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="px-8 py-4 border-t border-zinc-100 dark:border-zinc-800/60 transition-colors bg-white dark:bg-zinc-900/30 rounded-b-3xl">
            {{ $clientes->links() }}
        </div>
    </div>

    {{-- Modales --}}
    @include('clientes.partials.create')
    @include('clientes.partials.edit')
</div>
@endsection