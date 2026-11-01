document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('payout-cancellation-form');
    if (!form) return;
    let confirming = false;
    document.addEventListener('click', async event => {
        const button = event.target.closest('[data-payout-cancel-url]');
        if (!button || confirming) return;
        confirming = true;
        const result = await Swal.fire({
            title: 'Batalkan pencairan?',
            text: 'Pencairan ' + (button.dataset.payoutAmount || '') + ' dibatalkan. Saldo dikembalikan ke periode asal; histori dan bukti tetap tersimpan.',
            icon: 'warning', input: 'textarea', inputLabel: 'Alasan pembatalan', inputPlaceholder: 'Tuliskan alasan pembatalan…',
            inputAttributes: {maxlength: '500'}, showCancelButton: true, confirmButtonText: 'Ya, batalkan pencairan', cancelButtonText: 'Kembali',
            confirmButtonColor: '#b02a37', allowOutsideClick: false,
            inputValidator: value => !value || !value.trim() ? 'Alasan pembatalan wajib diisi.' : null,
        });
        if (!result.isConfirmed) { confirming = false; return; }
        button.disabled = true;
        form.action = button.dataset.payoutCancelUrl;
        form.elements.reason.value = result.value.trim();
        form.submit();
    });
});
