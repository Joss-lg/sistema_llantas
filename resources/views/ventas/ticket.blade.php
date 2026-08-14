<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $venta->folio }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Narrow:wght@400;600;700&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: #e5e5e5;
            font-family: 'Archivo Narrow', Arial, sans-serif;
            display: flex;
            justify-content: center;
            padding: 24px 12px;
        }

        .ticket {
            background: #fff;
            width: 340px;
            padding: 22px 20px 26px;
            box-shadow: 0 4px 18px rgba(0,0,0,.18);
            color: #000;
        }

        /* ── Encabezado ── */
        .logo-wrap {
            background: #000;
            border-radius: 6px;
            padding: 8px 14px;
            width: fit-content;
            margin: 0 auto 10px;
        }
        .logo-wrap img { display: block; height: 52px; width: auto; }

        .brand {
            font-family: 'Oswald', 'Archivo Narrow', sans-serif;
            font-size: 26px;
            font-weight: 700;
            text-align: center;
            letter-spacing: 1px;
            line-height: 1.05;
        }
        .brand-sub {
            font-family: 'Oswald', sans-serif;
            font-size: 15px;
            font-weight: 600;
            text-align: center;
            letter-spacing: 4px;
            margin-top: 2px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 3px 0;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
            padding-left: 10px; padding-right: 10px;
        }
        .direccion {
            text-align: center;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .5px;
            margin-top: 8px;
            text-transform: uppercase;
            line-height: 1.5;
        }

        /* ── Separador punteado ── */
        .dash {
            border: none;
            border-top: 2px dashed #000;
            margin: 12px 0;
        }

        /* ── Info del ticket ── */
        .ticket-info {
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            font-size: 12.5px;
            margin: 3px 0;
        }
        .row .lbl { font-weight: 700; letter-spacing: .5px; }
        .row .val { font-weight: 600; text-align: right; }

        /* ── Tabla de productos ── */
        table { width: 100%; border-collapse: collapse; }
        thead th {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .5px;
            text-align: left;
            padding-bottom: 6px;
        }
        thead th:last-child { text-align: right; }
        tbody td {
            font-size: 12.5px;
            padding: 3px 0;
            vertical-align: top;
        }
        tbody .cant { font-weight: 700; white-space: nowrap; padding-right: 6px; }
        tbody .desc { font-weight: 600; line-height: 1.25; padding-right: 6px; }
        tbody .precio { font-weight: 700; text-align: right; white-space: nowrap; }

        /* ── Totales ── */
        .tot-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            font-weight: 700;
            margin: 4px 0;
        }
        .total-bar {
            background: #000;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 9px 12px;
            margin-top: 10px;
            font-family: 'Oswald', sans-serif;
        }
        .total-bar .t-lbl { font-size: 16px; font-weight: 700; letter-spacing: 1px; }
        .total-bar .t-val { font-size: 19px; font-weight: 700; }

        /* ── Caja de pago ── */
        .box {
            border: 2px solid #000;
            padding: 10px 12px;
            margin-top: 14px;
        }
        .box-title {
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.5px;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .box .row { font-size: 12.5px; }

        /* ── QR ── */
        .qr-zone { text-align: center; margin-top: 16px; }
        #qrcode {
            display: inline-block;
            padding: 8px;
            border: 2px solid #000;
        }
        #qrcode img, #qrcode canvas { display: block; }
        .qr-hint {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .5px;
            margin-top: 8px;
            text-transform: uppercase;
        }

        /* ── Pie ── */
        .footer { text-align: center; margin-top: 16px; }
        .footer .gracias {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .footer .links {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .5px;
            margin-top: 8px;
            text-transform: uppercase;
            text-decoration: underline;
            line-height: 1.8;
        }
        .footer .version {
            font-size: 9.5px;
            color: #777;
            margin-top: 14px;
            letter-spacing: 2px;
        }

        /* ── Botones (no se imprimen) ── */
        .acciones {
            position: fixed;
            bottom: 18px;
            right: 18px;
            display: flex;
            gap: 8px;
        }
        .acciones button {
            font-family: 'Archivo Narrow', sans-serif;
            font-weight: 700;
            font-size: 13px;
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .btn-imprimir { background: #0F0F0F; color: #fff; }
        .btn-cerrar { background: #D32030; color: #fff; }

        @media print {
            body { background: #fff; padding: 0; }
            .ticket { box-shadow: none; width: 100%; }
            .acciones { display: none; }
        }
    </style>
</head>
<body>

    <div class="ticket">

        {{-- Logo --}}
        <div class="logo-wrap">
            <img src="{{ asset('img/logo-llantas.png') }}" alt="Logo">
        </div>

        {{-- Nombre del negocio --}}
        <div class="brand">LLANTAS ECONÓMICAS</div>
        <div class="brand-sub">CHALCO</div>

        <div class="direccion">
            Sucursal {{ $venta->sucursal_id }}<br>
            Tel: 55-0000-0000
        </div>

        <hr class="dash">

        {{-- Info del ticket --}}
        <div class="ticket-info">
            TICKET #{{ $venta->folio }} &nbsp;|&nbsp; {{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y') }}<br>
            {{ \Carbon\Carbon::parse($venta->fecha)->format('H:i') }}
        </div>

        <hr class="dash">

        {{-- Cliente --}}
        <div class="row">
            <span class="lbl">CLIENTE:</span>
            <span class="val">{{ $venta->cliente->nombre ?? 'PÚBLICO GENERAL' }}</span>
        </div>
        <div class="row">
            <span class="lbl">ATENDIÓ:</span>
            <span class="val">Cajero #{{ $venta->usuario_id ?? $venta->user_id }}</span>
        </div>

        <hr class="dash">

        {{-- Productos --}}
        <table>
            <thead>
                <tr>
                    <th>CANT</th>
                    <th>DESCRIPCIÓN</th>
                    <th>PRECIO</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->detalles as $d)
                    <tr>
                        <td class="cant">{{ (int) $d->cantidad }}x</td>
                        <td class="desc">{{ $d->nombre_producto }}</td>
                        <td class="precio">${{ number_format($d->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <hr class="dash">

        {{-- Totales --}}
        @php
            $descuentoTotal = $venta->detalles->sum('descuento');
            $subtotal = $venta->total + $descuentoTotal;
        @endphp

        <div class="tot-row">
            <span>SUBTOTAL:</span>
            <span>${{ number_format($subtotal, 2) }}</span>
        </div>
        @if($descuentoTotal > 0)
            <div class="tot-row">
                <span>DESCUENTO:</span>
                <span>-${{ number_format($descuentoTotal, 2) }}</span>
            </div>
        @endif

        <div class="total-bar">
            <span class="t-lbl">TOTAL:</span>
            <span class="t-val">${{ number_format($venta->total, 2) }}</span>
        </div>

        {{-- Caja de pago --}}
        <div class="box">
            <div class="box-title">PAGO</div>
            <div class="row">
                <span class="lbl">RECIBIDO:</span>
                <span class="val">${{ number_format((float)($venta->pago_con ?? 0), 2) }}</span>
            </div>
            <div class="row">
                <span class="lbl">CAMBIO:</span>
                <span class="val">${{ number_format((float)($venta->cambio ?? 0), 2) }}</span>
            </div>
        </div>

        {{-- QR de facturación por WhatsApp --}}
        <div class="qr-zone">
            <div id="qrcode"></div>
            <div class="qr-hint">Escanea para facturar por WhatsApp</div>
        </div>

        {{-- Pie --}}
        <div class="footer">
            <div class="gracias">Gracias por su preferencia</div>
            <div class="links">
                Facturación en línea<br>
                Quejas y sugerencias
            </div>
            <div class="version">L-E-C v1.0</div>
        </div>
    </div>

    {{-- Botones flotantes (no salen al imprimir) --}}
    <div class="acciones">
        <button class="btn-imprimir" onclick="window.print()">Imprimir</button>
        <button class="btn-cerrar" onclick="window.close()">Cerrar</button>
    </div>

    <script>
        window.onload = function () {
            var folio = "{{ $venta->folio }}";
            var total = "{{ number_format($venta->total, 2) }}";

            var mensaje =
                "Hola, quiero facturar mi compra:\n" +
                "Ticket: #" + folio + "\n" +
                "Total: $" + total + "\n\n" +
                "Mis datos fiscales son:\n" +
                "RFC: \n" +
                "Razón social: \n" +
                "Uso de CFDI: \n" +
                "Código postal: ";

            var url = "https://wa.me/525535690077?text=" + encodeURIComponent(mensaje);

            new QRCode(document.getElementById("qrcode"), {
                text: url,
                width: 130,
                height: 130,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M
            });
        };
    </script>
</body>
</html>