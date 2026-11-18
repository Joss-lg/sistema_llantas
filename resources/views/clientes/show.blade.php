@extends('layouts.app')

@section('header_title', 'Detalle de Cliente')

@section('content')

@php
    $numeroCompras = $cliente->ventas->count();
    $totalComprado = (float) ($cliente->compras_sum ?? 0);
    $ticketPromedio = $numeroCompras > 0 ? $totalComprado / $numeroCompras : 0;
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('clientes.index') }}"
               class="inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver a Clientes
            </a>
            <div class="flex items-center gap-4">
                <span class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 text-lg font-bold {{ $cliente->is_vip ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' }}">
                    {{ mb_strtoupper(mb_substr($cliente->nombre, 0, 1)) }}
                </span>
                <div>
                    <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                        {{ $cliente->nombre }}
                        @if($cliente->is_vip)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wide bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50">VIP</span>
                        @endif
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Cliente #{{ str_pad($cliente->id, 5, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
        </div>

        <a href="{{ route('clientes.edit', $cliente->id) }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Editar cliente
        </a>
    </div>

    {{-- TARJETAS DE COMPRAS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total comprado</p>
            </div>
            <p class="mt-3 text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 tabular-nums">${{ number_format($totalComprado, 2) }}</p>
        </div>

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Compras</p>
            </div>
            <p class="mt-3 text-2xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($numeroCompras) }}</p>
        </div>

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Ticket promedio</p>
            </div>
            <p class="mt-3 text-2xl font-extrabold text-gray-900 dark:text-white tabular-nums">${{ number_format($ticketPromedio, 2) }}</p>
        </div>

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Última compra</p>
            </div>
            <p class="mt-3 text-2xl font-extrabold text-gray-900 dark:text-white tabular-nums">
                {{ $cliente->ultima_compra ? \Carbon\Carbon::parse($cliente->ultima_compra)->format('d/m/Y') : '—' }}
            </p>
        </div>
    </div>

    {{-- DATOS DE CONTACTO Y FISCALES --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5 sm:p-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Datos de contacto</h3>
            <dl class="divide-y divide-gray-100 dark:divide-white/10 text-sm">
                <div class="flex justify-between gap-4 py-3 first:pt-0">
                    <dt class="text-gray-500 dark:text-gray-400">Teléfono</dt>
                    <dd class="font-semibold text-gray-900 dark:text-white text-right">{{ $cliente->telefono ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-3 last:pb-0">
                    <dt class="text-gray-500 dark:text-gray-400">Correo electrónico</dt>
                    <dd class="font-semibold text-gray-900 dark:text-white text-right break-all">{{ $cliente->email ?: '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5 sm:p-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Datos fiscales</h3>
            <dl class="divide-y divide-gray-100 dark:divide-white/10 text-sm">
                <div class="flex justify-between gap-4 py-3 first:pt-0">
                    <dt class="text-gray-500 dark:text-gray-400">RFC</dt>
                    <dd class="font-semibold text-gray-900 dark:text-white text-right">{{ $cliente->rfc ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-gray-500 dark:text-gray-400">Razón social</dt>
                    <dd class="font-semibold text-gray-900 dark:text-white text-right">{{ $cliente->razon_social ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-gray-500 dark:text-gray-400">Régimen fiscal</dt>
                    <dd class="font-semibold text-gray-900 dark:text-white text-right">{{ $cliente->regimen_fiscal ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-gray-500 dark:text-gray-400">Uso CFDI</dt>
                    <dd class="font-semibold text-gray-900 dark:text-white text-right">{{ $cliente->uso_cfdi ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-3 last:pb-0">
                    <dt class="text-gray-500 dark:text-gray-400">Código postal</dt>
                    <dd class="font-semibold text-gray-900 dark:text-white text-right">{{ $cliente->cp ?: '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- HISTORIAL DE COMPRAS --}}
    <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Historial de compras</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/[0.03] text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-white/10">
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Folio / Fecha</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Pago</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap">Tipo de precio</th>
                        <th class="px-5 py-4 font-bold whitespace-nowrap text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700 dark:text-gray-300 divide-y divide-gray-100 dark:divide-white/10">
                    @forelse($cliente->ventas as $venta)
                        @php
                            $metodo = $venta->metodo_pago ?? 'Efectivo';
                            $clasePago = match ($metodo) {
                                'Tarjeta'       => 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800/50',
                                'Transferencia' => 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-400 border-purple-200 dark:border-purple-800/50',
                                default         => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/50',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="font-bold text-gray-900 dark:text-white">{{ $venta->folio }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y H:i') }}</p>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center border px-3 py-0.5 rounded-full text-xs font-bold {{ $clasePago }}">{{ $metodo }}</span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">{{ ucfirst($venta->tipo_precio) }}</td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <span class="text-base font-extrabold text-emerald-600 dark:text-emerald-400 tabular-nums">${{ number_format($venta->total, 2) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-14 text-center">
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 mb-4">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                </div>
                                <p class="text-base font-bold text-gray-800 dark:text-gray-200">Sin compras registradas</p>
                                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Este cliente aún no ha realizado ninguna compra.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
