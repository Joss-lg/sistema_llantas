@extends('layouts.app')

@section('header_title', 'Inicio')

@section('content')

@php
    $nombrePila = \Illuminate\Support\Str::before(trim((string) auth()->user()->name), ' ');
    $dinero     = fn ($n) => '$' . number_format((float) $n, 2);
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white">Hola, {{ $nombrePila }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $fechaHoy }}
                @if($sucursalNombre)
                    <span class="mx-1 text-gray-300 dark:text-gray-600">·</span>{{ $sucursalNombre }}
                @endif
            </p>
        </div>

    </div>

    {{-- Sin permiso para ver los datos del inicio --}}
    @if($sinPermiso)
        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-8 text-center">
            <span class="mx-auto w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </span>
            <p class="mt-3 text-base font-bold text-gray-900 dark:text-white">Bienvenido</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Usa el menú de la izquierda para entrar a tus módulos.</p>
        </div>
    @else

    {{-- Aviso: usuario sin sucursal --}}
    @if($sinSucursal)
        <div class="flex items-start gap-3 p-4 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 text-amber-700 dark:text-amber-400 rounded-xl text-sm font-medium">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            <span>Tu usuario no tiene una sucursal asignada, por eso no se muestran datos. Pide a un administrador que te asigne una.</span>
        </div>
    @endif

    {{-- TARJETAS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- Ventas de hoy --}}
        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Ventas de hoy</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ $dinero($ventasHoy) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                {{ number_format($numVentasHoy) }} {{ $numVentasHoy === 1 ? 'venta' : 'ventas' }}
                @if(! $sinSucursal)
                    @if($cambioVsAyer === null)
                        · ayer no hubo ventas
                    @elseif($cambioVsAyer > 0)
                        · <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $cambioVsAyer }}% más que ayer</span>
                    @elseif($cambioVsAyer < 0)
                        · <span class="font-semibold text-red-600 dark:text-red-400">{{ abs($cambioVsAyer) }}% menos que ayer</span>
                    @else
                        · igual que ayer
                    @endif
                @endif
            </p>
        </div>

        {{-- Piezas vendidas --}}
        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Piezas vendidas</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($llantasVendidas) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">hoy, sin contar servicios</p>
        </div>

        {{-- Bajo stock --}}
        @php
            $tarjetaBajo = 'bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5 block';
        @endphp
        @if($puede['inventario'] && ! $sinSucursal)
            <a href="{{ route('inventario.index', ['stock_status' => 'bajo_stock']) }}" class="{{ $tarjetaBajo }} hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
        @else
            <div class="{{ $tarjetaBajo }}">
        @endif
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Bajo stock</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($bajoStock) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                {{ $bajoStock === 1 ? 'producto por debajo' : 'productos por debajo' }} de su mínimo
            </p>
        @if($puede['inventario'] && ! $sinSucursal)
            </a>
        @else
            </div>
        @endif

        {{-- Clientes nuevos --}}
        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </span>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Clientes nuevos</p>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ number_format($clientesNuevos) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">registrados hoy</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Ventas de los últimos 7 días --}}
        <div class="lg:col-span-2 bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Ventas de los últimos 7 días</h3>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Total de la semana</p>
                </div>
                <p class="text-xl font-extrabold text-gray-900 dark:text-white tabular-nums">{{ $dinero($totalSemana) }}</p>
            </div>

            <div class="mt-6 flex items-end gap-3 h-44">
                @foreach($ventasSemana as $dia)
                    @php
                        $alto = $maxSemana > 0 ? max((int) round($dia['total'] / $maxSemana * 100), $dia['total'] > 0 ? 4 : 0) : 0;
                    @endphp
                    <div class="flex-1 h-full flex flex-col items-center gap-2" title="{{ $dia['label'] }}: {{ $dinero($dia['total']) }}">
                        <div class="w-full flex-1 min-h-0 flex items-end">
                            <div class="w-full rounded-t-lg {{ $dia['hoy'] ? 'bg-indigo-600' : 'bg-indigo-200 dark:bg-indigo-500/30' }}"
                                 style="height: {{ $alto }}%"></div>
                        </div>
                        <span class="text-xs {{ $dia['hoy'] ? 'font-bold text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">{{ $dia['label'] }}</span>
                    </div>
                @endforeach
            </div>

            @if($totalSemana <= 0)
                <p class="mt-4 text-center text-xs text-gray-400 dark:text-gray-500">Todavía no hay ventas en estos 7 días.</p>
            @endif
        </div>

        {{-- Últimas ventas --}}
        <div class="bg-white dark:bg-[#15161A] border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between gap-4">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Últimas ventas</h3>
                @if($puede['historial'])
                    <a href="{{ route('ventas.historial') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Ver historial</a>
                @endif
            </div>

            @forelse($ultimasVentas as $venta)
                <div class="flex items-center justify-between gap-3 py-3 {{ ! $loop->last ? 'border-b border-gray-100 dark:border-white/5' : '' }} {{ $loop->first ? 'mt-2' : '' }}">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-900 dark:text-white truncate">#{{ $venta->folio }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 truncate">
                            {{ $venta->cliente?->nombre ?? ($venta->nombre_cliente_temporal ?: 'Mostrador') }}
                            · {{ \Carbon\Carbon::parse($venta->fecha)->format('d/m H:i') }}
                        </p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-sm font-bold text-gray-900 dark:text-white tabular-nums">{{ $dinero($venta->total) }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ $venta->metodo_pago }}</p>
                    </div>
                </div>
            @empty
                <div class="py-10 text-center">
                    <span class="mx-auto w-11 h-11 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-400 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </span>
                    <p class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Todavía no hay ventas</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Cuando se cobre la primera, aparecerá aquí.</p>
                </div>
            @endforelse
        </div>
    </div>
    @endif
</div>
@endsection
