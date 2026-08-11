@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">

    {{-- ========================================== --}}
    {{-- VISTA 1: ABRIR CAJA (CUANDO NO HAY TURNO)  --}}
    {{-- ========================================== --}}
    @if(is_null($corteActual))
        <div class="flex justify-center items-center min-h-[60vh]">
            <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                
                {{-- Encabezado --}}
                <div class="bg-blue-600 text-white text-center py-6">
                    <h4 class="text-2xl font-bold mb-1 flex items-center justify-center">
                        <i class="fas fa-cash-register mr-3"></i> Apertura de Caja
                    </h4>
                    <p class="text-blue-100 text-sm mt-1">Ingresa el efectivo inicial para comenzar tu turno</p>
                </div>

                {{-- Formulario --}}
                <div class="p-8">
                    <form action="{{ route('caja.abrir') }}" method="POST">
                        @csrf
                        <div class="mb-6">
                            <label for="saldo_inicial" class="block text-gray-700 font-bold mb-2">Fondo de Caja (Efectivo)</label>
                            <div class="flex items-stretch border border-gray-300 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500 transition-all shadow-sm">
                                <span class="bg-gray-50 px-5 py-3 font-bold text-gray-500 border-r border-gray-300 flex items-center">
                                    $
                                </span>
                                <input type="number" step="0.01" min="0" class="w-full px-4 py-3 text-lg outline-none" name="saldo_inicial" id="saldo_inicial" placeholder="Ej. 1000.00" required autofocus>
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-4 rounded-xl transition duration-200 shadow-md text-lg flex items-center justify-center">
                            Abrir Caja e Ir a Vender <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    {{-- ========================================== --}}
    {{-- VISTA 2: RESUMEN DE TURNO (CAJA ABIERTA)   --}}
    {{-- ========================================== --}}
    @else
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            
            {{-- COLUMNA IZQUIERDA: RESUMEN FINANCIERO --}}
            <div class="md:col-span-4 lg:col-span-3">
                <div class="bg-white shadow-sm border border-gray-100 rounded-2xl h-full flex flex-col">
                    <div class="bg-blue-500 text-white py-4 px-6 rounded-t-2xl">
                        <h5 class="text-lg font-bold flex items-center m-0">
                            <i class="fas fa-calculator mr-2"></i> Resumen de Turno
                        </h5>
                    </div>
                    
                    <div class="p-6 flex-1">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-gray-500 text-sm">ID Caja:</span>
                            <span class="font-bold text-gray-800">#{{ $corteActual->id }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-gray-500 text-sm">Cajero:</span>
                            <span class="font-bold text-gray-800">{{ Auth::user()->name }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-100">
                            <span class="text-gray-500 text-sm">Apertura:</span>
                            <span class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($corteActual->fecha_apertura)->format('d/m/Y H:i') }}</span>
                        </div>

                        <div class="flex justify-between items-center mb-3">
                            <span class="text-gray-500 text-sm">Saldo Inicial:</span>
                            <span class="font-bold bg-gray-100 px-2 py-1 rounded text-gray-700">${{ number_format($corteActual->saldo_inicial, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-gray-500 text-sm">+ Ventas (Efectivo):</span>
                            <span class="font-bold text-green-500">+${{ number_format($totalVentas, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-gray-500 text-sm">+ Anticipos (Efectivo):</span>
                            <span class="font-bold text-green-500">+${{ number_format($totalAnticipos, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
                            <span class="text-gray-500 text-sm">- Salidas/Gastos:</span>
                            <span class="font-bold text-red-500">-${{ number_format($totalGastos, 2) }}</span>
                        </div>

                        <div class="text-center mt-2 mb-8">
                            <span class="text-gray-400 font-bold text-xs tracking-wider uppercase block mb-1">SALDO ACTUAL ESTIMADO</span>
                            <h2 class="font-extrabold text-blue-600 text-4xl">${{ number_format($saldoEstimado, 2) }}</h2>
                        </div>
                        
                        <div class="space-y-3 mt-auto">
                            <a href="{{ route('ventas.index') }}" class="w-full flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl transition shadow-sm">
                                <i class="fas fa-shopping-cart mr-2"></i> Ir al Punto de Venta
                            </a>
                            <button class="w-full flex items-center justify-center bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-4 rounded-xl transition shadow-sm">
                                <i class="fas fa-file-excel mr-2"></i> Exportar Reporte
                            </button>
                            <form action="{{ route('caja.cerrar') }}" method="POST" class="block w-full">
                                @csrf
                                <button type="submit" class="w-full flex items-center justify-center bg-white border-2 border-red-100 text-red-500 hover:bg-red-50 hover:border-red-200 font-bold py-3 px-4 rounded-xl transition" onclick="return confirm('¿Estás seguro de que deseas cerrar la caja? Ya no podrás hacer más ventas en este turno.')">
                                    <i class="fas fa-lock mr-2"></i> Cerrar Caja
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: TABLAS DE MOVIMIENTOS --}}
            <div class="md:col-span-8 lg:col-span-9 space-y-6">
                
                {{-- VENTAS DEL TURNO --}}
                <div class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
                    <div class="bg-sky-500 text-white py-4 px-6 flex justify-between items-center">
                        <h5 class="text-lg font-bold flex items-center m-0">
                            <i class="fas fa-shopping-basket mr-2"></i> Ventas del Turno
                        </h5>
                        <span class="bg-white text-sky-800 text-sm font-bold px-4 py-1.5 rounded-full shadow-sm">
                            Total: ${{ number_format($totalVentas, 2) }}
                        </span>
                    </div>
                    <div class="p-0">
                        <div class="overflow-y-auto max-h-[350px]">
                            <table class="w-full text-center whitespace-nowrap">
                                <thead class="bg-gray-50 sticky top-0 border-b border-gray-100 text-gray-500 text-xs uppercase font-bold tracking-wider">
                                    <tr>
                                        <th class="px-6 py-4">FECHA</th>
                                        <th class="px-6 py-4">TOTAL</th>
                                        <th class="px-6 py-4">MÉTODO</th>
                                        <th class="px-6 py-4">ACCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 text-sm">
                                    @forelse($ventasDelTurno as $venta)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 text-gray-600">{{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 font-bold text-gray-800">${{ number_format($venta->total, 2) }}</td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center bg-green-50 text-green-600 border border-green-200 px-3 py-1 rounded-full text-xs font-bold">
                                                <i class="fas fa-money-bill-wave mr-1.5"></i> Efectivo
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('ventas.ticket', $venta->id) }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sky-500 hover:bg-sky-100 transition" title="Reimprimir Ticket">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                            No hay ventas registradas en este turno aún.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ANTICIPOS / APARTADOS --}}
                <div class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
                    <div class="bg-amber-500 text-white py-4 px-6 flex justify-between items-center">
                        <h5 class="text-lg font-bold flex items-center m-0">
                            <i class="fas fa-clock mr-2"></i> Anticipos / Apartados
                        </h5>
                        <span class="bg-white text-amber-800 text-sm font-bold px-4 py-1.5 rounded-full shadow-sm">
                            Total: ${{ number_format($totalAnticipos, 2) }}
                        </span>
                    </div>
                    <div class="p-8 text-center text-gray-400">
                        No hay anticipos registrados en este turno.
                    </div>
                </div>

                {{-- GASTOS Y SALIDAS --}}
                <div class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
                    <div class="bg-red-500 text-white py-4 px-6 flex justify-between items-center">
                        <h5 class="text-lg font-bold flex items-center m-0">
                            <i class="fas fa-chart-line mr-2"></i> Gastos y Salidas
                        </h5>
                        <span class="bg-white text-red-800 text-sm font-bold px-4 py-1.5 rounded-full shadow-sm">
                            Total: ${{ number_format($totalGastos, 2) }}
                        </span>
                    </div>
                    <div class="p-8 text-center text-gray-400">
                        No hay gastos registrados en este turno.
                    </div>
                </div>

            </div>
        </div>
    @endif
</div>
@endsection