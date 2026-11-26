<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $venta->folio }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Narrow:wght@400;600;700&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
    @if(config('empresa.whatsapp'))
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    @endif
    <style>
        :root {
            --azul-oscuro: #00406F;
            --azul: #0090C0;
            --azul-claro: #EAF5FA;
            --gris: #5B6B77;
            --linea: #C9D8E1;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* El color se imprime igual que en pantalla */
        .ticket, .ticket * {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            background: #e5e5e5;
            font-family: 'Archivo Narrow', Arial, sans-serif;
            display: flex;
            justify-content: center;
            padding: 24px 12px;
        }

        .ticket {
            background: #fff;
            width: 360px;
            box-shadow: 0 4px 18px rgba(0,0,0,.18);
            color: #111;
            overflow: hidden;
        }

        /* Franja de color arriba */
        .franja {
            height: 8px;
            background: linear-gradient(90deg, var(--azul-oscuro), var(--azul));
        }

        .contenido { padding: 18px 22px 24px; }

        /* ── Encabezado ── */
        .logo-wrap {
            width: fit-content;
            margin: 0 auto 8px;
        }
        .logo-wrap img { display: block; height: 70px; width: auto; }

        .brand {
            font-family: 'Oswald', 'Archivo Narrow', sans-serif;
            font-size: 26px;
            font-weight: 700;
            text-align: center;
            letter-spacing: 2px;
            line-height: 1.1;
            color: var(--azul-oscuro);
        }
        .sucursal {
            width: fit-content;
            margin: 8px auto 0;
            padding: 3px 14px;
            border: 1.5px solid var(--azul);
            border-radius: 999px;
            font-family: 'Oswald', sans-serif;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 2.5px;
            color: var(--azul-oscuro);
            text-transform: uppercase;
        }
        .contacto {
            text-align: center;
            font-size: 11px;
            font-weight: 600;
            color: var(--gris);
            margin-top: 8px;
            letter-spacing: .5px;
        }

        /* ── Datos del ticket ── */
        .meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
            padding: 10px 12px;
            background: var(--azul-claro);
            border: 1px solid var(--linea);
            border-radius: 8px;
        }
        .meta .folio-lbl {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.5px;
            color: var(--gris);
        }
        .meta .folio {
            font-family: 'Oswald', sans-serif;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: .5px;
            color: var(--azul-oscuro);
        }
        .meta .fecha { text-align: right; }
        .meta .fecha .dia {
            font-size: 13px;
            font-weight: 700;
            color: #111;
        }
        .meta .fecha .hora {
            font-size: 12px;
            font-weight: 600;
            color: var(--gris);
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 12.5px;
            margin: 6px 0;
        }
        .row .lbl {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 1px;
            color: var(--gris);
            text-transform: uppercase;
            padding-top: 1px;
        }
        .row .val { font-weight: 700; text-align: right; }

        .linea {
            border: none;
            border-top: 1px solid var(--linea);
            margin: 14px 0;
        }

        /* ── Productos ── */
        table { width: 100%; border-collapse: collapse; }
        thead th {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 1px;
            color: var(--azul-oscuro);
            text-align: left;
            padding-bottom: 6px;
            border-bottom: 2px solid var(--azul);
        }
        thead th:last-child { text-align: right; }
        tbody td {
            font-size: 13px;
            padding: 7px 0;
            vertical-align: top;
            border-bottom: 1px solid var(--linea);
        }
        tbody tr:last-child td { border-bottom: none; }
        tbody .cant {
            font-weight: 700;
            white-space: nowrap;
            padding-right: 8px;
            color: var(--azul-oscuro);
        }
        tbody .desc { font-weight: 600; line-height: 1.25; padding-right: 8px; }
        tbody .precio { font-weight: 700; text-align: right; white-space: nowrap; }

        /* ── Totales ── */
        .tot-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            font-weight: 600;
            color: var(--gris);
            margin: 4px 0;
        }
        .tot-row span:last-child { color: #111; font-weight: 700; }

        .total-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            padding: 11px 14px;
            background: var(--azul-claro);
            border: 2px solid var(--azul-oscuro);
            border-radius: 8px;
            font-family: 'Oswald', sans-serif;
            color: var(--azul-oscuro);
        }
        .total-box .t-lbl { font-size: 15px; font-weight: 600; letter-spacing: 2px; }
        .total-box .t-val { font-size: 24px; font-weight: 700; }

        /* ── Pago ── */
        .pago {
            margin-top: 14px;
            padding: 10px 14px;
            border: 1px solid var(--linea);
            border-radius: 8px;
        }
        .pago-titulo {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 1.5px;
            color: var(--azul-oscuro);
            margin-bottom: 4px;
        }

        /* ── QR ── */
        .qr-zone { text-align: center; margin-top: 18px; }
        #qrcode {
            display: inline-block;
            padding: 8px;
            border: 1px solid var(--linea);
            border-radius: 8px;
        }
        #qrcode img, #qrcode canvas { display: block; }
        .qr-hint {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .5px;
            color: var(--gris);
            margin-top: 8px;
            text-transform: uppercase;
        }

        /* ── Pie ── */
        .footer { text-align: center; margin-top: 20px; }
        .footer .gracias {
            font-family: 'Oswald', sans-serif;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 1.5px;
            color: var(--azul-oscuro);
            text-transform: uppercase;
        }
        .footer .vuelva {
            font-size: 11px;
            font-weight: 600;
            color: var(--gris);
            margin-top: 3px;
            letter-spacing: .5px;
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
        .btn-imprimir { background: var(--azul-oscuro); color: #fff; }
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
        <div class="franja"></div>

        <div class="contenido">

            {{-- Logo y nombre del negocio --}}
            <div class="logo-wrap">
                <img src="{{ asset(config('empresa.logo')) }}" alt="Logo">
            </div>
            <div class="brand">{{ mb_strtoupper(config('empresa.nombre')) }}</div>
            <div class="sucursal">{{ $venta->sucursal->nombre ?? 'Sucursal' }}</div>

            @if(config('empresa.telefono'))
                <div class="contacto">Tel: {{ config('empresa.telefono') }}</div>
            @endif

            {{-- Datos del ticket --}}
            <div class="meta">
                <div>
                    <div class="folio-lbl">TICKET</div>
                    <div class="folio">#{{ $venta->folio }}</div>
                </div>
                <div class="fecha">
                    <div class="dia">{{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y') }}</div>
                    <div class="hora">{{ \Carbon\Carbon::parse($venta->fecha)->format('H:i') }}</div>
                </div>
            </div>

            {{-- Cliente y cajero --}}
            <div style="margin-top: 12px;">
                <div class="row">
                    <span class="lbl">Cliente</span>
                    <span class="val">{{ $venta->cliente->nombre ?? $venta->nombre_cliente_temporal ?? 'Público General' }}</span>
                </div>
                <div class="row">
                    <span class="lbl">Atendió</span>
                    <span class="val">{{ $venta->user->name ?? 'Usuario #' . $venta->user_id }}</span>
                </div>
            </div>

            <hr class="linea">

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

            <hr class="linea">

            {{-- Totales --}}
            @php
                // Subtotal = suma de los productos (sin IVA). El IVA es lo que falta para llegar al total.
                $descuentoTotal = $venta->detalles->sum('descuento');
                $subtotal = $venta->detalles->sum('subtotal');
                $iva = $venta->requiere_factura ? round($venta->total - $subtotal, 2) : 0;
            @endphp

            <div class="tot-row">
                <span>Subtotal</span>
                <span>${{ number_format($subtotal, 2) }}</span>
            </div>
            @if($descuentoTotal > 0)
                <div class="tot-row">
                    <span>Descuento</span>
                    <span>-${{ number_format($descuentoTotal, 2) }}</span>
                </div>
            @endif

            @if($venta->requiere_factura)
                <div class="tot-row">
                    <span>IVA (16%)</span>
                    <span>${{ number_format($iva, 2) }}</span>
                </div>
            @endif

            <div class="total-box">
                <span class="t-lbl">TOTAL</span>
                <span class="t-val">${{ number_format($venta->total, 2) }}</span>
            </div>

            {{-- Pago --}}
            <div class="pago">
                <div class="pago-titulo">FORMA DE PAGO</div>
                <div class="row">
                    <span class="lbl">Método</span>
                    <span class="val">{{ mb_strtoupper($venta->metodo_pago ?? 'Efectivo') }}</span>
                </div>
                <div class="row">
                    <span class="lbl">Recibido</span>
                    <span class="val">${{ number_format((float)($venta->pago_con ?? 0), 2) }}</span>
                </div>
                @if(($venta->metodo_pago ?? 'Efectivo') === 'Efectivo')
                <div class="row">
                    <span class="lbl">Cambio</span>
                    <span class="val">${{ number_format((float)($venta->cambio ?? 0), 2) }}</span>
                </div>
                @endif
            </div>

            {{-- QR de facturación por WhatsApp (solo si la empresa configuró su número) --}}
            @if(config('empresa.whatsapp'))
            <div class="qr-zone">
                <div id="qrcode"></div>
                <div class="qr-hint">Escanea para facturar por WhatsApp</div>
            </div>
            @endif

            {{-- Pie --}}
            <div class="footer">
                <div class="gracias">¡Gracias por su preferencia!</div>
                <div class="vuelva">Vuelva pronto</div>
            </div>
        </div>
    </div>

    {{-- Botones flotantes (no salen al imprimir) --}}
    <div class="acciones">
        <button class="btn-imprimir" onclick="window.print()">Imprimir</button>
        <button class="btn-cerrar" onclick="window.close()">Cerrar</button>
    </div>

    @if(config('empresa.whatsapp'))
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

            var url = "https://wa.me/{{ preg_replace('/\D+/', '', config('empresa.whatsapp')) }}?text=" + encodeURIComponent(mensaje);

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
    @endif
</body>
</html>
