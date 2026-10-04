document.addEventListener('DOMContentLoaded', () => {
    const selects = jQuery('.maintenance-select');
    selects.each(function () {
        const modal = jQuery(this).closest('.modal');
        jQuery(this).select2({
            theme: 'bootstrap-5', width: '100%',
            dropdownParent: modal.length ? modal : jQuery(document.body),
        });
    });
    const payment = document.querySelector('[data-payment-method]');
    const bankFields = document.querySelector('[data-bank-fields]');
    const updatePayment = () => {
        if (!payment || !bankFields) return;
        const useBank = payment.value === 'kas_besar';
        bankFields.hidden = !useBank;
        bankFields.classList.toggle('d-none', !useBank);
        bankFields.querySelectorAll('input').forEach((input) => {
            input.required = useBank;
            input.disabled = !useBank;
        });
    };
    if (payment) jQuery(payment).on('change', updatePayment);
    updatePayment();

    const notification = document.querySelector('[data-maintenance-notification]');
    if (notification) {
        Swal.fire({
            icon: notification.dataset.icon,
            title: notification.dataset.icon === 'success' ? 'Berhasil!' : 'Periksa kembali',
            text: notification.dataset.message, confirmButtonText: 'OK',
        });
    }

    document.querySelectorAll('[data-maintenance-form]').forEach((form) => {
        let submitting = false;
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (submitting) return;
            submitting = true;
            try {
                if (!form.checkValidity()) {
                    const invalid = Array.from(form.elements).find((input) => input.willValidate && !input.validity.valid);
                    await Swal.fire({icon: 'error', title: 'Isian belum sesuai', text: invalid?.validationMessage || 'Lengkapi seluruh isian dengan benar.'});
                    invalid?.focus();
                    return;
                }
                const result = await Swal.fire({
                    icon: 'question', title: 'Konfirmasi', text: form.dataset.warning ? `Peringatan ritase di bawah acuan: ${form.dataset.warning}. ${form.dataset.confirm}` : form.dataset.confirm,
                    showCancelButton: true, confirmButtonText: 'Ya, lanjutkan',
                    cancelButtonText: 'Batal', focusCancel: true,
                });
                if (!result.isConfirmed) return;
                form.querySelectorAll('[type="submit"]').forEach((button) => button.disabled = true);
                Swal.fire({title: 'Memproses…', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading()});
                HTMLFormElement.prototype.submit.call(form);
            } finally {
                submitting = false;
            }
        });
    });
});
