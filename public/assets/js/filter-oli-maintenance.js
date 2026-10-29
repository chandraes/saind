document.addEventListener('DOMContentLoaded', () => {
    const selects = jQuery('.maintenance-select');
    selects.each(function () {
        const modal = jQuery(this).closest('.modal');
        jQuery(this).select2({
            theme: 'bootstrap-5', width: '100%',
            dropdownParent: modal.length ? modal : jQuery(document.body),
        });
    });
    const moneyInput = document.querySelector('[data-maintenance-money]');
    const moneyValue = document.querySelector('[data-maintenance-money-value]');
    if (moneyInput && moneyValue) {
        const formatter = new Cleave(moneyInput, {numeral: true, numeralThousandsGroupStyle: 'thousand', delimiter: '.', numeralDecimalMark: ',', numeralDecimalScale: 0, numeralPositiveOnly: true});
        const syncMoney = () => { moneyValue.value = formatter.getRawValue(); };
        moneyInput.addEventListener('input', syncMoney);
        syncMoney();
    }
    const payment = document.querySelector('[data-payment-method]');
    const bankFields = document.querySelector('[data-bank-fields]');
    const updatePayment = () => {
        if (!payment || !bankFields) return;
        const useBank = ['kas_besar', 'uj_ditahan', 'uj_ditahan_ban'].includes(payment.value);
        const balance = document.querySelector('[data-uj-balance]');
        if (balance) {
            const showBalance = ['uj_ditahan', 'uj_ditahan_ban'].includes(payment.value);
            balance.hidden = !showBalance;
            balance.classList.toggle('d-none', !showBalance);
        }
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

    document.addEventListener('submit', async (event) => {
            const form = event.target;
            if (!form.matches('[data-maintenance-form]')) return;
            event.preventDefault();
            if (form.dataset.submitting === 'true') return;
            form.dataset.submitting = 'true';
            try {
                if (!form.checkValidity()) {
                    const invalid = Array.from(form.elements).find((input) => input.willValidate && !input.validity.valid);
                    await Swal.fire({icon: 'error', title: 'Isian belum sesuai', text: invalid?.validationMessage || 'Lengkapi seluruh isian dengan benar.'});
                    invalid?.focus();
                    return;
                }
                const warning = form.querySelector('[data-authorization-warning]');
                const result = await Swal.fire({
                    ...(warning ? {html: warning.innerHTML, width: '760px'} : {text: form.dataset.warning ? `Peringatan ritase di bawah acuan: ${form.dataset.warning}. ${form.dataset.confirm}` : form.dataset.confirm}),
                    icon: warning ? 'warning' : 'question', title: 'Konfirmasi',
                    showCancelButton: true, confirmButtonText: 'Ya, lanjutkan',
                    cancelButtonText: 'Batal', focusCancel: true,
                });
                if (!result.isConfirmed) return;
                form.querySelectorAll('[type="submit"]').forEach((button) => button.disabled = true);
                Swal.fire({title: 'Memproses…', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading()});
                if (form.hasAttribute('data-maintenance-ajax')) {
                    const response = await fetch(form.action, {method: 'POST', body: new FormData(form), headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Permintaan gagal. Coba kembali.'));
                    await Swal.fire({icon: 'success', title: 'Berhasil', text: data.message});
                    window.location.reload();
                } else {
                    HTMLFormElement.prototype.submit.call(form);
                }
            } catch (error) {
                await Swal.fire({icon: 'error', title: 'Proses gagal', text: error.message || 'Periksa koneksi dan coba kembali.'});
                form.querySelectorAll('[type="submit"]').forEach((button) => button.disabled = false);
            } finally {
                delete form.dataset.submitting;
            }
    });
});
