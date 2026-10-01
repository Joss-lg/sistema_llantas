@extends('layouts.app')

@section('header_title', 'Clientes')

@section('content')

<style>
    @keyframes fadeUpIn {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulseVipSoft {
        0%, 100% { box-shadow: 0 0 0 0 rgba(129, 140, 248, 0.3); }
        50%       { box-shadow: 0 0 0 8px rgba(129, 140, 248, 0); }
    }
    @keyframes bell-shake {
        0%, 100% { transform: rotate(0); }
        15% { transform: rotate(20deg); }
        30% { transform: rotate(-20deg); }
        45% { transform: rotate(15deg); }
        60% { transform: rotate(-15deg); }
        75% { transform: rotate(8deg); }
        85% { transform: rotate(-8deg); }
    }
    .animate-bell { animation: bell-shake 0.8s cubic-bezier(.36,.07,.19,.97) 0.1s both; transform-origin: top center; }
    @keyframes shrink-progress { from { width: 100%; } to { width: 0%; } }
    .animate-progress { animation: shrink-progress 2.5s linear forwards; }
    .toast-container:hover .animate-progress { animation-play-state: paused; }

    /* Trituradora */
    .shredder-wrapper { position: relative; width: 120px; height: 140px; margin: 0 auto; }
    .s-doc { position: absolute; top: 5px; left: 50%; transform: translateX(-50%); width: 56px; height: 66px; background: #ffffff; border: 2px solid #cbd5e1; border-radius: 6px; padding: 10px 8px; display: flex; flex-direction: column; gap: 5px; z-index: 1; animation: float-doc 3s ease-in-out infinite; }
    @keyframes float-doc { 0%, 100% { transform: translate(-50%, 0); } 50% { transform: translate(-50%, -6px); } }
    .s-doc-line { height: 4px; background: #e2e8f0; border-radius: 2px; width: 100%; }
    .s-doc-line.short { width: 60%; }
    .s-doc-line.red { background: #f43f5e; width: 40%; opacity: 0.8; }
    .s-machine { position: absolute; top: 65px; left: 50%; transform: translateX(-50%); width: 90px; height: 26px; background: #0f172a; border-radius: 8px; z-index: 2; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); display: flex; justify-content: center; align-items: center; }
    .s-slot { width: 66px; height: 4px; background: #000; border-radius: 2px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.5); }
    .s-shreds { position: absolute; top: 90px; left: 50%; transform: translateX(-50%); width: 56px; height: 50px; z-index: 1; display: flex; justify-content: space-between; }
    .s-shred { width: 9px; height: 0%; background: #ffffff; border: 1px solid #cbd5e1; border-top: none; transform-origin: top; border-radius: 0 0 2px 2px; }
    .is-active .s-doc { animation: doc-down 1.2s cubic-bezier(0.4,0,1,1) forwards !important; }
    .is-active .s-shred { animation: shred-fall 1.2s cubic-bezier(0.4,0,1,1) forwards; }
    .is-active .s-shred:nth-child(1) { animation-delay: 0.05s; }
    .is-active .s-shred:nth-child(2) { animation-delay: 0.0s; }
    .is-active .s-shred:nth-child(3) { animation-delay: 0.1s; }
    .is-active .s-shred:nth-child(4) { animation-delay: 0.02s; }
    .is-active .s-shred:nth-child(5) { animation-delay: 0.08s; }
    @keyframes doc-down { 0% { transform: translate(-50%,0); opacity:1; } 70% { transform: translate(-50%,65px); opacity:1; } 100% { transform: translate(-50%,65px); opacity:0; } }
    @keyframes shred-fall { 0% { height:0%; transform:translateY(0); opacity:1; } 50% { height:100%; transform:translateY(0); opacity:1; } 100% { height:100%; transform:translateY(30px); opacity:0; } }

    .row-anim { animation: fadeUpIn 0.5s cubic-bezier(0.16,1,0.3,1) both; }
    .vip-badge { animation: pulseVipSoft 2.5s cubic-bezier(0.4,0,0.6,1) infinite; }
    ::-webkit-scrollbar { display: none !important; }
    html, body, * { -ms-overflow-style: none !important; scrollbar-width: none !important; }
    @media (prefers-reduced-motion: reduce) {
        .row-anim, .vip-badge, .animate-bell, .animate-progress, .s-doc { animation: none !important; opacity: 1 !important; transform: none !important; }
    }
    @keyframes barGrow { from { width: 0; } to { width: var(--target-w); } }
    .bar-grow { animation: barGrow 1.2s cubic-bezier(0.16,1,0.3,1) forwards; animation-delay: 0.5s; }
</style>

<div class="max-w-7xl mx-auto space-y-8 py-6"
     x-data="{
        cargado: false,
        modalNuevoCliente: {{ session('abrirModalNuevo') ? 'true' : 'false' }},
        modalEditarCliente: {{ session('clienteEditar') ? 'true' : 'false' }},
        clienteEditando: @js(session('clienteEditar')),
        modalEliminarCliente: false,
        urlEliminar: ''
     }"
     x-init="setTimeout(() => cargado = true, 50)">

    {{-- Toast --}}
    @if(session('success'))
        <div x-data="{ show: false, timer: null }"
             x-init="setTimeout(() => show = true, 50); timer = setTimeout(() => show = false, 2500);"
             @mouseenter="clearTimeout(timer)"
             @mouseleave="timer = setTimeout(() => show = false, 1500)"
             x-show="show"
             x-transition:enter="transition-all ease-out duration-500"
             x-transition:enter-start="opacity-0 translate-x-full scale-95"
             x-transition:enter-end="opacity-100 translate-x-0 scale-100"
             x-transition:leave="transition-all ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-x-0 scale-100"
             x-transition:leave-end="opacity-0 translate-x-full scale-95"
             class="toast-container fixed top-6 right-6 sm:top-8 sm:right-8 z-[100] w-full max-w-[380px] bg-white dark:bg-zinc-900 rounded-[20px] shadow-[0_8px_30px_rgb(0,0,0,0.12)] ring-1 ring-slate-200/50 dark:ring-white/10 overflow-hidden flex flex-col cursor-default"
             style="display: none;">
            <div class="flex items-start gap-4 p-5">
                <div class="relative flex items-center justify-center w-[46px] h-[46px] rounded-[14px] bg-[#EEF2FF] dark:bg-[#818CF8]/20 shrink-0">
                    <svg class="w-[22px] h-[22px] text-[#818CF8]" :class="show ? 'animate-bell' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-2.5 right-2.5 w-[9px] h-[9px] bg-[#F43F5E] rounded-full border-2 border-white dark:border-zinc-900"></span>
                </div>
                <div class="flex-1 pt-0.5">
                    <h4 class="text-[15px] font-extrabold text-[#1E293B] dark:text-white leading-tight tracking-tight">¡Actualización exitosa!</h4>
                    <p class="text-[13px] font-medium text-[#64748B] dark:text-slate-400 mt-1">{{ session('success') }}</p>
                </div>
                <button @click="show = false" class="text-[#94A3B8] hover:text-[#475569] dark:hover:text-white transition-colors p-1 -mr-2">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="h-[3px] bg-slate-100 dark:bg-zinc-800 w-full">
                <div class="h-full bg-[#818CF8]" :class="show ? 'animate-progress' : ''"></div>
            </div>
        </div>
    @endif

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 transition-all duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                Gestión de <span class="text-[#818CF8]">Clientes</span>
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium mt-1.5">Consulta y administra el historial de tus clientes registrados.</p>
        </div>
        <button type="button" @click="modalNuevoCliente = true"
                class="group relative inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#818CF8] hover:bg-[#6366F1] text-white rounded-xl text-sm font-bold transition-all duration-300 hover:-translate-y-0.5 shadow-[0_4px_14px_rgba(129,140,248,0.3)] hover:shadow-[0_6px_20px_rgba(129,140,248,0.45)] shrink-0 active:scale-95">
            <svg class="w-4 h-4 transition-transform duration-300 group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo Cliente
        </button>
    </div>

    {{-- Métricas --}}
    @php
        $totalClientes = $clientes->total();
        $totalVip = $clientes->getCollection()->where('is_vip', true)->count();
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total --}}
        <div class="bg-white dark:bg-[#0c0c0c] rounded-2xl p-5 border border-gray-200 dark:border-neutral-800/60 shadow-sm dark:shadow-2xl hover:-translate-y-1 hover:shadow-md transition-all duration-300 group
                    delay-75 transition-all"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            <div class="w-11 h-11 rounded-xl bg-[#818CF8]/10 text-[#818CF8] border border-[#818CF8]/20 flex items-center justify-center mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <p class="text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-1">Total Clientes</p>
            <div class="flex items-baseline gap-1.5">
                <p class="text-2xl font-black text-slate-900 dark:text-white group-hover:text-[#818CF8] transition-colors">{{ number_format($totalClientes) }}</p>
                <p class="text-xs font-medium text-slate-400">registrados</p>
            </div>
        </div>

        {{-- Ticket promedio --}}
        <div class="bg-white dark:bg-[#0c0c0c] rounded-2xl p-5 border border-gray-200 dark:border-neutral-800/60 shadow-sm dark:shadow-2xl hover:-translate-y-1 hover:shadow-md transition-all duration-300 group
                    delay-100"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30 flex items-center justify-center mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-1">Ticket Promedio</p>
            <div class="flex items-baseline gap-1.5">
                <p class="text-2xl font-black text-slate-900 dark:text-white group-hover:text-emerald-600 transition-colors">$342.50</p>
                <p class="text-xs font-medium text-slate-400">promedio</p>
            </div>
        </div>

        {{-- Retención --}}
        <div class="bg-white dark:bg-[#0c0c0c] rounded-2xl p-5 border border-gray-200 dark:border-neutral-800/60 shadow-sm dark:shadow-2xl hover:-translate-y-1 hover:shadow-md transition-all duration-300 group
                    delay-150"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            <div class="flex items-start justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-800/30 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <span class="text-[10px] font-black text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/30 px-2 py-0.5 rounded-full">78%</span>
            </div>
            <p class="text-[10px] font-black text-gray-400 dark:text-neutral-500 uppercase tracking-[0.15em] mb-1">Retención</p>
            <p class="text-2xl font-black text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors mb-3">78%</p>
            <div class="h-1 bg-gray-100 dark:bg-neutral-800 rounded-full overflow-hidden">
                <div class="h-full bg-blue-500 rounded-full bar-grow" style="--target-w: 78%; width: 0;"></div>
            </div>
        </div>

        {{-- VIP --}}
        <div class="bg-[#818CF8] rounded-2xl p-5 shadow-[0_4px_20px_rgba(129,140,248,0.3)] hover:-translate-y-1 hover:shadow-[0_8px_30px_rgba(129,140,248,0.45)] transition-all duration-300 relative overflow-hidden
                    delay-200"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            {{-- Decoración --}}
            <div class="absolute -top-6 -right-6 w-24 h-24 bg-white/10 rounded-full"></div>
            <div class="absolute -bottom-4 -left-4 w-16 h-16 bg-white/5 rounded-full"></div>

            <div class="flex items-start justify-between mb-4 relative z-10">
                <div class="w-11 h-11 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white border border-white/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
                <span class="text-[10px] font-black text-white/80 bg-white/20 border border-white/20 px-2 py-0.5 rounded-full uppercase tracking-wider">VIP</span>
            </div>
            <p class="text-[10px] font-black text-indigo-100 uppercase tracking-[0.15em] mb-1 relative z-10">Clientes VIP</p>
            <div class="flex items-baseline gap-1.5 relative z-10">
                <p class="text-2xl font-black text-white">{{ $totalVip }}</p>
                <p class="text-xs font-medium text-indigo-100">preferenciales</p>
            </div>
            <div class="mt-3 h-1 bg-white/20 rounded-full overflow-hidden relative z-10">
                <div class="h-full bg-white rounded-full bar-grow"
                     style="--target-w: {{ $totalClientes > 0 ? round(($totalVip / $totalClientes) * 100) : 0 }}%; width: 0;"></div>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white dark:bg-[#0c0c0c] rounded-2xl border border-gray-200 dark:border-neutral-800/60 shadow-sm dark:shadow-2xl overflow-hidden
                transition-all duration-700 delay-300 ease-[cubic-bezier(0.16,1,0.3,1)]"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'">

        {{-- Buscador --}}
        <div class="p-5 border-b border-gray-100 dark:border-neutral-800/60">
            <div class="relative group max-w-xl">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400 group-focus-within:text-[#818CF8] transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" placeholder="Buscar por nombre, teléfono, email..."
                       class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-[#141414] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm font-medium text-slate-800 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-neutral-600 focus:outline-none focus:ring-2 focus:ring-[#818CF8]/40 focus:border-[#818CF8] focus:bg-white dark:focus:bg-[#0c0c0c] transition-all duration-300">
            </div>
        </div>

        {{-- Tabla --}}
        <div class="overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-gray-50/80 dark:bg-[#111] border-b border-gray-100 dark:border-neutral-800/50">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 dark:text-neutral-500 uppercase tracking-[0.2em]">Nombre</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 dark:text-neutral-500 uppercase tracking-[0.2em]">Teléfono</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 dark:text-neutral-500 uppercase tracking-[0.2em]">Email</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 dark:text-neutral-500 uppercase tracking-[0.2em]">Última Compra</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 dark:text-neutral-500 uppercase tracking-[0.2em]">Total</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 dark:text-neutral-500 uppercase tracking-[0.2em] text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-neutral-800/30">
                    @forelse($clientes as $c)
                        @php $delayFila = min($loop->index * 0.05, 0.4); @endphp
                        <tr class="group relative hover:bg-gray-50/60 dark:hover:bg-[#111] transition-all duration-200 row-anim" style="animation-delay: {{ $delayFila }}s">

                            {{-- Borde lateral acento --}}
                            <td class="p-0 w-0">
                                <div class="absolute left-0 top-0 bottom-0 w-[3px] {{ $c->is_vip ? 'bg-[#818CF8]' : 'bg-gray-300 dark:bg-neutral-700' }} opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-r"></div>
                            </td>

                            {{-- Nombre & Avatar --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative flex items-center justify-center w-9 h-9 rounded-xl {{ $c->is_vip ? 'bg-[#818CF8] text-white shadow-md shadow-[#818CF8]/30 vip-badge' : 'bg-gray-100 dark:bg-neutral-800 text-gray-600 dark:text-gray-300' }} font-black text-sm shrink-0 transition-transform duration-300 group-hover:scale-105">
                                        {{ strtoupper(substr($c->nombre, 0, 1)) }}
                                        @if($c->is_vip)
                                            <span class="absolute -top-1 -right-1 w-3 h-3 bg-white dark:bg-[#0c0c0c] rounded-full flex items-center justify-center shadow-sm">
                                                <span class="w-1.5 h-1.5 bg-amber-400 rounded-full"></span>
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#818CF8] transition-colors">{{ $c->nombre }}</p>
                                        <p class="text-[11px] text-slate-400 dark:text-neutral-500 font-medium mt-0.5">
                                            #{{ str_pad($c->id, 5, '0', STR_PAD_LEFT) }}
                                            @if($c->is_vip)
                                                <span class="ml-1.5 text-[9px] font-black text-[#818CF8] bg-[#818CF8]/10 border border-[#818CF8]/20 px-1.5 py-0.5 rounded-full uppercase tracking-wider">VIP</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-slate-500 dark:text-neutral-400">{{ $c->telefono ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-slate-500 dark:text-neutral-400">{{ $c->email ?? '—' }}</td>

                            <td class="px-6 py-4">
                                @if($c->ultima_compra)
                                    <span class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-gray-50 dark:bg-[#141414] text-gray-600 dark:text-neutral-400 border border-gray-100 dark:border-neutral-800">
                                        {{ \Carbon\Carbon::parse($c->ultima_compra)->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-sm font-medium text-slate-400 dark:text-neutral-600">—</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-sm font-black text-slate-900 dark:text-white">
                                ${{ number_format($c->compras_sum ?? 0, 2) }}
                            </td>

                            {{-- Acciones --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200 focus-within:opacity-100">
                                    <a href="{{ route('clientes.show', $c->id) }}"
                                       class="p-2 text-slate-400 hover:text-[#818CF8] hover:bg-[#818CF8]/10 rounded-lg transition-all duration-200" title="Ver Detalles">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <button type="button" @click="clienteEditando = @js($c); modalEditarCliente = true"
                                            class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-all duration-200" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button type="button" @click.prevent="urlEliminar = '{{ route('clientes.destroy', $c->id) }}'; modalEliminarCliente = true"
                                            class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-all duration-200" title="Eliminar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-50 dark:bg-[#141414] rounded-2xl border border-gray-100 dark:border-neutral-800 flex items-center justify-center mb-4 shadow-inner">
                                        <svg class="w-7 h-7 text-gray-300 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <p class="text-sm font-black text-gray-900 dark:text-white">Sin clientes</p>
                                    <p class="text-xs font-semibold text-gray-400 dark:text-neutral-500 mt-1">Aún no hay clientes registrados.</p>
                                </div>
                            </td>
                        </tr>
                    @endempty
                </tbody>
            </table>
        </div>

        @if(method_exists($clientes, 'hasPages') && $clientes->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-neutral-800/50 bg-gray-50/40 dark:bg-[#0a0a0a]">
                {{ $clientes->links() }}
            </div>
        @endif
    </div>

    {{-- Modales Parciales --}}
    @include('clientes.partials.create')
    @include('clientes.partials.edit')

    {{-- Modal Eliminar (trituradora) --}}
    <div x-show="modalEliminarCliente"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[150] bg-slate-900/40 backdrop-blur-[2px] flex items-center justify-center p-4"
         x-data="{ isDeleting: false }"
         @click.away="!isDeleting ? modalEliminarCliente = false : null"
         style="display: none;">

        <div x-show="modalEliminarCliente"
             x-transition:enter="transition ease-out duration-400 delay-75"
             x-transition:enter-start="opacity-0 translate-y-12 scale-90"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white dark:bg-[#151515] rounded-[32px] max-w-[420px] w-full shadow-[0_20px_60px_-15px_rgba(244,63,94,0.15)] overflow-hidden relative">

            <div class="absolute -top-24 -right-24 w-48 h-48 bg-rose-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="p-8 sm:p-10 relative z-10 flex flex-col items-center">
                <div class="shredder-wrapper" :class="isDeleting ? 'is-active' : ''">
                    <div class="s-doc">
                        <div class="s-doc-line"></div>
                        <div class="s-doc-line short"></div>
                        <div class="s-doc-line red"></div>
                    </div>
                    <div class="s-machine">
                        <div class="s-slot"></div>
                        <div class="absolute right-2.5 w-1.5 h-1.5 rounded-full transition-colors duration-300"
                             :class="isDeleting ? 'bg-rose-500 shadow-[0_0_6px_#f43f5e] animate-pulse' : 'bg-emerald-400 shadow-[0_0_4px_#10b981]'"></div>
                    </div>
                    <div class="s-shreds">
                        <div class="s-shred"></div><div class="s-shred"></div><div class="s-shred"></div>
                        <div class="s-shred"></div><div class="s-shred"></div>
                    </div>
                </div>

                <div x-show="!isDeleting" x-transition.opacity class="flex flex-col items-center w-full">
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-3 mt-4 tracking-tight">¿Eliminar cliente?</h3>
                    <p class="text-[15px] font-medium text-slate-500 dark:text-slate-400 leading-relaxed text-center px-2">
                        Esta acción no se puede deshacer. Se eliminarán permanentemente todos sus datos y el historial de compras.
                    </p>
                    <div class="mt-8 flex flex-col sm:flex-row gap-3 w-full">
                        <button type="button" @click="modalEliminarCliente = false"
                                class="flex-1 px-5 py-3.5 text-[15px] font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white bg-slate-50 hover:bg-slate-100 dark:bg-zinc-800/50 dark:hover:bg-zinc-800 rounded-2xl transition-all duration-300">
                            Cancelar
                        </button>
                        <button type="button"
                                @click.prevent="isDeleting = true; setTimeout(() => $refs.formEliminar.submit(), 1400)"
                                class="group relative flex-1 inline-flex items-center justify-center px-5 py-3.5 bg-[#F43F5E] hover:bg-[#E11D48] text-white rounded-2xl text-[15px] font-bold transition-all duration-300 shadow-[0_8px_20px_rgba(244,63,94,0.25)] overflow-hidden">
                            <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                            <span>Sí, eliminar</span>
                        </button>
                    </div>
                </div>

                <div x-show="isDeleting" style="display: none;" x-transition.opacity.duration.500ms class="mt-8 text-center w-full min-h-[140px]">
                    <h3 class="text-xl font-bold text-rose-500 animate-pulse tracking-wide mt-10">Destruyendo datos...</h3>
                </div>
            </div>

            <form x-ref="formEliminar" :action="urlEliminar" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

</div>
@endsection