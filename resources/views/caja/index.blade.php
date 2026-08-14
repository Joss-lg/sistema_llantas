@extends('layouts.app')

@section('content')

{{-- Oculta visualmente todas las barras de desplazamiento y define la suite completa de animaciones custom --}}
<style>
    ::-webkit-scrollbar {
        display: none;
        width: 0;
        height: 0;
    }
    * {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Keyframes de animaciones de entrada y efectos */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(25px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes popIn {
        0% {
            opacity: 0;
            transform: scale(0.9) translateY(10px);
        }
        70% {
            transform: scale(1.02);
        }
        100% {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    @keyframes pulseGlow {
        0%, 100% {
            box-shadow: 0 0 15px rgba(129, 140, 248, 0.2);
        }
        50% {
            box-shadow: 0 0 25px rgba(129, 140, 248, 0.5);
        }
    }

    @keyframes softFloat {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-4px);
        }
    }

    /* Clases de animación */
    .animate-fade-in-up {
        animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .animate-pop-in {
        animation: popIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .animate-pulse-glow {
        animation: pulseGlow 3s infinite ease-in-out;
    }

    .animate-float {
        animation: softFloat 3s infinite ease-in-out;
    }

    /* Retardos en cascada (Stagger) */
    .delay-100 { animation-delay: 100ms; }
    .delay-150 { animation-delay: 150ms; }
    .delay-200 { animation-delay: 200ms; }
    .delay-250 { animation-delay: 250ms; }
    .delay-300 { animation-delay: 300ms; }
</style>

<div class="container mx-auto px-4 py-8">

    {{-- ========================================== --}}
    {{-- VISTA 1: ABRIR CAJA (CUANDO NO HAY TURNO)  --}}
    {{-- ========================================== --}}
    @if(is_null($corteActual))
        <div class="flex justify-center items-center min-h-[60vh]">
            <div class="w-full max-w-md bg-white dark:bg-[#15161A] rounded-2xl shadow-xl dark:shadow-black/40 border border-gray-100 dark:border-white/10 overflow-hidden transition-all duration-300 animate-pop-in hover:-translate-y-1.5 hover:shadow-2xl hover:shadow-indigo-500/10">
                
                {{-- Encabezado --}}
                <div class="bg-[#818CF8] text-white text-center py-6 relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full blur-xl animate-pulse"></div>
                    <h4 class="text-2xl font-bold mb-1 flex items-center justify-center">
                        <i class="fas fa-cash-register mr-3 animate-bounce"></i> Apertura de Caja
                    </h4>
                    <p class="text-[#EEF2FF] text-sm mt-1 animate-fade-in-up delay-100">Ingresa el efectivo inicial para comenzar tu turno</p>
                </div>

                {{-- Formulario --}}
                <div class="p-8">
                    <form action="{{ route('caja.abrir') }}" method="POST">
                        @csrf
                        <div class="mb-6 animate-fade-in-up delay-150">
                            <label for="saldo_inicial" class="block text-gray-700 dark:text-gray-200 font-bold mb-2 transition-colors">Fondo de Caja (Efectivo)</label>
                            <div class="flex items-stretch border border-gray-300 dark:border-gray-700 rounded-xl overflow-hidden focus-within:ring-4 focus-within:ring-[#818CF8]/30 focus-within:border-[#818CF8] transition-all duration-300 shadow-sm hover:border-[#818CF8]/60">
                                <span class="bg-gray-50 dark:bg-[#1F2026] px-5 py-3 font-bold text-gray-500 dark:text-gray-400 border-r border-gray-300 dark:border-gray-700 flex items-center transition-colors">
                                    $
                                </span>
                                <input type="number" step="0.01" min="0" class="w-full px-4 py-3 text-lg outline-none bg-transparent text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all" name="saldo_inicial" id="saldo_inicial" placeholder="Ej. 1000.00" required autofocus>
                            </div>
                        </div>
                        
                        <button type="submit" class="group w-full bg-[#818CF8] hover:bg-[#6366F1] text-white font-bold py-4 px-4 rounded-xl transition-all duration-300 shadow-md hover:shadow-indigo-500/30 hover:-translate-y-0.5 active:scale-[0.98] active:translate-y-0 text-lg flex items-center justify-center animate-fade-in-up delay-200">
                            <span>Abrir Caja e Ir a Vender</span> <i class="fas fa-arrow-right ml-2 group-hover:translate-x-2 transition-transform duration-300 ease-out"></i>
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
                <div class="bg-white dark:bg-[#15161A] shadow-sm hover:shadow-xl hover:shadow-indigo-500/5 border border-gray-100 dark:border-white/10 rounded-2xl h-full flex flex-col transition-all duration-300 animate-fade-in-up hover:-translate-y-1">
                    <div class="bg-[#818CF8] text-white py-4 px-6 rounded-t-2xl group">
                        <h5 class="text-lg font-bold flex items-center m-0">
                            <i class="fas fa-calculator mr-2 group-hover:rotate-12 group-hover:scale-110 transition-transform duration-300"></i> Resumen de Turno
                        </h5>
                    </div>
                    
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex justify-between items-center mb-3 p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors duration-200">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">ID Caja:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-100">#{{ $corteActual->id }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-3 p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors duration-200">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">Cajero:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-100">{{ Auth::user()->name }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-100 dark:border-white/10 p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors duration-200">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">Apertura:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-100">{{ \Carbon\Carbon::parse($corteActual->fecha_apertura)->format('d/m/Y H:i') }}</span>
                        </div>

                        <div class="flex justify-between items-center mb-3 p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors duration-200">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">Saldo Inicial:</span>
                            <span class="font-bold bg-gray-100 dark:bg-[#202228] px-2.5 py-1 rounded-lg text-gray-700 dark:text-gray-300 text-sm transition-transform hover:scale-105">{{ number_format($corteActual->saldo_inicial, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-3 p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors duration-200">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">+ Ventas (Efectivo):</span>
                            <span class="font-bold text-green-600 dark:text-green-400 transition-transform hover:scale-105">+${{ number_format($totalVentas, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-3 p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors duration-200">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">+ Anticipos (Efectivo):</span>
                            <span class="font-bold text-green-600 dark:text-green-400 transition-transform hover:scale-105">+${{ number_format($totalAnticipos, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100 dark:border-white/10 p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors duration-200">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">- Salidas/Gastos:</span>
                            <span class="font-bold text-red-600 dark:text-red-400 transition-transform hover:scale-105">-${{ number_format($totalGastos, 2) }}</span>
                        </div>

                        <div class="text-center mt-2 mb-8 transform hover:scale-110 transition-transform duration-300 cursor-default animate-float">
                            <span class="text-gray-400 dark:text-gray-500 font-bold text-xs tracking-wider uppercase block mb-1">SALDO ACTUAL ESTIMADO</span>
                            <h2 class="font-extrabold text-[#818CF8] text-4xl hover:text-[#6366F1] transition-colors">${{ number_format($saldoEstimado, 2) }}</h2>
                        </div>
                        
                        <div class="space-y-3 mt-auto">
                            <a href="{{ route('ventas.index') }}" class="group w-full flex items-center justify-center bg-[#818CF8] hover:bg-[#6366F1] text-white font-bold py-3 px-4 rounded-xl transition-all duration-300 shadow-sm hover:shadow-lg hover:shadow-indigo-500/25 hover:-translate-y-1 active:scale-[0.98]">
                                <i class="fas fa-shopping-cart mr-2 group-hover:scale-125 group-hover:-rotate-12 transition-transform duration-300"></i> Ir al Punto de Venta
                            </a>
                            <button class="group w-full flex items-center justify-center bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-4 rounded-xl transition-all duration-300 shadow-sm hover:shadow-lg hover:shadow-teal-500/25 hover:-translate-y-1 active:scale-[0.98]">
                                <i class="fas fa-file-excel mr-2 group-hover:scale-125 group-hover:rotate-12 transition-transform duration-300"></i> Exportar Reporte
                            </button>
                            <form action="{{ route('caja.cerrar') }}" method="POST" class="block w-full">
                                @csrf
                                <button type="submit" class="group w-full flex items-center justify-center bg-white dark:bg-[#15161A] border-2 border-red-100 dark:border-red-900/40 text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 hover:border-red-200 dark:hover:border-red-800 font-bold py-3 px-4 rounded-xl transition-all duration-300 hover:shadow-md hover:-translate-y-1 active:scale-[0.98]" onclick="return confirm('¿Estás seguro de que deseas cerrar la caja? Ya no podrás hacer más ventas en este turno.')">
                                    <i class="fas fa-lock mr-2 group-hover:scale-125 group-hover:rotate-12 transition-transform duration-300"></i> Cerrar Caja
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: TABLAS DE MOVIMIENTOS --}}
            <div class="md:col-span-8 lg:col-span-9 space-y-6">
                
                {{-- VENTAS DEL TURNO --}}
                <div class="bg-white dark:bg-[#15161A] shadow-sm hover:shadow-xl border border-gray-100 dark:border-white/10 rounded-2xl overflow-hidden transition-all duration-300 animate-fade-in-up delay-100 hover:-translate-y-0.5">
                    <div class="bg-[#818CF8] text-white py-4 px-6 flex justify-between items-center group">
                        <h5 class="text-lg font-bold flex items-center m-0">
                            <i class="fas fa-shopping-basket mr-2 group-hover:scale-125 group-hover:-rotate-12 transition-transform duration-300"></i> Ventas del Turno
                        </h5>
                        <span class="bg-white dark:bg-[#1F2026] text-[#818CF8] text-sm font-bold px-4 py-1.5 rounded-full shadow-sm transition-all duration-300 hover:scale-105 hover:shadow-md">
                            Total: ${{ number_format($totalVentas, 2) }}
                        </span>
                    </div>
                    <div class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-center whitespace-nowrap">
                                <thead class="bg-gray-50 dark:bg-[#1B1D22] border-b border-gray-100 dark:border-white/10 text-gray-500 dark:text-gray-400 text-xs uppercase font-bold tracking-wider">
                                    <tr>
                                        <th class="px-6 py-4">FECHA</th>
                                        <th class="px-6 py-4">TOTAL</th>
                                        <th class="px-6 py-4">MÉTODO</th>
                                        <th class="px-6 py-4">ACCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-white/10 text-sm">
                                    @forelse($ventasDelTurno as $venta)
                                    <tr class="hover:bg-indigo-50/50 dark:hover:bg-indigo-950/20 hover:scale-[1.002] transition-all duration-200">
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 font-bold text-gray-800 dark:text-white">${{ number_format($venta->total, 2) }}</td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-800/50 px-3 py-1 rounded-full text-xs font-bold transition-transform duration-200 hover:scale-110">
                                                <i class="fas fa-money-bill-wave mr-1.5 animate-pulse"></i> Efectivo
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('ventas.ticket', $venta->id) }}" target="_blank" class="inline-flex items-center justify-center w-9 h-9 rounded-full text-[#818CF8] hover:bg-[#818CF8] hover:text-white dark:hover:bg-[#818CF8] transition-all duration-300 hover:scale-125 hover:rotate-12 active:scale-90 shadow-none hover:shadow-md" title="Reimprimir Ticket">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500 animate-pulse">
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
                <div class="bg-white dark:bg-[#15161A] shadow-sm hover:shadow-xl border border-gray-100 dark:border-white/10 rounded-2xl overflow-hidden transition-all duration-300 animate-fade-in-up delay-200 hover:-translate-y-0.5">
                    <div class="bg-[#D97706] text-white py-4 px-6 flex justify-between items-center group">
                        <h5 class="text-lg font-bold flex items-center m-0">
                            <i class="fas fa-clock mr-2 group-hover:scale-125 group-hover:rotate-45 transition-transform duration-300"></i> Anticipos / Apartados
                        </h5>
                        <span class="bg-white dark:bg-[#1F2026] text-[#92400E] dark:text-[#FBBF24] text-sm font-bold px-4 py-1.5 rounded-full shadow-sm transition-all duration-300 hover:scale-105 hover:shadow-md">
                            Total: ${{ number_format($totalAnticipos, 2) }}
                        </span>
                    </div>
                    <div class="p-8 text-center text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        No hay anticipos registrados en este turno.
                    </div>
                </div>

                {{-- GASTOS Y SALIDAS --}}
                <div class="bg-white dark:bg-[#15161A] shadow-sm hover:shadow-xl border border-gray-100 dark:border-white/10 rounded-2xl overflow-hidden transition-all duration-300 animate-fade-in-up delay-300 hover:-translate-y-0.5">
                    <div class="bg-[#EF4444] text-white py-4 px-6 flex justify-between items-center group">
                        <h5 class="text-lg font-bold flex items-center m-0">
                            <i class="fas fa-chart-line mr-2 group-hover:scale-125 group-hover:-translate-y-1 transition-transform duration-300"></i> Gastos y Salidas
                        </h5>
                        <span class="bg-white dark:bg-[#1F2026] text-[#991B1B] dark:text-[#FCA5A5] text-sm font-bold px-4 py-1.5 rounded-full shadow-sm transition-all duration-300 hover:scale-105 hover:shadow-md">
                            Total: ${{ number_format($totalGastos, 2) }}
                        </span>
                    </div>
                    <div class="p-8 text-center text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        No hay gastos registrados en este turno.
                    </div>
                </div>

            </div>
        </div>
    @endif
</div>
@endsection