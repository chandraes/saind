export function qrFilename(address) {
    const host = new URL(address).host.replace(/[^a-zA-Z0-9.-]/g, '-');
    return `qr-dsgroup-hauling-${host}.png`;
}

export async function shareWorkspaceQr(browser, { file, name, address }) {
    const title = `Hubungkan DSGroup Hauling — ${name}`;
    const text = `Ruang kerja ${name}. Tambahkan melalui QR atau alamat ${address}. Login dengan akun SAIND Anda.`;
    try {
        if (typeof browser.share === 'function') {
            if (typeof browser.canShare === 'function' && browser.canShare({ files: [file] })) {
                await browser.share({ title, text, files: [file] });
                return 'shared';
            }
            await browser.share({ title, text, url: address });
            return 'shared-link';
        }
        if (typeof browser.clipboard?.writeText === 'function') {
            await browser.clipboard.writeText(address);
            return 'copied';
        }
    } catch (error) {
        return error?.name === 'AbortError' ? 'cancelled' : 'unsupported';
    }
    return 'unsupported';
}
