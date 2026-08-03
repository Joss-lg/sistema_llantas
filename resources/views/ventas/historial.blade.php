@extends('layouts.app')

@section('header_title', 'Historial de Ventas')

@section('content')

{{-- ─────────────────────────────────────────────────────────
     Nota: idealmente estas fuentes y este <style> viven en
     resources/views/layouts/app.blade.php (dentro de <head>).
     Se dejan aquí para que la vista funcione de forma autónoma.
───────────────────────────────────────────────────────────── --}}
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Archivo+Narrow:wght@400;600;700&display=swap" rel="stylesheet">

<style>
    .font-brand { font-family: 'Oswald', 'Archivo Narrow', sans-serif; }

    /* ── Franja de "banda de rodadura" — el elemento distintivo de la llantera ── */
    .tread {
        height: 10px;
        border-radius: 999px;
        background: repeating-linear-gradient(
            -45deg,
            #16181d 0px, #16181d 10px,
            #f59e0b 10px, #f59e0b 20px
        );
        background-size: 200% 100%;
        animation: treadShift 14s linear infinite;
        opacity: .9;
    }
    @keyframes treadShift {
        from { background-position: 0 0; }
        to   { background-position: 200px 0; }
    }

    /* ── Entrada escalonada ── */
    .fade-up {
        opacity: 0;
        transform: translateY(14px);
        animation: fadeUp .55s cubic-bezier(.22,.61,.36,1) forwards;
    }
    @keyframes fadeUp {
        to { opacity: 1; transform: translateY(0); }
    }

    /* ── Fila de tabla: barra de acento que entra por la izquierda ── */
    .row-hover { position: relative; transition: transform .18s ease, box-shadow .18s ease; }
    .row-hover::before {
        content: '';
        position: absolute; left: 0; top: 0; bottom: 0;
        width: 3px; background: #f59e0b;
        transform: scaleY(0); transform-origin: center;
        transition: transform .18s ease;
    }
    .row-hover:hover { transform: translateY(-1px); box-shadow: 0 6px 18px -8px rgba(0,0,0,.18); }
    .row-hover:hover::before { transform: scaleY(1); }

    /* ── Punto pulsante de "requiere factura" ── */
    .pulse-dot { position: relative; }
    .pulse-dot::before {
        content: ''; position: absolute; inset: 0; border-radius: 999px;
        background: currentColor; opacity: .5;
        animation: pulseRing 1.8s ease-out infinite;
    }
    @keyframes pulseRing {
        0% { transform: scale(1); opacity: .5; }
        100% { transform: scale(2.4); opacity: 0; }
    }

    /* ── Botón de reimprimir ── */
    .btn-print svg { transition: transform .35s ease; }
    .btn-print:hover svg { transform: rotate(-14deg) scale(1.1); }

    /* ── Tarjetas de estadística: brillo ambiental sutil ── */
    .stat-card { position: relative; overflow: hidden; }
    .stat-card::after {
        content: '';
        position: absolute; top: -60%; left: -20%;
        width: 60%; height: 220%;
        background: linear-gradient(120deg, transparent, rgba(255,255,255,.35), transparent);
        transform: rotate(20deg);
        animation: sheen 5s ease-in-out infinite;
    }
    @keyframes sheen {
        0%, 20% { transform: translateX(-120%) rotate(20deg); }
        50%, 100% { transform: translateX(220%) rotate(20deg); }
    }

    @media (prefers-reduced-motion: reduce) {
        .tread, .fade-up, .pulse-dot::before, .stat-card::after { animation: none !important; }
        .fade-up { opacity: 1; transform: none; }
    }
</style>

