@extends('layouts.app')

@section('header_title', 'Detalle de Cliente')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Encabezado --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('clientes.index') }}" class="p-2 rounded-xl border border-gray-200 dark:border-neutral-800 text-gray-500 dark:text-neutral-400 hover:bg-gray-100 dark:hover:bg-neutral-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full {{ $cliente->is_vip ? 'bg-[#D32030] text-white' : 'bg-gray-100 dark:bg-neutral-800 text-gray-600 dark:text-gray-300' }} flex items-center justify-center font-bold text-lg shadow-sm border border-transparent dark:border-neutral-700">
                    {{ strtoupper(substr($cliente->nombre, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        {{ $cliente->nombre }}
                        @if($cliente->is_vip)
                            <span class="text-[9px] font-bold text-[#D32030] dark:text-red-500 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-900/50 px-1.5 py-0.5 rounded-full uppercase tracking-wider">VIP</span>
                        @endif
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-neutral-400">ID: #C-{{ str_pad($cliente->id, 5, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
        </div>
        <a href="{{ route('clientes.edit', $cliente->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#D32030] text-white rounded-xl text-sm font-semibold hover:bg-[#B91C2C] active:scale-95 transition-all duration-200 shadow-lg shadow-red-500/20">
            Editar Cliente
        </a>
    </div>

    {{-- Datos de contacto y fiscales --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="bg-white dark:bg-[#151515] rounded-2xl p-6 border border-gray-200 dark:border-neutral-800">
            <h3 class="text-xs font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-wide mb-4">Datos de Contacto</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-neutral-400">Teléfono</dt><dd class="font-semibold text-gray-800 dark:text-gray-100">{{ $cliente->telefono ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-neutral-400">Email</dt><dd class="font-semibold text-gray-800 dark:text-gray-100">{{ $cliente->email ?? '—' }}</dd></div>
            </dl>
        </div>

        <div class="bg-white dark:bg-[#151515] rounded-2xl p-6 border border-gray-200 dark:border-neutral-800">
            <h3 class="text-xs font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-wide mb-4">Datos Fiscales</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-neutral-400">RFC</dt><dd class="font-semibold text-gray-800 dark:text-gray-100">{{ $cliente->rfc ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-neutral-400">Razón Social</dt><dd class="font-semibold text-gray-800 dark:text-gray-100">{{ $cliente->razon_social ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-neutral-400">Uso CFDI</dt><dd class="font-semibold text-gray-800 dark:text-gray-100">{{ $cliente->uso_cfdi ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-neutral-400">Código Postal</dt><dd class="font-semibold text-gray-800 dark:text-gray-100">{{ $cliente->cp ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-neutral-400">Régimen Fiscal</dt><dd class="font-semibold text-gray-800 dark:text-gray-100">{{ $cliente->regimen_fiscal ?? '—' }}</dd></div>
            </dl>
        </div>
    </div>

    {{-- Métricas de compras --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white dark:bg-[#151515] rounded-2xl p-6 border border-gray-200 dark:border-neutral-800">
            <p class="text-xs text-gray-400 dark:text-neutral-500 font-semibold uppercase tracking-wide">Total Comprado</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">${{ number_format($cliente->compras_sum ?? 0, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-[#151515] rounded-2xl p-6 border border-gray-200 dark:border-neutral-800">
            <p class="text-xs text-gray-400 dark:text-neutral-500 font-semibold uppercase tracking-wide">Número de Compras</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $cliente->ventas->count() }}</p>
        </div>
        <div class="bg-white dark:bg-[#151515] rounded-2xl p-6 border border-gray-200 dark:border-neutral-800">
            <p class="text-xs text-gray-400 dark:text-neutral-500 font-semibold uppercase tracking-wide">Última Compra</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                {{ $cliente->ultima_compra ? \Carbon\Carbon::parse($cliente->ultima_compra)->format('d M Y') : '—' }}
            </p>
        </div>
    </div>

    {{-- Historial de ventas --}}
    <div class="bg-white dark:bg-[#151515] rounded-2xl border border-gray-200 dark:border-neutral-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-neutral-800">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Historial de Compras</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-[#0A0A0A] text-[11px] font-bold text-gray-500 dark:text-neutral-500 uppercase tracking-wider border-b border-gray-100 dark:border-neutral-800">
                        <th class="px-6 py-3 text-left">Folio</th>
                        <th class="px-6 py-3 text-left">Fecha</th>
                        <th class="px-6 py-3 text-left">Tipo de Precio</th>
                        <th class="px-6 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                    @forelse($cliente->ventas as $venta)
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-neutral-800/40 transition-colors">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $venta->folio }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ \Carbon\Carbon::parse($venta->fecha)->format('d M Y, H:i') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 capitalize">{{ $venta->tipo_precio }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-800 dark:text-white text-right">${{ number_format($venta->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400 dark:text-neutral-500 text-sm">
                                Este cliente aún no tiene compras registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection