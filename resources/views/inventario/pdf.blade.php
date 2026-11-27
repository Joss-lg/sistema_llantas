<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { margin: 0; padding: 18px; color: #1a1a1a; font-size: 10px; }
        .header { text-align: center; border-bottom: 2px solid #0090C0; padding-bottom: 8px; margin-bottom: 12px; }
        .header h1 { margin: 0; font-size: 16px; color: #00406F; }
        .header p { margin: 2px 0 0; font-size: 10px; color: #5B6B77; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #00406F; color: #fff; text-align: left; padding: 5px 6px; font-size: 9px; }
        td { padding: 4px 6px; border-bottom: 1px solid #C9D8E1; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bajo { color: #D32030; font-weight: bold; }
        .footer { margin-top: 12px; text-align: center; font-size: 8px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ mb_strtoupper(config('empresa.nombre')) }}</h1>
        <p>Reporte de Inventario - Generado el {{ $fecha }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:18%;">Marca</th>
                <th style="width:18%;">Medida</th>
                <th style="width:34%;">Descripción</th>
                <th style="width:12%;" class="text-center">Stock</th>
                <th style="width:18%;" class="text-right">Precio público</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productos as $p)
                <tr>
                    <td>{{ $p->marca }}</td>
                    <td>{{ $p->medida }}</td>
                    <td>{{ $p->descripcion ?? 'S/D' }}</td>
                    <td class="text-center {{ ($p->stock_cantidad ?? 0) < 5 ? 'bajo' : '' }}">{{ (int) ($p->stock_cantidad ?? 0) }}</td>
                    <td class="text-right">${{ number_format($p->precio_publico, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center" style="padding:20px;">Sin productos para mostrar.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p style="font-size:10px; color:#666; margin-top:8px;">Total de productos: <strong>{{ $productos->count() }}</strong></p>

    <div class="footer">Generado por {{ config('empresa.nombre') }} - {{ $fecha }}</div>
</body>
</html>