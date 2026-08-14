@extends('layouts.app')

@section('header_title', 'Clientes')

@section('content')

{{-- ============================================
    Keyframes, animaciones y estilos de scroll
============================================= --}}
<style>
    @keyframes fadeUpIn {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulseVipSoft {
        0%, 100% { box-shadow: 0 0 0 0 rgba(129, 140, 248, 0.3); }
        50%      { box-shadow: 0 0 0 8px rgba(129, 140, 248, 0); }
    }
    
    /* Animación de campana con rebote realista (Toast) */
    @keyframes bell-shake {
        0%, 100% { transform: rotate(0); }
        15% { transform: rotate(20deg); }
        30% { transform: rotate(-20deg); }
        45% { transform: rotate(15deg); }
        60% { transform: rotate(-15deg); }
        75% { transform: rotate(8deg); }
        85% { transform: rotate(-8deg); }
    }
    .animate-bell {
        animation: bell-shake 0.8s cubic-bezier(.36,.07,.19,.97) 0.1s both;
        transform-origin: top center;
    }

    /* Animación de la barra de progreso (Toast) */
    @keyframes shrink-progress {
        from { width: 100%; }
        to { width: 0%; }
    }
    .animate-progress {
        animation: shrink-progress 2.5s linear forwards;
    }
    .toast-container:hover .animate-progress {
        animation-play-state: paused;
    }

    /* ==============================================
       ANIMACIÓN DE LA TRITURADORA DE PAPEL
       ============================================== */
    .shredder-wrapper {
        position: relative;
        width: 120px;
        height: 140px;
        margin: 0 auto;
    }
    
    /* Documento (Estado de reposo flotante) */
    .s-doc {
        position: absolute;
        top: 5px;
        left: 50%;
        transform: translateX(-50%);
        width: 56px;
        height: 66px;
        background: #ffffff;
        border: 2px solid #cbd5e1;
        border-radius: 6px;
        padding: 10px 8px;
        display: flex;
        flex-direction: column;
        gap: 5px;
        z-index: 1;
        animation: float-doc 3s ease-in-out infinite;
    }
    @keyframes float-doc {
        0%, 100% { transform: translate(-50%, 0); }
        50% { transform: translate(-50%, -6px); }
    }
    .s-doc-line { height: 4px; background: #e2e8f0; border-radius: 2px; width: 100%; }
    .s-doc-line.short { width: 60%; }
    .s-doc-line.red { background: #f43f5e; width: 40%; opacity: 0.8; } /* Toque rojo para indicar peligro */

    /* La Máquina Trituradora */
    .s-machine {
        position: absolute;
        top: 65px;
        left: 50%;
        transform: translateX(-50%);
        width: 90px;
        height: 26px;
        background: #0f172a;
        border-radius: 8px;
        z-index: 2;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .s-slot {
        width: 66px;
        height: 4px;
        background: #000000;
        border-radius: 2px;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.5);
    }

    /* Contenedor de las tiras de papel (Ocultas inicialmente) */
    .s-shreds {
        position: absolute;
        top: 90px;
        left: 50%;
        transform: translateX(-50%);
        width: 56px;
        height: 50px;
        z-index: 1;
        display: flex;
        justify-content: space-between;
    }
    .s-shred {
        width: 9px;
        height: 0%; /* Empieza en 0 */
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-top: none;
        transform-origin: top;
        border-radius: 0 0 2px 2px;
    }

    /* --- ESTADO ACTIVO (Triturando) --- */
    .is-active .s-doc {
        /* Cancela el float y baja el documento */
        animation: doc-down 1.2s cubic-bezier(0.4, 0, 1, 1) forwards !important;
    }
    .is-active .s-shred {
        animation: shred-fall 1.2s cubic-bezier(0.4, 0, 1, 1) forwards;
    }
    
    /* Desfase de las tiras para que parezca más realista */
    .is-active .s-shred:nth-child(1) { animation-delay: 0.05s; }
    .is-active .s-shred:nth-child(2) { animation-delay: 0.0s; }
    .is-active .s-shred:nth-child(3) { animation-delay: 0.1s; }
    .is-active .s-shred:nth-child(4) { animation-delay: 0.02s; }
    .is-active .s-shred:nth-child(5) { animation-delay: 0.08s; }

    @keyframes doc-down {
        0% { transform: translate(-50%, 0); opacity: 1; }
        70% { transform: translate(-50%, 65px); opacity: 1; }
        100% { transform: translate(-50%, 65px); opacity: 0; }
    }
    @keyframes shred-fall {
        0% { height: 0%; transform: translateY(0); opacity: 1; }
        50% { height: 100%; transform: translateY(0); opacity: 1; }
        100% { height: 100%; transform: translateY(30px); opacity: 0; }
    }

    .row-anim { animation: fadeUpIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) both; }
    .vip-badge { animation: pulseVipSoft 2.5s cubic-bezier(0.4, 0, 0.6, 1) infinite; }

    /* Ocultamiento de barras de desplazamiento */
    ::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }
    html, body, * { -ms-overflow-style: none !important; scrollbar-width: none !important; }

    @media (prefers-reduced-motion: reduce) {
        .row-anim, .vip-badge, .animate-bell, .animate-progress, .s-doc { animation: none !important; opacity: 1 !important; transform: none !important; }
    }
</style>

<!-- Contenedor principal con Alpine.js -->
<div class="max-w-7xl mx-auto space-y-8 py-6 transition-colors duration-500"
     x-data="{
        cargado: false,
        modalNuevoCliente: {{ session('abrirModalNuevo') ? 'true' : 'false' }},
        modalEditarCliente: {{ session('clienteEditar') ? 'true' : 'false' }},
        clienteEditando: @js(session('clienteEditar')),
        modalEliminarCliente: false,
        urlEliminar: ''
     }"
     x-init="setTimeout(() => cargado = true, 50)">

    {{-- Notificación Flotante Estilo "Pro" --}}
    @if(session('success'))
        <div x-data="{ show: false, timer: null }"
             x-init="
                setTimeout(() => show = true, 50);
                timer = setTimeout(() => show = false, 2500);
             "
             @mouseenter="clearTimeout(timer)"
             @mouseleave="timer = setTimeout(() => show = false, 1500)"
             x-show="show"
             x-transition:enter="transition-all ease-out duration-500"
             x-transition:enter-start="opacity-0 translate-x-full scale-95"
             x-transition:enter-end="opacity-100 translate-x-0 scale-100"
             x-transition:leave="transition-all ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-x-0 scale-100"
             x-transition:leave-end="opacity-0 translate-x-full scale-95"
             class="toast-container fixed top-6 right-6 sm:top-8 sm:right-8 z-[100] w-full max-w-[380px] bg-white dark:bg-zinc-900 rounded-[20px] shadow-[0_8px_30px_rgb(0,0,0,0.12)] dark:shadow-[#818CF8]/10 ring-1 ring-slate-200/50 dark:ring-white/10 overflow-hidden flex flex-col cursor-default"
             style="display: none;">
            
            <div class="flex items-start gap-4 p-5">
                <div class="relative flex items-center justify-center w-[46px] h-[46px] rounded-[14px] bg-[#EEF2FF] dark:bg-[#818CF8]/20 shrink-0">
                    <svg class="w-[22px] h-[22px] text-[#818CF8]" :class="show ? 'animate-bell' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="absolute top-2.5 right-2.5 w-[9px] h-[9px] bg-[#F43F5E] rounded-full border-2 border-white dark:border-zinc-900"></span>
                </div>
                <div class="flex-1 pt-0.5">
                    <h4 class="text-[15px] font-extrabold text-[#1E293B] dark:text-white leading-tight tracking-tight">¡Actualización exitosa!</h4>
                    <p class="text-[13px] font-medium text-[#64748B] dark:text-slate-400 mt-1">{{ session('success') }}</p>
                </div>
                <button @click="show = false" class="text-[#94A3B8] hover:text-[#475569] dark:hover:text-white transition-colors p-1 -mr-2">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="h-[3px] bg-slate-100 dark:bg-zinc-800 w-full">
                <div class="h-full bg-[#818CF8]" :class="show ? 'animate-progress' : ''"></div>
            </div>
        </div>
    @endif

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 transform transition-all duration-700 ease-out"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
        <div class="space-y-1">
            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">
                Gestión de <span class="text-[#818CF8]">Clientes</span>
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 transition-colors font-medium">
                Consulta y administra el historial de tus clientes registrados.
            </p>
        </div>
        
        <button type="button"
                @click="modalNuevoCliente = true"
                class="group relative inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#818CF8] hover:bg-[#6366F1] text-white rounded-xl text-sm font-semibold transition-all duration-300 hover:-translate-y-0.5 shadow-md shadow-[#818CF8]/25 cursor-pointer shrink-0">
            <svg class="w-5 h-5 transition-transform duration-300 group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Nuevo Cliente</span>
        </button>
    </div>

    {{-- Métricas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Card 1: Total Clientes -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 ring-1 ring-slate-100 dark:ring-white/10 shadow-sm transition-all duration-300 hover:-translate-y-2 hover:shadow-lg transform group relative"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center mb-4 text-slate-600 dark:text-slate-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 font-semibold mb-1">Total Clientes</p>
            <div class="flex items-baseline gap-1.5">
                <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">{{ number_format($clientes->total()) }}</p>
                <p class="text-sm font-medium text-slate-500">registrados</p>
            </div>
        </div>

        <!-- Card 2: Ticket Promedio -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 ring-1 ring-slate-100 dark:ring-white/10 shadow-sm transition-all duration-300 hover:-translate-y-2 hover:shadow-lg delay-75 transform group relative"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center mb-4 text-slate-600 dark:text-slate-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 font-semibold mb-1">Ticket Promedio</p>
            <div class="flex items-baseline gap-1.5">
                <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">$342.50</p>
                <p class="text-sm font-medium text-slate-500">promedio</p>
            </div>
        </div>

        <!-- Card 3: Retención -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 ring-1 ring-slate-100 dark:ring-white/10 shadow-sm transition-all duration-300 hover:-translate-y-2 hover:shadow-lg delay-150 transform group relative"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center mb-4 text-slate-600 dark:text-slate-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 font-semibold mb-1">Retención</p>
            <div class="flex items-baseline gap-1.5">
                <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">78%</p>
                <p class="text-sm font-medium text-slate-500">activos</p>
            </div>
        </div>

        <!-- Card 4: Clientes VIP -->
        <div class="bg-[#818CF8] rounded-2xl p-6 shadow-md shadow-[#818CF8]/20 transition-all duration-300 hover:-translate-y-2 hover:shadow-xl hover:shadow-[#818CF8]/40 delay-200 transform group relative overflow-hidden">
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <div class="flex items-center gap-1.5 bg-white/20 px-2.5 py-1 rounded-full backdrop-blur-sm">
                    <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                    <span class="text-[11px] font-bold text-white tracking-wider uppercase">VIP</span>
                </div>
            </div>
            <p class="text-sm text-indigo-100 font-semibold mb-1 relative z-10">Clientes VIP</p>
            <div class="flex items-baseline gap-1.5 relative z-10">
                <p class="text-3xl font-bold tracking-tight text-white">
                    {{ $clientes->getCollection()->where('is_vip', true)->count() }}
                </p>
                <p class="text-sm font-medium text-indigo-100">preferenciales</p>
            </div>
        </div>
    </div>

    {{-- Contenedor de la Tabla Principal --}}
    <div class="bg-white dark:bg-zinc-900 rounded-3xl ring-1 ring-slate-100 dark:ring-white/10 shadow-sm overflow-hidden transition-all duration-700 ease-out delay-300 transform"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'">

        {{-- Buscador --}}
        <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-zinc-800/80 transition-colors">
            <div class="relative group max-w-xl">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-slate-400 group-focus-within:text-[#818CF8] transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" placeholder="Buscar por nombre, teléfono, email..."
                       class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-zinc-950/50 border-0 ring-1 ring-slate-200 dark:ring-white/10 rounded-xl text-sm font-medium text-slate-800 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#818CF8] focus:bg-white dark:focus:bg-zinc-900 transition-all duration-300">
            </div>
        </div>

        {{-- Tabla --}}
        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest border-b border-slate-100 dark:border-zinc-800/60 transition-colors">
                        <th class="px-8 py-4">Nombre</th>
                        <th class="px-6 py-4">Teléfono</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Última Compra</th>
                        <th class="px-6 py-4">Total</th>
                        <th class="px-8 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-zinc-800/40">
                    @forelse($clientes as $c)
                        @php
                            $delayFila = min($loop->index * 0.05, 0.4);
                        @endphp
                        <tr class="group hover:bg-slate-50 dark:hover:bg-zinc-800/40 transition-all duration-200 row-anim" style="animation-delay: {{ $delayFila }}s">
                            
                            {{-- Nombre & Avatar --}}
                            <td class="px-8 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="relative flex items-center justify-center w-10 h-10 rounded-xl {{ $c->is_vip ? 'bg-[#818CF8] text-white shadow-md shadow-[#818CF8]/30 vip-badge' : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300' }} font-bold text-sm shrink-0 transition-transform duration-300 group-hover:scale-105">
                                        {{ strtoupper(substr($c->nombre, 0, 1)) }}
                                        @if($c->is_vip)
                                            <span class="absolute -top-1 -right-1 w-3 h-3 bg-white dark:bg-zinc-900 rounded-full flex items-center justify-center shadow-xs">
                                                <span class="w-1.5 h-1.5 bg-amber-400 rounded-full"></span>
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900 dark:text-zinc-100 transition-colors">
                                            {{ $c->nombre }}
                                        </p>
                                        <p class="text-xs text-slate-400 dark:text-slate-500 font-medium mt-0.5 transition-colors">
                                            ID: #{{ str_pad($c->id, 5, '0', STR_PAD_LEFT) }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Teléfono --}}
                            <td class="px-6 py-4 text-sm font-medium text-slate-600 dark:text-slate-400 transition-colors">
                                {{ $c->telefono ?? '—' }}
                            </td>

                            {{-- Email --}}
                            <td class="px-6 py-4 text-sm font-medium text-slate-600 dark:text-slate-400 transition-colors">
                                {{ $c->email ?? '—' }}
                            </td>

                            {{-- Última Compra --}}
                            <td class="px-6 py-4">
                                @if($c->ultima_compra)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-300 transition-colors">
                                        {{ \Carbon\Carbon::parse($c->ultima_compra)->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-sm font-medium text-slate-400 dark:text-slate-600">—</span>
                                @endif
                            </td>

                            {{-- Total --}}
                            <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white transition-colors">
                                ${{ number_format($c->compras_sum ?? 0, 2) }}
                            </td>

                            {{-- Acciones --}}
                            <td class="px-8 py-4">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 focus-within:opacity-100">
                                    <a href="{{ route('clientes.show', $c->id) }}" 
                                       class="p-2 text-slate-400 hover:text-[#818CF8] hover:bg-indigo-50 dark:hover:bg-zinc-800 rounded-lg transition-all duration-200" 
                                       title="Ver Detalles">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    <button type="button"
                                            @click="clienteEditando = @js($c); modalEditarCliente = true"
                                            class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-zinc-800 rounded-lg transition-all duration-200" 
                                            title="Editar Cliente">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>

                                    {{-- Botón que abre la Trituradora --}}
                                    <button type="button" 
                                            @click.prevent="urlEliminar = '{{ route('clientes.destroy', $c->id) }}'; modalEliminarCliente = true"
                                            class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-all duration-200" 
                                            title="Eliminar Cliente">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-slate-50 dark:bg-zinc-800 text-slate-400 mb-4 ring-1 ring-slate-100">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <p class="text-slate-800 dark:text-slate-200 text-sm font-bold">No se encontraron clientes</p>
                                <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 font-medium">Aún no hay clientes registrados o no coinciden con la búsqueda.</p>
                            </td>
                        </tr>
                    @endempty
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if(method_exists($clientes, 'hasPages') && $clientes->hasPages())
            <div class="px-8 py-4 border-t border-slate-100 dark:border-zinc-800/60 bg-slate-50 dark:bg-zinc-900/40 rounded-b-3xl">
                {{ $clientes->links() }}
            </div>
        @endif
    </div>

    {{-- Modales Parciales --}}
    @include('clientes.partials.create')
    @include('clientes.partials.edit')

    {{-- ============================================
        MODAL PREMIUM: Confirmar Eliminación (Con Trituradora)
    ============================================= --}}
    <div x-show="modalEliminarCliente"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 backdrop-blur-none"
         x-transition:enter-end="opacity-100 backdrop-blur-[2px]"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 backdrop-blur-[2px]"
         x-transition:leave-end="opacity-0 backdrop-blur-none"
         class="fixed inset-0 z-[150] overflow-y-auto bg-slate-900/40 flex items-center justify-center p-4 sm:p-6"
         x-data="{ isDeleting: false }"
         @click.away="!isDeleting ? modalEliminarCliente = false : null"
         style="display: none;">

        <div x-show="modalEliminarCliente"
             x-transition:enter="transition ease-out duration-400 delay-75"
             x-transition:enter-start="opacity-0 translate-y-12 scale-90"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 scale-95"
             class="bg-white dark:bg-[#151515] rounded-[32px] max-w-[420px] w-full shadow-[0_20px_60px_-15px_rgba(244,63,94,0.15)] overflow-hidden transform transition-all relative">
            
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-rose-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="p-8 sm:p-10 relative z-10 flex flex-col items-center">
                
                {{-- ANIMACIÓN DE LA TRITURADORA --}}
                <div class="shredder-wrapper" :class="isDeleting ? 'is-active' : ''">
                    <!-- Documento -->
                    <div class="s-doc">
                        <div class="s-doc-line"></div>
                        <div class="s-doc-line short"></div>
                        <div class="s-doc-line red"></div>
                    </div>
                    <!-- Máquina -->
                    <div class="s-machine">
                        <div class="s-slot"></div>
                        <!-- Led de estado (Verde / Rojo Parpadeante) -->
                        <div class="absolute right-2.5 w-1.5 h-1.5 rounded-full transition-colors duration-300"
                             :class="isDeleting ? 'bg-rose-500 shadow-[0_0_6px_#f43f5e] animate-pulse' : 'bg-emerald-400 shadow-[0_0_4px_#10b981]'"></div>
                    </div>
                    <!-- Tiras cortadas -->
                    <div class="s-shreds">
                        <div class="s-shred"></div>
                        <div class="s-shred"></div>
                        <div class="s-shred"></div>
                        <div class="s-shred"></div>
                        <div class="s-shred"></div>
                    </div>
                </div>

                {{-- Textos y Botones (Estado normal) --}}
                <div x-show="!isDeleting" x-transition.opacity class="flex flex-col items-center w-full">
                    <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white mb-3 mt-4 tracking-tight">¿Eliminar cliente?</h3>
                    <p class="text-[15px] font-medium text-slate-500 dark:text-slate-400 leading-relaxed text-center px-2">
                        Esta acción no se puede deshacer. Se eliminarán permanentemente todos sus datos y el historial de compras.
                    </p>
                    
                    <div class="mt-8 flex flex-col sm:flex-row gap-3 w-full">
                        <button type="button" @click="modalEliminarCliente = false" class="flex-1 px-5 py-3.5 text-[15px] font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white bg-slate-50 hover:bg-slate-100 dark:bg-zinc-800/50 dark:hover:bg-zinc-800 rounded-2xl transition-all duration-300">
                            Cancelar
                        </button>
                        
                        {{-- Botón que activa la animación y luego hace el submit --}}
                        <button type="button" 
                                @click.prevent="isDeleting = true; setTimeout(() => $refs.formEliminar.submit(), 1400)"
                                class="group relative flex-1 inline-flex items-center justify-center px-5 py-3.5 bg-[#F43F5E] hover:bg-[#E11D48] text-white rounded-2xl text-[15px] font-bold transition-all duration-300 shadow-[0_8px_20px_rgba(244,63,94,0.25)] hover:shadow-[0_12px_25px_rgba(244,63,94,0.35)] overflow-hidden">
                            <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                            <span>Sí, eliminar</span>
                        </button>
                    </div>
                </div>

                {{-- Texto mientras tritura --}}
                <div x-show="isDeleting" style="display: none;" x-transition.opacity.duration.500ms class="mt-8 text-center w-full min-h-[140px]">
                    <h3 class="text-xl font-bold text-rose-500 animate-pulse tracking-wide mt-10">Destruyendo datos...</h3>
                </div>

            </div>

            {{-- Formulario oculto para enviar la petición --}}
            <form x-ref="formEliminar" :action="urlEliminar" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>

        </div>
    </div>
</div>
@endsection