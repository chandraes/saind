import QRCode from 'qrcode';
import { qrFilename, shareWorkspaceQr } from './workspace-qr-actions';

const canvas = document.getElementById('workspace-qr');
const status = document.getElementById('workspace-qr-status');
const download = document.getElementById('workspace-qr-download');
const share = document.getElementById('workspace-qr-share');
const feedback = document.getElementById('workspace-qr-feedback');
const address = document.getElementById('workspace-address');

if (canvas && status && download && share && feedback && address) {
    let file;
    const setBusy = (busy) => {
        download.disabled = busy;
        share.disabled = busy;
        share.setAttribute('aria-busy', String(busy));
    };
    const prepare = async () => {
        const exportCanvas = document.createElement('canvas');
        const options = { margin: 4, errorCorrectionLevel: 'M' };
        await Promise.all([
            QRCode.toCanvas(canvas, canvas.dataset.connection, { ...options, width: 280 }),
            QRCode.toCanvas(exportCanvas, canvas.dataset.connection, { ...options, width: 1024 }),
        ]);
        const blob = await new Promise((resolve) => exportCanvas.toBlob(resolve, 'image/png'));
        if (!blob) throw new Error('PNG unavailable');
        file = new File([blob], qrFilename(address.value), { type: 'image/png' });
        status.textContent = 'Pindai dengan DSGroup Hauling.';
        setBusy(false);
    };
    prepare().catch(() => {
        status.textContent = 'QR belum siap ditampilkan atau diunduh. Gunakan alamat website di samping.';
    });

    download.addEventListener('click', () => {
        if (!file) return;
        let url;
        let link;
        try {
            url = URL.createObjectURL(file);
            link = document.createElement('a');
            link.href = url;
            link.download = file.name;
            document.body.appendChild(link);
            link.click();
            feedback.textContent = 'Unduhan PNG dimulai. Gambar ini dapat dibagikan kepada pengguna SAIND.';
        } catch {
            feedback.textContent = 'Unduhan tidak dapat dimulai. Coba lagi melalui browser perangkat Anda.';
        } finally {
            link?.remove();
            if (url) setTimeout(() => URL.revokeObjectURL(url), 1000);
        }
    });
    share.addEventListener('click', async () => {
        if (!file) return;
        setBusy(true);
        feedback.textContent = 'Membuka pilihan berbagi…';
        try {
            const result = await shareWorkspaceQr(navigator, { file, name: canvas.dataset.workspaceName, address: address.value });
            feedback.textContent = {
                shared: 'Gambar QR diteruskan ke menu berbagi perangkat.',
                'shared-link': 'Browser ini membagikan alamat website. Gunakan Unduh QR untuk membagikan gambarnya.',
                copied: 'Berbagi QR tidak tersedia. Alamat website sudah disalin; gunakan Unduh QR untuk membagikan gambarnya.',
                cancelled: 'Berbagi dibatalkan.',
                unsupported: 'Berbagi tidak tersedia atau tidak diizinkan. Gunakan Unduh QR atau salin alamat website secara manual.',
            }[result];
        } finally {
            setBusy(false);
        }
    });
}
