// Código QR del ticket para facturar por WhatsApp (solo se carga en el ticket).
import QRCode from 'qrcode';

window.dibujarQr = function (canvas, texto, tamano) {
    return QRCode.toCanvas(canvas, texto, {
        width: tamano,
        margin: 0,
        errorCorrectionLevel: 'M',
        color: { dark: '#000000', light: '#ffffff' },
    });
};
