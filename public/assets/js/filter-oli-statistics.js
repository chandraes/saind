document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('filterRitaseModal');
    if (!modal) return;
    const content = modal.querySelector('[data-ritase-content]');
    let controller;
    modal.addEventListener('show.bs.modal', async (event) => {
        controller?.abort();
        const request = new AbortController();
        controller = request;
        content.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat transaksi…</span></div><p class="text-muted mt-3 mb-0">Memuat daftar transaksi…</p></div>';
        try {
            const response = await fetch(event.relatedTarget.dataset.ritaseUrl, {
                headers: {'X-Requested-With': 'XMLHttpRequest'}, signal: request.signal,
            });
            if (!response.ok || response.redirected) throw new Error('Daftar transaksi tidak dapat dimuat. Silakan coba kembali.');
            const html = await response.text();
            if (!request.signal.aborted) content.innerHTML = html;
        } catch (error) {
            if (error.name === 'AbortError') return;
            bootstrap.Modal.getInstance(modal)?.hide();
            Swal.fire({icon: 'error', title: 'Gagal memuat transaksi', text: error.message, confirmButtonText: 'OK'});
        }
    });
    modal.addEventListener('hide.bs.modal', () => controller?.abort());
});
