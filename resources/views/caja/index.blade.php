@extends('layouts.app')

@section('header_title', 'Flujo de Caja')

@section('content')
{{-- Alpine oculta con x-cloak lo que aún no debe verse (ventanas emergentes) --}}
<style>[x-cloak] { display: none !important; }</style>

<div class="max-w-7xl mx-auto">

    {{-- MENSAJES DE ÉXITO / ERROR --}}
    @if(session('success'))
        <div class="mb-4 px-5 py-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-xl flex items-center gap-2 text-sm font-medium">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-5 py-3 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 rounded-xl flex items-center gap-2 text-sm font-medium">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 px-5 py-3 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 rounded-xl flex items-start gap-2 text-sm font-medium">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                @foreach($errors->all() as $mensajeError)
                    <p>{{ $mensajeError }}</p>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ====================== --}}
    {{-- VISTA 1: ABRIR CAJA   --}}
    {{-- ====================== --}}
    @if(is_null($corteActual))
        <div class="flex justify-center items-center min-h-[60vh]">
            <div class="w-full max-w-md bg-white dark:bg-[#15161A] rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">

                <div class="px-8 pt-8 pb-6 text-center border-b border-gray-100 dark:border-white/10">
                    <div class="mx-auto mb-4 w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Apertura de Caja</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ingresa el efectivo inicial para comenzar tu turno</p>
                </div>

                <div class="p-8">
                    {{-- Sucursal donde quedará el corte --}}
                    <div class="mb-6 flex items-center gap-2 px-4 py-3 bg-gray-50 dark:bg-white/[0.03] border border-gray-200 dark:border-white/10 rounded-xl text-sm">
                        <svg class="w-4 h-4 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span class="text-gray-500 dark:text-gray-400">Sucursal del turno:</span>
                        <span class="font-bold text-gray-900 dark:text-gray-100">{{ $sucursalUsuario?->nombre ?? 'Sin sucursal asignada' }}</span>
                    </div>

                    <form action="{{ route('caja.abrir') }}" method="POST" x-data="{ enviando: false }" @submit="enviando = true">
                        @csrf
                        <div class="mb-6">
                            <label for="saldo_inicial" class="block text-gray-700 dark:text-gray-200 font-bold mb-2">Fondo de Caja (Efectivo)</label>
                            <div class="flex items-stretch border border-gray-300 dark:border-white/15 rounded-xl overflow-hidden focus-within:ring-4 focus-within:ring-indigo-500/20 focus-within:border-indigo-500 transition">
                                <span class="bg-gray-50 dark:bg-white/[0.03] px-5 py-3 font-bold text-gray-500 dark:text-gray-400 border-r border-gray-300 dark:border-white/15 flex items-center">$</span>
                                <input type="number" step="0.01" min="0" class="w-full px-4 py-3 text-lg outline-none bg-transparent text-gray-900 dark:text-white placeholder-gray-400" name="saldo_inicial" id="saldo_inicial" placeholder="Ej. 1000.00" required autofocus>
                            </div>
                        </div>
                        <button type="submit" :disabled="enviando"
                            class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold py-3.5 px-4 rounded-xl transition-colors text-base">
                            <span>Abrir Caja e Ir a Vender</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    {{-- ========================= --}}
    {{-- VISTA 2: CAJA ABIERTA    --}}
    {{-- ========================= --}}
    @else
        @php
            $cantidadVentas = $ventasDelTurno->count();
            $totalVendido   = $ventasDelTurno->sum('total');
        @endphp

        <div x-data="cajaTurno({ esperado: {{ round((float) $saldoEstimado, 2) }}, abrirCierre: @js($errors->has('efectivo_contado')), contado: @js(old('efectivo_contado', '')) })"
             @keydown.escape.window="modal = null">

            {{-- DATOS DEL TURNO --}}
            <div class="mb-6 flex flex-wrap items-center gap-x-4 gap-y-2">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Turno abierto
                </span>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <span class="font-semibold text-gray-700 dark:text-gray-200">Turno #{{ $corteActual->id }}</span>
                    · {{ $corteActual->sucursal?->nombre ?? 'N/A' }}
                    · Cajero: {{ Auth::user()->name }}
                    · Apertura: {{ \Carbon\Carbon::parse($corteActual->fecha_apertura)->format('d/m/Y H:i') }}
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                {{-- COLUMNA IZQUIERDA: RESUMEN Y ACCIONES --}}
                <div class="lg:col-span-4 xl:col-span-3">
                    <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-6">

                        <div class="text-center pb-6 border-b border-gray-100 dark:border-white/10">
                            <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Efectivo estimado en caja</span>
                            <p class="mt-2 text-4xl font-extrabold text-indigo-600 dark:text-indigo-400 tabular-nums">${{ number_format($saldoEstimado, 2) }}</p>
                        </div>

                        <dl class="py-5 space-y-3 text-sm border-b border-gray-100 dark:border-white/10">
                            <div class="flex justify-between items-center">
                                <dt class="text-gray-500 dark:text-gray-400">Saldo inicial</dt>
                                <dd class="font-semibold text-gray-800 dark:text-gray-100 tabular-nums">${{ number_format($corteActual->saldo_inicial, 2) }}</dd>
                            </div>
                            <div class="flex justify-between items-center">
                                <dt class="text-gray-500 dark:text-gray-400">+ Ventas (efectivo)</dt>
                                <dd class="font-semibold text-emerald-600 dark:text-emerald-400 tabular-nums">+${{ number_format($totalVentas, 2) }}</dd>
                            </div>
                            <div class="flex justify-between items-center">
                                <dt class="text-gray-500 dark:text-gray-400">+ Anticipos</dt>
                                <dd class="font-semibold text-emerald-600 dark:text-emerald-400 tabular-nums">+${{ number_format($totalAnticipos, 2) }}</dd>
                            </div>
                            <div class="flex justify-between items-center">
                                <dt class="text-gray-500 dark:text-gray-400">- Gastos</dt>
                                <dd class="font-semibold text-red-600 dark:text-red-400 tabular-nums">-${{ number_format($totalGastos, 2) }}</dd>
                            </div>
                        </dl>

                        <div class="pt-5 space-y-3">
                            <a href="{{ route('ventas.index') }}"
                               class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                Ir al Punto de Venta
                            </a>

                            <button type="button" @click="abrir('gasto')"
                                class="w-full flex items-center justify-center gap-2 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5 font-semibold py-2.5 px-4 rounded-xl transition-colors">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Registrar Gasto
                            </button>

                            <button type="button" @click="abrir('anticipo')"
                                class="w-full flex items-center justify-center gap-2 bg-white dark:bg-transparent border border-gray-200 dark:border-white/15 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5 font-semibold py-2.5 px-4 rounded-xl transition-colors">
                                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Registrar Anticipo
                            </button>
                        </div>

                        {{-- Cerrar Caja: separada del resto porque termina el turno --}}
                        <div class="mt-5 pt-5 border-t border-gray-100 dark:border-white/10">
                            <button type="button" @click="abrir('cierre')"
                                class="w-full flex items-center justify-center gap-2 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-950/50 font-bold py-2.5 px-4 rounded-xl transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                Cerrar Caja
                            </button>
                        </div>
                    </div>
                </div>

                {{-- COLUMNA DERECHA: TABLAS --}}
                <div class="lg:col-span-8 xl:col-span-9 space-y-6">

                    {{-- VENTAS DEL TURNO --}}
                    <section class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between gap-4 px-6 py-4 border-b border-gray-100 dark:border-white/10">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </span>
                                <div class="min-w-0">
                                    <h3 class="font-bold text-gray-900 dark:text-white leading-tight">Ventas del Turno</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $cantidadVentas }} {{ $cantidadVentas === 1 ? 'venta' : 'ventas' }} · Total vendido ${{ number_format($totalVendido, 2) }} (todos los métodos)
                                    </p>
                                </div>
                            </div>
                            <span class="shrink-0 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 text-sm font-bold tabular-nums">
                                Efectivo: ${{ number_format($totalVentas, 2) }}
                            </span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left whitespace-nowrap">
                                <thead class="bg-gray-50 dark:bg-white/[0.03] text-gray-500 dark:text-gray-400 text-xs uppercase font-bold tracking-wider">
                                    <tr>
                                        <th class="px-6 py-3">Fecha</th>
                                        <th class="px-6 py-3 text-right">Total</th>
                                        <th class="px-6 py-3">Método</th>
                                        <th class="px-6 py-3 text-right">Ticket</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-white/10 text-sm">
                                    @forelse($ventasDelTurno as $venta)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors">
                                        <td class="px-6 py-3.5 text-gray-600 dark:text-gray-300">{{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-3.5 text-right font-bold text-gray-900 dark:text-white tabular-nums">${{ number_format($venta->total, 2) }}</td>
                                        @php
                                            $metodo = $venta->metodo_pago ?? 'Efectivo';
                                            $claseMetodo = match ($metodo) {
                                                'Tarjeta'       => 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800/50',
                                                'Transferencia' => 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-400 border-purple-200 dark:border-purple-800/50',
                                                default         => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/50',
                                            };
                                        @endphp
                                        <td class="px-6 py-3.5">
                                            <span class="inline-flex items-center border px-3 py-0.5 rounded-full text-xs font-bold {{ $claseMetodo }}">{{ $metodo }}</span>
                                        </td>
                                        <td class="px-6 py-3.5 text-right">
                                            <a href="{{ route('ventas.ticket', $venta->id) }}" onclick="event.preventDefault(); imprimirTicket(this.href);"
                                               class="inline-flex items-center justify-center w-9 h-9 rounded-full text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-colors" title="Reimprimir Ticket">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                                            No hay ventas registradas en este turno aún.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    {{-- ANTICIPOS --}}
                    <section class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between gap-4 px-6 py-4 border-b border-gray-100 dark:border-white/10">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </span>
                                <div class="min-w-0">
                                    <h3 class="font-bold text-gray-900 dark:text-white leading-tight">Anticipos / Apartados</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Dinero que entró al cajón sin ser una venta</p>
                                </div>
                            </div>
                            <span class="shrink-0 px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-500/10 text-amber-800 dark:text-amber-300 text-sm font-bold tabular-nums">
                                Total: ${{ number_format($totalAnticipos, 2) }}
                            </span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left whitespace-nowrap">
                                <thead class="bg-gray-50 dark:bg-white/[0.03] text-gray-500 dark:text-gray-400 text-xs uppercase font-bold tracking-wider">
                                    <tr>
                                        <th class="px-6 py-3">Fecha</th>
                                        <th class="px-6 py-3">Concepto</th>
                                        <th class="px-6 py-3 text-right">Monto</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-white/10 text-sm">
                                    @forelse($anticiposDelTurno as $anticipo)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors">
                                        <td class="px-6 py-3.5 text-gray-600 dark:text-gray-300">{{ \Carbon\Carbon::parse($anticipo->created_at)->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-3.5 text-gray-800 dark:text-white whitespace-normal">{{ $anticipo->concepto }}</td>
                                        <td class="px-6 py-3.5 text-right font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">+${{ number_format($anticipo->monto, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                                            No hay anticipos registrados en este turno.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    {{-- GASTOS --}}
                    <section class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between gap-4 px-6 py-4 border-b border-gray-100 dark:border-white/10">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                                </span>
                                <div class="min-w-0">
                                    <h3 class="font-bold text-gray-900 dark:text-white leading-tight">Gastos y Salidas</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Dinero que salió del cajón</p>
                                </div>
                            </div>
                            <span class="shrink-0 px-3 py-1 rounded-full bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-300 text-sm font-bold tabular-nums">
                                Total: ${{ number_format($totalGastos, 2) }}
                            </span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left whitespace-nowrap">
                                <thead class="bg-gray-50 dark:bg-white/[0.03] text-gray-500 dark:text-gray-400 text-xs uppercase font-bold tracking-wider">
                                    <tr>
                                        <th class="px-6 py-3">Fecha</th>
                                        <th class="px-6 py-3">Concepto</th>
                                        <th class="px-6 py-3 text-right">Monto</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-white/10 text-sm">
                                    @forelse($gastosDelTurno as $gasto)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors">
                                        <td class="px-6 py-3.5 text-gray-600 dark:text-gray-300">{{ \Carbon\Carbon::parse($gasto->created_at)->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-3.5 text-gray-800 dark:text-white whitespace-normal">{{ $gasto->concepto }}</td>
                                        <td class="px-6 py-3.5 text-right font-bold text-red-600 dark:text-red-400 tabular-nums">-${{ number_format($gasto->monto, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                                            No hay gastos registrados en este turno.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                </div>{{-- fin columna derecha --}}
            </div>{{-- fin grid --}}

            {{-- ===================== --}}
            {{-- VENTANA: GASTO        --}}
            {{-- ===================== --}}
            <div x-show="modal === 'gasto'" x-cloak x-transition.opacity
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                 role="dialog" aria-modal="true" aria-labelledby="titulo-gasto"
                 @click.self="modal = null">
                <div class="bg-white dark:bg-[#15161A] rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-transparent dark:border-white/10">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                        <h3 id="titulo-gasto" class="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
                            <span class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                            Registrar Gasto / Salida
                        </h3>
                        <button type="button" @click="modal = null" class="text-gray-400 hover:text-gray-700 dark:hover:text-white transition-colors" aria-label="Cerrar ventana">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <form action="{{ route('caja.gasto.store') }}" method="POST" class="p-6 space-y-4" @submit="enviando = true">
                        @csrf
                        <div>
                            <label class="block text-gray-700 dark:text-gray-200 font-bold mb-2">Concepto</label>
                            <input type="text" name="concepto" placeholder="Ej. Compra de refacciones" required maxlength="255"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-white/15 rounded-xl outline-none bg-transparent text-gray-900 dark:text-white focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition">
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-gray-200 font-bold mb-2">Monto</label>
                            <div class="flex items-stretch border border-gray-300 dark:border-white/15 rounded-xl overflow-hidden focus-within:ring-4 focus-within:ring-red-500/20 focus-within:border-red-500 transition">
                                <span class="bg-gray-50 dark:bg-white/[0.03] px-4 py-3 font-bold text-gray-500 dark:text-gray-400 border-r border-gray-300 dark:border-white/15 flex items-center">$</span>
                                <input type="number" step="0.01" min="0.01" name="monto" placeholder="0.00" required
                                    class="w-full px-4 py-3 outline-none bg-transparent text-gray-900 dark:text-white">
                            </div>
                        </div>
                        <button type="submit" :disabled="enviando"
                            class="w-full bg-red-600 hover:bg-red-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold py-3 rounded-xl transition-colors">
                            Guardar Gasto
                        </button>
                    </form>
                </div>
            </div>

            {{-- ===================== --}}
            {{-- VENTANA: ANTICIPO     --}}
            {{-- ===================== --}}
            <div x-show="modal === 'anticipo'" x-cloak x-transition.opacity
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                 role="dialog" aria-modal="true" aria-labelledby="titulo-anticipo"
                 @click.self="modal = null">
                <div class="bg-white dark:bg-[#15161A] rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-transparent dark:border-white/10">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                        <h3 id="titulo-anticipo" class="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
                            <span class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                            Registrar Anticipo / Apartado
                        </h3>
                        <button type="button" @click="modal = null" class="text-gray-400 hover:text-gray-700 dark:hover:text-white transition-colors" aria-label="Cerrar ventana">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <form action="{{ route('caja.anticipo.store') }}" method="POST" class="p-6 space-y-4" @submit="enviando = true">
                        @csrf
                        <div>
                            <label class="block text-gray-700 dark:text-gray-200 font-bold mb-2">Concepto</label>
                            <input type="text" name="concepto" placeholder="Ej. Anticipo de cliente Juan" required maxlength="255"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-white/15 rounded-xl outline-none bg-transparent text-gray-900 dark:text-white focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition">
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-gray-200 font-bold mb-2">Monto</label>
                            <div class="flex items-stretch border border-gray-300 dark:border-white/15 rounded-xl overflow-hidden focus-within:ring-4 focus-within:ring-amber-500/20 focus-within:border-amber-500 transition">
                                <span class="bg-gray-50 dark:bg-white/[0.03] px-4 py-3 font-bold text-gray-500 dark:text-gray-400 border-r border-gray-300 dark:border-white/15 flex items-center">$</span>
                                <input type="number" step="0.01" min="0.01" name="monto" placeholder="0.00" required
                                    class="w-full px-4 py-3 outline-none bg-transparent text-gray-900 dark:text-white">
                            </div>
                        </div>
                        <button type="submit" :disabled="enviando"
                            class="w-full bg-amber-700 hover:bg-amber-800 disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold py-3 rounded-xl transition-colors">
                            Guardar Anticipo
                        </button>
                    </form>
                </div>
            </div>

            {{-- ===================== --}}
            {{-- VENTANA: CERRAR CAJA  --}}
            {{-- ===================== --}}
            <div x-show="modal === 'cierre'" x-cloak x-transition.opacity
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                 role="dialog" aria-modal="true" aria-labelledby="titulo-cierre"
                 @click.self="modal = null">
                <div class="bg-white dark:bg-[#15161A] rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-transparent dark:border-white/10">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                        <h3 id="titulo-cierre" class="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
                            <span class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </span>
                            Cerrar Caja
                        </h3>
                        <button type="button" @click="modal = null" class="text-gray-400 hover:text-gray-700 dark:hover:text-white transition-colors" aria-label="Cerrar ventana">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <form action="{{ route('caja.cerrar') }}" method="POST" class="p-6 space-y-4" @submit="enviando = true">
                        @csrf
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Cuenta el efectivo que hay en el cajón y captúralo. Al cerrar ya no podrás hacer más ventas en este turno.
                        </p>

                        <div class="flex justify-between items-center p-3 rounded-xl bg-gray-50 dark:bg-white/[0.03]">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">Efectivo esperado:</span>
                            <span class="font-extrabold text-indigo-600 dark:text-indigo-400 text-lg tabular-nums">${{ number_format($saldoEstimado, 2) }}</span>
                        </div>

                        <div>
                            <label for="efectivo_contado" class="block text-gray-700 dark:text-gray-200 font-bold mb-2">Efectivo contado</label>
                            <div class="flex items-stretch border border-gray-300 dark:border-white/15 rounded-xl overflow-hidden focus-within:ring-4 focus-within:ring-red-500/20 focus-within:border-red-500 transition">
                                <span class="bg-gray-50 dark:bg-white/[0.03] px-4 py-3 font-bold text-gray-500 dark:text-gray-400 border-r border-gray-300 dark:border-white/15 flex items-center">$</span>
                                <input type="number" step="0.01" min="0" name="efectivo_contado" id="efectivo_contado" placeholder="0.00" required
                                    x-ref="contado" x-model="contado"
                                    class="w-full px-4 py-3 outline-none bg-transparent text-gray-900 dark:text-white">
                            </div>
                        </div>

                        {{-- Diferencia en vivo --}}
                        <div x-show="diferencia !== null" x-cloak
                             class="p-3 rounded-xl text-sm font-bold text-center"
                             :class="diferencia > 0
                                ? 'bg-amber-50 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400'
                                : (diferencia < 0
                                    ? 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400'
                                    : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400')"
                             x-text="textoDiferencia"></div>

                        <button type="submit" :disabled="enviando"
                            class="w-full bg-red-600 hover:bg-red-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold py-3 rounded-xl transition-colors">
                            Confirmar y Cerrar Caja
                        </button>
                    </form>
                </div>
            </div>

        </div>{{-- fin x-data --}}

        <script>
            document.addEventListener('alpine:init', function () {
                Alpine.data('cajaTurno', function (config) {
                    return {
                        modal: config.abrirCierre ? 'cierre' : null,
                        esperado: config.esperado,
                        contado: config.contado,
                        enviando: false,

                        abrir: function (nombre) {
                            this.modal = nombre;
                            if (nombre === 'cierre') {
                                var self = this;
                                this.$nextTick(function () { self.$refs.contado.focus(); });
                            }
                        },

                        // contado - esperado: positivo = sobrante, negativo = faltante
                        get diferencia() {
                            if (this.contado === '' || this.contado === null) { return null; }
                            var numero = parseFloat(this.contado);
                            if (isNaN(numero)) { return null; }
                            return Math.round((numero - this.esperado) * 100) / 100;
                        },

                        get textoDiferencia() {
                            var d = this.diferencia;
                            if (d === null) { return ''; }
                            var monto = '$' + Math.abs(d).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            if (d > 0) { return 'Sobrante de ' + monto; }
                            if (d < 0) { return 'Faltante de ' + monto; }
                            return 'La caja cuadra exacto';
                        }
                    };
                });
            });
        </script>

    @endif
</div>
@endsection