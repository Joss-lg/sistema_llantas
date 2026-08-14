@extends('layouts.app')

@section('header_title', 'Corte de Caja y Gastos')

@section('content')
{{--
    ========================================================================
    MOCKUP / VISTA PREVIA — Módulo de Corte de Caja
    Todos los datos de abajo son de EJEMPLO (hardcodeados) para que veas
    cómo queda. Cuando aprobemos el diseño, lo conectamos a las tablas reales.
    ========================================================================
--}}
@php
    // ===== DATOS DE EJEMPLO (se reemplazarán por datos reales del sistema) =====
    $ventaTotal       = 48750.00;   // Suma de todas las ventas del periodo
    $pagoEfectivo     = 21300.00;   // Ventas pagadas en efectivo
    $pagoTarjeta      = 18450.00;   // Ventas pagadas con tarjeta
    $pagoTransferencia= 6500.00;    // Ventas pagadas por transferencia
    $porPagar         = 2500.00;    // Ventas a crédito / pendientes de cobro
    $totalGastos      = 7850.00;    // Gastos manuales del periodo

    $efectivoEnCaja   = $pagoEfectivo - $totalGastos; // Lo que debería haber en caja
    $gananciaNeta     = $ventaTotal - $totalGastos;

    // Porcentajes para las barras
    $cobrado = $pagoEfectivo + $pagoTarjeta + $pagoTransferencia;
    $pctEfectivo = $cobrado > 0 ? round(($pagoEfectivo / $cobrado) * 100) : 0;
    $pctTarjeta  = $cobrado > 0 ? round(($pagoTarjeta / $cobrado) * 100) : 0;
    $pctTransfer = $cobrado > 0 ? round(($pagoTransferencia / $cobrado) * 100) : 0;

    // Gastos de ejemplo
    $gastos = [
        ['concepto' => 'Pago de renta del local', 'categoria' => 'Renta', 'monto' => 4500.00, 'hora' => '09:15'],
        ['concepto' => 'Recibo de luz CFE', 'categoria' => 'Servicios', 'monto' => 1850.00, 'hora' => '11:30'],
        ['concepto' => 'Compra de agua y café', 'categoria' => 'Insumos', 'monto' => 350.00, 'hora' => '13:00'],
        ['concepto' => 'Gasolina camioneta reparto', 'categoria' => 'Transporte', 'monto' => 800.00, 'hora' => '15:45'],
        ['concepto' => 'Material de limpieza', 'categoria' => 'Insumos', 'monto' => 350.00, 'hora' => '17:20'],
    ];
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    {{-- Aviso de mockup --}}
    <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl flex items-center gap-2 text-sm">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span><strong>Vista previa:</strong> los datos mostrados son de ejemplo. Cuando apruebes el diseño, lo conectamos a tus ventas y gastos reales.</span>
    </div>

    {{-- Encabezado + filtros de periodo --}}
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Corte de Caja</h2>
            <p class="text-sm text-gray-500 mt-1">Resumen de ventas, formas de pago y gastos.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2" x-data="{ periodo: 'hoy' }">
            <div class="flex bg-gray-100 rounded-lg p-1">
                <button @click="periodo = 'hoy'" :class="periodo === 'hoy' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500'" class="px-4 py-1.5 rounded-md text-sm font-semibold transition">Hoy</button>
                <button @click="periodo = 'semana'" :class="periodo === 'semana' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500'" class="px-4 py-1.5 rounded-md text-sm font-semibold transition">Semana</button>
                <button @click="periodo = 'mes'" :class="periodo === 'mes' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500'" class="px-4 py-1.5 rounded-md text-sm font-semibold transition">Mes</button>
            </div>
            <button onclick="openModal('modal-gasto')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#818CF8] text-white rounded-lg text-sm font-semibold hover:bg-[#6366F1] transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Registrar gasto
            </button>
        </div>
    </div>

    {{-- TARJETA PRINCIPAL: Venta total + Ganancia neta --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Venta total (grande, negra) --}}
        <div class="lg:col-span-2 bg-gradient-to-br from-[#0F0F0F] to-[#1f1f1f] rounded-2xl p-6 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 bg-[#818CF8]/10 rounded-full -mr-16 -mt-16"></div>
            <div class="relative">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Venta total del día</p>
                <p class="text-4xl font-bold mb-4">${{ number_format($ventaTotal, 2) }}</p>
                <div class="flex flex-wrap gap-6 text-sm">
                    <div>
                        <p class="text-gray-400 text-xs">Cobrado</p>
                        <p class="font-bold text-emerald-400">${{ number_format($cobrado, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Por pagar</p>
                        <p class="font-bold text-amber-400">${{ number_format($porPagar, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Gastos</p>
                        <p class="font-bold text-[#818CF8]">-${{ number_format($totalGastos, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ganancia neta --}}
        <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm flex flex-col justify-center">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Ganancia neta</p>
            <p class="text-3xl font-bold text-emerald-700 mb-2">${{ number_format($gananciaNeta, 2) }}</p>
            <p class="text-xs text-gray-500">Venta total menos gastos del periodo.</p>
            <div class="mt-3 pt-3 border-t border-gray-100">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Efectivo en caja</span>
                    <span class="font-bold text-gray-800">${{ number_format($efectivoEnCaja, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- DESGLOSE DE FORMAS DE PAGO --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
        <h3 class="text-base font-bold text-gray-900 mb-4">Desglose por forma de pago</h3>

        {{-- Barra visual de proporción --}}
        <div class="flex h-3 rounded-full overflow-hidden mb-6">
            <div class="bg-emerald-500" style="width: {{ $pctEfectivo }}%"></div>
            <div class="bg-blue-500" style="width: {{ $pctTarjeta }}%"></div>
            <div class="bg-purple-500" style="width: {{ $pctTransfer }}%"></div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Efectivo --}}
            <div class="border border-gray-100 rounded-xl p-4 hover:border-emerald-200 transition">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Efectivo</p>
                        <span class="text-[10px] font-bold text-emerald-600">{{ $pctEfectivo }}%</span>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">${{ number_format($pagoEfectivo, 2) }}</p>
            </div>

            {{-- Tarjeta --}}
            <div class="border border-gray-100 rounded-xl p-4 hover:border-blue-200 transition">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Tarjeta</p>
                        <span class="text-[10px] font-bold text-blue-600">{{ $pctTarjeta }}%</span>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">${{ number_format($pagoTarjeta, 2) }}</p>
            </div>

            {{-- Transferencia --}}
            <div class="border border-gray-100 rounded-xl p-4 hover:border-purple-200 transition">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-9 h-9 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Transferencia</p>
                        <span class="text-[10px] font-bold text-purple-600">{{ $pctTransfer }}%</span>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">${{ number_format($pagoTransferencia, 2) }}</p>
            </div>
        </div>
    </div>

    {{-- LISTA DE GASTOS + POR PAGAR --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Gastos (2 columnas) --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Gastos del día</h3>
                <span class="text-sm font-bold text-[#818CF8]">-${{ number_format($totalGastos, 2) }}</span>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($gastos as $g)
                    <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50/60 transition">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-lg bg-[#818CF8]/10 text-[#818CF8] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                            </span>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">{{ $g['concepto'] }}</p>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase">{{ $g['categoria'] }}</span>
                                    <span class="text-[10px] text-gray-300">•</span>
                                    <span class="text-[10px] text-gray-400">{{ $g['hora'] }}</span>
                                </div>
                            </div>
                        </div>
                        <span class="font-bold text-gray-700 text-sm">-${{ number_format($g['monto'], 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Por pagar (1 columna) --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Por pagar</h3>
                <p class="text-xs text-gray-500 mt-0.5">Ventas a crédito pendientes</p>
            </div>
            <div class="p-5">
                <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-center mb-4">
                    <p class="text-xs font-bold text-amber-600 uppercase tracking-wider mb-1">Total pendiente</p>
                    <p class="text-3xl font-bold text-amber-700">${{ number_format($porPagar, 2) }}</p>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm py-2 border-b border-gray-50">
                        <span class="text-gray-600">Juan Pérez</span>
                        <span class="font-bold text-amber-700">$1,500.00</span>
                    </div>
                    <div class="flex items-center justify-between text-sm py-2 border-b border-gray-50">
                        <span class="text-gray-600">Taller Hnos. López</span>
                        <span class="font-bold text-amber-700">$1,000.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: Registrar gasto (solo visual en el mockup) --}}
<div id="modal-gasto" class="fixed inset-0 bg-black/40 z-[100] hidden flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-900">Registrar Gasto</h3>
            <button onclick="closeModal('modal-gasto')" class="text-gray-400 text-xl">&times;</button>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Concepto</label>
                <input type="text" placeholder="Ej. Pago de luz, renta, gasolina..." class="w-full p-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Categoría</label>
                    <select class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                        <option>Renta</option>
                        <option>Servicios</option>
                        <option>Insumos</option>
                        <option>Transporte</option>
                        <option>Nómina</option>
                        <option>Otros</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Monto ($)</label>
                    <input type="number" step="0.01" placeholder="0.00" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Forma de pago</label>
                <select class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                    <option>Efectivo</option>
                    <option>Tarjeta</option>
                    <option>Transferencia</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeModal('modal-gasto')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancelar</button>
                <button type="button" class="px-4 py-2 bg-[#818CF8] hover:bg-[#6366F1] text-white rounded-lg text-sm transition">Guardar gasto</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
    function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
</script>
@endsection