import QRCode from 'qrcode';

const canvas = document.getElementById('workspace-qr');
const status = document.getElementById('workspace-qr-status');
if (canvas && status) {
    QRCode.toCanvas(canvas, canvas.dataset.connection, {
        width: 280, margin: 4, errorCorrectionLevel: 'M',
    }).then(() => {
        status.textContent = 'Pindai dengan DSGroup Hauling.';
    }).catch(() => {
        status.textContent = 'QR tidak dapat ditampilkan. Gunakan alamat website di samping.';
    });
}
