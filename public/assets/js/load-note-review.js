/* global Swal, Cleave */
document.addEventListener('DOMContentLoaded', () => {
    let saving = false;
    document.querySelectorAll('.note-search-select').forEach(select => {
        window.jQuery(select).select2({width: '100%', dropdownParent: window.jQuery(select.closest('.modal'))});
    });
    const paymentModal = document.getElementById('note-payment-modal');
    if (paymentModal) {
        const amount = document.getElementById('note-transfer-input');
        const raw = document.getElementById('note-transfer-raw');
        const useSystem = document.getElementById('note-use-system');
        const systemValue = document.getElementById('note-use-system-value');
        const total = document.getElementById('note-payment-total');
        const formatter = new Cleave(amount, {numeral: true, numeralThousandsGroupStyle: 'thousand',
            delimiter: '.', numeralDecimalMark: ',', numeralDecimalScale: 0, numeralPositiveOnly: true,
            onValueChanged: event => { raw.value = event.target.rawValue; updatePaymentTotal(); }});
        function updatePaymentTotal() {
            const transfer = useSystem.checked ? paymentModal.dataset.systemTransfer : raw.value;
            const funds = [paymentModal.dataset.sparepart, paymentModal.dataset.ban];
            const valid = transfer !== '' && /^\d+$/.test(transfer) && funds.every(value => value !== '' && /^\d+$/.test(value));
            total.textContent = valid ? 'Rp ' + (Number(transfer) + Number(funds[0]) + Number(funds[1])).toLocaleString('id-ID') : 'Belum tersedia';
        }
        raw.value = formatter.getRawValue();
        useSystem.addEventListener('change', () => {
            systemValue.value = useSystem.checked ? '1' : '0';
            amount.readOnly = useSystem.checked;
            amount.required = !useSystem.checked;
            raw.disabled = useSystem.checked;
            updatePaymentTotal();
        });
        updatePaymentTotal();
    }
    const decisionModal = document.getElementById('note-decision-modal');
    decisionModal?.addEventListener('show.bs.modal', event => {
        const action = event.relatedTarget?.dataset.noteDecision;
        if (!['reject', 'cancel'].includes(action)) { event.preventDefault(); return; }
        const form = decisionModal.querySelector('form');
        form.querySelector('[name="action"]').value = action;
        form.querySelector('[name="reason"]').value = '';
        const label = action === 'reject' ? 'Tolak nota' : 'Batalkan nota';
        document.getElementById('note-decision-title').textContent = label;
        document.getElementById('note-decision-submit').textContent = label;
        form.dataset.confirm = label + '?';
    });
    document.querySelectorAll('#note-edit-modal, #note-decision-modal, #note-payment-modal').forEach(modal => {
        modal.addEventListener('hide.bs.modal', event => { if (saving) event.preventDefault(); });
        modal.addEventListener('shown.bs.modal', () => modal.querySelector('input:not([type="hidden"]), textarea')?.focus());
    });
    document.querySelectorAll('[data-note-review]').forEach(form => {
        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (saving) return;
            const target = form.closest('.modal') || document.body;
            if (!form.checkValidity()) {
                await Swal.fire({icon: 'warning', title: 'Data belum lengkap', text: 'Lengkapi data wajib dan alasan sebelum melanjutkan.', target});
                return;
            }
            saving = true;
            const action = form.querySelector('[name="action"]')?.value;
            const reason = form.querySelector('[name="reason"]')?.value || form.dataset.confirmDetails || '';
            let buttons = [];
            let navigating = false;
            try {
                const confirmation = await Swal.fire({title: form.dataset.confirm, text: reason,
                    icon: action ? 'warning' : 'question', showCancelButton: true, confirmButtonText: 'Ya, lanjutkan', cancelButtonText: 'Kembali', focusCancel: true, target});
                if (!confirmation.isConfirmed) return;
                const body = new FormData(form);
                buttons = [...target.querySelectorAll('input, textarea, select, button')].map(element => ({element, disabled: element.disabled}));
                buttons.forEach(({element}) => { element.disabled = true; });
                Swal.fire({title: 'Memproses nota…', target, allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: () => Swal.showLoading()});
                const response = await fetch(form.action, {method: 'POST', body, credentials: 'same-origin', redirect: 'error', headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.errors ? Object.values(data.errors).flat().join('\n') : data.message || 'Nota belum dapat diproses.');
                }
                await Swal.fire({icon: 'success', title: 'Berhasil', text: data.message, target, confirmButtonText: 'Baik'});
                navigating = true;
                if (form.hasAttribute('data-note-process')) {
                    window.location.assign(form.dataset.successUrl);
                } else {
                    window.location.reload();
                }
            } catch (error) {
                await Swal.fire({icon: 'error', title: 'Nota belum dapat diproses', text: error instanceof TypeError || error instanceof SyntaxError ? 'Respons belum dapat dipastikan. Muat ulang nota sebelum mencoba kembali.' : error.message, target});
            } finally {
                if (!navigating) {
                    saving = false;
                    buttons.forEach(({element, disabled}) => { element.disabled = disabled; });
                }
            }
        });
    });
});
