@extends('layouts.app')

@section('header_title', 'Detalle de Cliente')

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
    .row-anim { 
        animation: fadeUpIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) both; 
    }
    .vip-badge { 
        animation: pulseVipSoft 2.5s cubic-bezier(0.4, 0, 0.6, 1) infinite; 
    }

    /* Ocultamiento de barras de desplazamiento de forma global */
    ::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
    html, body, * {
        -ms-overflow-style: none !important; 
        scrollbar-width: none !important; 
    }

    @media (prefers-reduced-motion: reduce) {
        .row-anim, .vip-badge { 
            animation: none !important; 
            opacity: 1 !important; 
            transform: none !important; 
        }
    }
</style>

<div class="max-w-6xl mx-auto space-y-6 py-6 transition-colors duration-500"
     x-data="{ cargado: false }"
     x-init="setTimeout(() => cargado = true, 50)">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 transform transition-all duration-700 ease-out"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
        
        <div class="flex items-center gap-4">
            <a href="{{ route('clientes.index') }}" class="p-2.5 rounded-xl border border-slate-200 dark:border-zinc-800 text-slate-400 hover:text-slate-700 dark:text-slate-500 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-zinc-800 transition-all duration-300 hover:-translate-x-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            
            <div class="flex items-center gap-4">
                {{-- Avatar --}}
                <div class="relative flex items-center justify-center w-12 h-12 rounded-xl {{ $cliente->is_vip ? 'bg-[#818CF8] text-white shadow-md shadow-[#818CF8]/30 vip-badge' : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 ring-1 ring-inset ring-slate-900/5 dark:ring-white/10' }} font-bold text-lg shrink-0">
                    {{ strtoupper(substr($cliente->nombre, 0, 1)) }}
                    @if($cliente->is_vip)
                        <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-white dark:bg-zinc-900 rounded-full flex items-center justify-center shadow-xs">
                            <span class="w-2 h-2 bg-amber-400 rounded-full"></span>
                        </span>
                    @endif
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        {{ $cliente->nombre }}
                        @if($cliente->is_vip)
                            <span class="text-[9px] font-bold text-[#818CF8] bg-indigo-50 dark:bg-[#818CF8]/10 border border-indigo-100 dark:border-[#818CF8]/20 px-2 py-0.5 rounded-full uppercase tracking-wider">VIP</span>
                        @endif
                    </h2>
                    <p class="text-sm font-medium text-slate-400 dark:text-slate-500">ID: #C-{{ str_pad($cliente->id, 5, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
        </div>

        <a href="{{ route('clientes.edit', $cliente->id) }}" class="group relative inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-[#818CF8] hover:bg-[#6366F1] text-white rounded-xl text-sm font-bold transition-all duration-300 hover:-translate-y-0.5 shadow-[0_8px_20px_rgba(129,140,248,0.25)] hover:shadow-[0_12px_25px_rgba(129,140,248,0.35)] overflow-hidden shrink-0">
            <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            <span>Editar Cliente</span>
        </a>
    </div>

    {{-- Datos de contacto y fiscales --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 transform transition-all duration-700 delay-100"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'">
         
        <div class="bg-white dark:bg-zinc-900 rounded-3xl p-6 ring-1 ring-slate-100 dark:ring-white/10 shadow-sm hover:-translate-y-1 hover:shadow-md transition-all duration-300">
            <h3 class="text-[11px] font-bold text-[#818CF8] uppercase tracking-widest mb-5">Datos de Contacto</h3>
            <dl class="space-y-4 text-sm">
                <div class="flex justify-between items-center border-b border-slate-50 dark:border-zinc-800/50 pb-3">
                    <dt class="text-slate-500 dark:text-slate-400 font-medium">Teléfono</dt>
                    <dd class="font-bold text-slate-800 dark:text-slate-100">{{ $cliente->telefono ?? '—' }}</dd>
                </div>
                <div class="flex justify-between items-center pt-1">
                    <dt class="text-slate-500 dark:text-slate-400 font-medium">Email</dt>
                    <dd class="font-bold text-slate-800 dark:text-slate-100">{{ $cliente->email ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-3xl p-6 ring-1 ring-slate-100 dark:ring-white/10 shadow-sm hover:-translate-y-1 hover:shadow-md transition-all duration-300">
            <h3 class="text-[11px] font-bold text-[#818CF8] uppercase tracking-widest mb-5">Datos Fiscales</h3>
            <dl class="space-y-4 text-sm">
                <div class="flex justify-between items-center border-b border-slate-50 dark:border-zinc-800/50 pb-3">
                    <dt class="text-slate-500 dark:text-slate-400 font-medium">RFC</dt>
                    <dd class="font-bold text-slate-800 dark:text-slate-100">{{ $cliente->rfc ?? '—' }}</dd>
                </div>
                <div class="flex justify-between items-center border-b border-slate-50 dark:border-zinc-800/50 pb-3 pt-1">
                    <dt class="text-slate-500 dark:text-slate-400 font-medium">Razón Social</dt>
                    <dd class="font-bold text-slate-800 dark:text-slate-100 text-right max-w-[60%] truncate" title="{{ $cliente->razon_social }}">{{ $cliente->razon_social ?? '—' }}</dd>
                </div>
                <div class="flex justify-between items-center pt-1">
                    <dt class="text-slate-500 dark:text-slate-400 font-medium">Régimen Fiscal</dt>
                    <dd class="font-bold text-slate-800 dark:text-slate-100">{{ $cliente->regimen_fiscal ?? '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Métricas de compras (Con la animación Hover de Dashboard) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 transform transition-all duration-700 delay-200"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'">
         
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 ring-1 ring-slate-100 dark:ring-white/10 shadow-sm hover:-translate-y-2 hover:shadow-lg transition-all duration-300 group">
            <p class="text-[11px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest">Total Comprado</p>
            <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-3 group-hover:text-[#818CF8] transition-colors">${{ number_format($cliente->compras_sum ?? 0, 2) }}</p>
        </div>
        
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 ring-1 ring-slate-100 dark:ring-white/10 shadow-sm hover:-translate-y-2 hover:shadow-lg transition-all duration-300 group">
            <p class="text-[11px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest">Número de Compras</p>
            <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-3 group-hover:text-[#818CF8] transition-colors">{{ $cliente->ventas->count() }}</p>
        </div>
        
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 ring-1 ring-slate-100 dark:ring-white/10 shadow-sm hover:-translate-y-2 hover:shadow-lg transition-all duration-300 group">
            <p class="text-[11px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest">Última Compra</p>
            <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-3 group-hover:text-[#818CF8] transition-colors">
                {{ $cliente->ultima_compra ? \Carbon\Carbon::parse($cliente->ultima_compra)->format('d M Y') : '—' }}
            </p>
        </div>
    </div>

    {{-- Historial de ventas --}}
    <div class="bg-white dark:bg-zinc-900 rounded-3xl ring-1 ring-slate-100 dark:ring-white/10 shadow-sm overflow-hidden transform transition-all duration-700 delay-300"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'">
         
        <div class="px-6 py-5 border-b border-slate-100 dark:border-zinc-800/80">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Historial de Compras</h3>
        </div>
        
        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-zinc-800/30 text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest border-b border-slate-100 dark:border-zinc-800/60">
                        <th class="px-6 py-4">Folio</th>
                        <th class="px-6 py-4">Fecha</th>
                        <th class="px-6 py-4">Tipo de Precio</th>
                        <th class="px-6 py-4 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-zinc-800/40">
                    @forelse($cliente->ventas as $venta)
                        @php
                            $delayFila = min($loop->index * 0.05, 0.4);
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-zinc-800/40 transition-colors row-anim" style="animation-delay: {{ $delayFila }}s">
                            <td class="px-6 py-4 text-sm font-bold text-slate-800 dark:text-slate-100">
                                #{{ $venta->folio }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-slate-500 dark:text-slate-400">
                                {{ \Carbon\Carbon::parse($venta->fecha)->format('d M Y, H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-300 capitalize">
                                    {{ $venta->tipo_precio }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white text-right">
                                ${{ number_format($venta->total, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-slate-50 dark:bg-zinc-800 text-slate-400 mb-3 ring-1 ring-slate-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                </div>
                                <p class="text-slate-800 dark:text-slate-200 text-sm font-bold">Sin compras registradas</p>
                                <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 font-medium">Este cliente aún no ha realizado ninguna compra.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection