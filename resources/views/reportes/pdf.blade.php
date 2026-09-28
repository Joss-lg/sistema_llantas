<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1f2937; }
        .header { border-bottom: 2px solid #818CF8; padding-bottom: 10px; margin-bottom: 18px; }
        .header h1 { font-size: 20px; margin: 0 0 4px 0; color: #111827; }
        .header p { margin: 2px 0; color: #6b7280; font-size: 10px; }
        .metricas { width: 100%; margin-bottom: 20px; }
        .metricas td { width: 33.33%; padding: 10px; border: 1px solid #e5e7eb; border-radius: 4px; }
        .metrica-label { font-size: 9px; text-transform: uppercase; color: #9ca3af; font-weight: bold; }
        .metrica-valor { font-size: 18px; font-weight: bold; color: #111827; margin-top: 4px; }
        h2 { font-size: 13px; color: #111827; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; margin-top: 24px; margin-bottom: 10px; }
        table.datos { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.datos th { background-color: #f9fafb; text-align: left; padding: 6px 8px; font-size: 9px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        table.datos td { padding: 6px 8px; border-bottom: 1px solid #f3f4f6; font-size: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .sin-datos { color: #9ca3af; font-style: italic; padding: 10px 0; }
        .footer { margin-top: 30px; font-size: 8px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Reporte de Ventas y Estadísticas</h1>
        <p>Periodo: {{ ucfirst($periodo) }} — Del {{ $fechaInicio->format('d/m/Y') }} al {{ $fechaFin->format('d/m/Y') }}</p>
        <p>Sucursal: {{ $sucursalFiltro ? ($sucursales->firstWhere('id', $sucursalFiltro)->nombre ?? 'N/A') : 'Todas las sucursales' }}</p>
        <p>Generado el {{ $fechaGeneracion }}</p>
    </div>

    <table class="metricas">
        <tr>
            <td>
                <div class="metrica-label">Ingresos Totales</div>
                <div class="metrica-valor">${{ number_format($ingresosTotales, 2) }}</div>
            </td>
            <td>
                <div class="metrica-label">Ventas Realizadas</div>
                <div class="metrica-valor">{{ $ventasRealizadas }}</div>
            </td>
            <td>
                <div class="metrica-label">Nuevos Clientes</div>
                <div class="metrica-valor">{{ $clientesNuevos }}</div>
            </td>
        </tr>
    </table>

    <h2>Ventas por Día</h2>
    @if($ventasPorDia->isEmpty())
        <p class="sin-datos">Sin ventas registradas en este periodo.</p>
    @else
        <table class="datos">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th class="text-right">Total Vendido</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ventasPorDia as $dia)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($dia->dia)->format('d/m/Y') }}</td>
                        <td class="text-right">${{ number_format($dia->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Ventas por Sucursal</h2>
    @if($ventasPorSucursal->isEmpty())
        <p class="sin-datos">Sin ventas registradas en este periodo.</p>
    @else
        <table class="datos">
            <thead>
                <tr>
                    <th>Sucursal</th>
                    <th class="text-right">Total Vendido</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ventasPorSucursal as $suc)
                    <tr>
                        <td>{{ $suc->sucursal->nombre ?? 'N/A' }}</td>
                        <td class="text-right">${{ number_format($suc->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Productos más Vendidos</h2>
    @if($productosMasVendidos->isEmpty())
        <p class="sin-datos">Aún no hay productos vendidos en este periodo.</p>
    @else
        <table class="datos">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-center">Unidades</th>
                    <th class="text-right">Ingresos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productosMasVendidos as $producto)
                    <tr>
                        <td>{{ $producto->nombre_producto }}</td>
                        <td class="text-center">{{ (int) $producto->total_cantidad }}</td>
                        <td class="text-right">${{ number_format($producto->total_ingresos, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Reporte generado automáticamente por el Sistema de Llantas — {{ $fechaGeneracion }}
    </div>

</body>
</html>