<div class="max-w-7xl mx-auto space-y-6">

    {{-- ── Encabezado temático ── --}}
    <div class="fade-up flex items-center justify-between flex-wrap gap-3">
        <div>
            <p class="font-brand text-[11px] font-bold tracking-[3px] text-amber-600 uppercase">Llantas Económicas · Chalco</p>
            <h1 class="font-brand text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Historial de Ventas</h1>
        </div>
        <div class="hidden sm:block w-40">
            <div class="tread"></div>
        </div>
    </div>

    {{-- ── Estadísticas rápidas ── --}}
    @php
        $sumaPagina = $ventas->sum('total');
        $piezasPagina = $ventas->sum(fn($v) => $v->detalles->sum('cantidad'));
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="fade-up stat-card bg-gray-900 rounded-2xl p-5 text-white shadow-sm" style="animation-delay:.05s">
            <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400">Ventas encontradas</p>
            <p class="font-brand text-2xl font-bold mt-1">{{ $ventas->total() }}</p>
        </div>
        <div class="fade-up stat-card bg-white rounded-2xl p-5 border border-gray-200 shadow-sm" style="animation-delay:.1s">
            <p class="text-[11px] font-bold uppercase tracking-widest text-gray-500">En esta página</p>
            <p class="font-brand text-2xl font-bold mt-1 text-gray-900">{{ $ventas->count() }}</p>
        </div>
        <div class="fade-up stat-card bg-white rounded-2xl p-5 border border-gray-200 shadow-sm" style="animation-delay:.15s">
            <p class="text-[11px] font-bold uppercase tracking-widest text-gray-500">Piezas vendidas (pág.)</p>
            <p class="font-brand text-2xl font-bold mt-1 text-gray-900">{{ $piezasPagina }}</p>
        </div>
        <div class="fade-up stat-card bg-emerald-600 rounded-2xl p-5 text-white shadow-sm" style="animation-delay:.2s">
            <p class="text-[11px] font-bold uppercase tracking-widest text-emerald-100">Total (pág.)</p>
            <p class="font-brand text-2xl font-bold mt-1">${{ number_format($sumaPagina, 2) }}</p>
        </div>
    </div>

    {{-- ── Filtros ── --}}
    <div class="fade-up bg-white rounded-2xl shadow-sm border border-gray-200 p-5 sm:p-6" style="animation-delay:.25s">
        <div class="flex items-center gap-2 mb-5">
            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            <p class="font-brand text-xs font-bold uppercase tracking-widest text-gray-500">Filtrar resultados</p>
        </div>

        <form method="GET" action="{{ route('ventas.historial') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Folio del Ticket</label>
                <input type="text" name="folio" value="{{ request('folio') }}" placeholder="Ej. VNT-2026..."
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 focus:bg-white hover:border-gray-300">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}"
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 focus:bg-white hover:border-gray-300">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Fecha Fin</label>
                <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}"
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 focus:bg-white hover:border-gray-300">
            </div>

            @if($esAdmin)
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Sucursal</label>
                <select name="sucursal_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 focus:bg-white hover:border-gray-300">
                    <option value="">Todas las sucursales</option>
                    @foreach($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}" {{ request('sucursal_id') == $sucursal->id ? 'selected' : '' }}>
                            {{ $sucursal->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="flex gap-2 lg:col-span-4 justify-end mt-2">
                <a href="{{ route('ventas.historial') }}" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-600 font-bold text-sm rounded-xl transition-all duration-200 hover:bg-gray-50 hover:border-gray-300">
                    Limpiar
                </a>
                <button type="submit" class="group inline-flex items-center gap-2 px-6 py-2.5 bg-gray-900 text-white font-bold text-sm rounded-xl shadow-sm transition-all duration-200 hover:bg-black hover:shadow-lg hover:shadow-amber-500/20 active:scale-95">
                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                    Buscar Ventas
                </button>
            </div>
        </form>
    </div>

    {{-- ── Tabla de resultados ── --}}
    <div class="fade-up bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden" style="animation-delay:.3s">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-900 border-b-2 border-amber-500">
                        <th class="p-4 text-xs font-black text-gray-300 uppercase tracking-wider">Folio / Fecha</th>
                        <th class="p-4 text-xs font-black text-gray-300 uppercase tracking-wider">Cliente</th>
                        <th class="p-4 text-xs font-black text-gray-300 uppercase tracking-wider">Sucursal / Cajero</th>
                        <th class="p-4 text-xs font-black text-gray-300 uppercase tracking-wider">Artículos</th>
                        <th class="p-4 text-right text-xs font-black text-gray-300 uppercase tracking-wider">Total</th>
                        <th class="p-4 text-center text-xs font-black text-gray-300 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($ventas as $venta)
                    <tr class="row-hover fade-up hover:bg-amber-50/40" style="animation-delay: {{ 0.35 + ($loop->index * 0.04) }}s">
                        <td class="p-4">
                            <div class="font-bold text-gray-900">{{ $venta->folio }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($venta->fecha)->format('d M, Y - H:i') }}</div>
                        </td>
                        <td class="p-4">
                            <div class="text-sm text-gray-800 font-medium">{{ $venta->cliente ?: 'Público General' }}</div>
                            @if($venta->requiere_factura)
                                <span class="inline-flex items-center gap-1.5 mt-1 text-[10px] bg-blue-100 text-blue-700 font-bold px-2 py-0.5 rounded uppercase tracking-wider">
                                    <span class="pulse-dot w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                    Req. Factura
                                </span>
                            @endif
                        </td>
                        <td class="p-4">
                            <div class="text-sm font-medium text-gray-800">Sucursal {{ $venta->sucursal_id }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">Cajero ID: {{ $venta->usuario_id }}</div>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center gap-1.5">
                                <span class="text-sm font-bold text-gray-700">{{ $venta->detalles->sum('cantidad') }}</span>
                                <span class="text-xs text-gray-400">pzas</span>
                            </div>
                        </td>
                        <td class="p-4 text-right">
                            <div class="font-black text-emerald-600">${{ number_format($venta->total, 2) }}</div>
                        </td>
                        <td class="p-4 text-center">
                            <button onclick="window.open('{{ route('ventas.ticket', $venta->id) }}', 'Ticket', 'width=400,height=600')"
                                class="btn-print inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 text-gray-600 transition-all duration-200 hover:bg-amber-500 hover:text-white hover:shadow-md hover:shadow-amber-500/30 active:scale-90" title="Reimprimir Ticket">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-14 text-center">
                            <div class="fade-up inline-flex items-center justify-center w-14 h-14 rounded-full bg-amber-50 mb-4 ring-4 ring-amber-100/60">
                                <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <p class="font-brand text-sm font-bold text-gray-700 uppercase tracking-wide">Sin resultados</p>
                            <p class="text-gray-500 text-sm mt-1">No se encontraron ventas con los filtros actuales.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ventas->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50/50">
            {{ $ventas->links() }}
        </div>
        @endif
    </div>
</div>
@endsection