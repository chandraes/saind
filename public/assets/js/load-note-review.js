/* global Swal */
document.addEventListener('DOMContentLoaded', () => {
    let saving = false;
    document.querySelectorAll('.note-search-select').forEach(select => {
        window.jQuery(select).select2({width: '100%', dropdownParent: window.jQuery(select.closest('.modal'))});
    });
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
    document.querySelectorAll('#note-edit-modal, #note-decision-modal').forEach(modal => {
        modal.addEventListener('hide.bs.modal', event => { if (saving) event.preventDefault(); });
        modal.addEventListener('shown.bs.modal', () => modal.querySelector('input:not([type="hidden"]), textarea')?.focus());
    });
    document.querySelectorAll('[data-note-review]').forEach(form => {
        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (saving) return;
            const target = form.closest('.modal');
            if (!form.checkValidity()) {
                await Swal.fire({icon: 'warning', title: 'Data belum lengkap', text: 'Lengkapi data wajib dan alasan sebelum melanjutkan.', target});
                return;
            }
            saving = true;
            const action = form.querySelector('[name="action"]')?.value;
            const reason = form.querySelector('[name="reason"]').value;
            let buttons = [];
            let navigating = false;
            try {
                const confirmation = await Swal.fire({title: form.dataset.confirm, text: reason,
                    icon: action ? 'warning' : 'question', showCancelButton: true, confirmButtonText: 'Ya, lanjutkan', cancelButtonText: 'Kembali', focusCancel: true, target});
                if (!confirmation.isConfirmed) return;
                const body = new FormData(form);
                buttons = [...target.querySelectorAll('input, textarea, select, button')];
                buttons.forEach(button => { button.disabled = true; });
                Swal.fire({title: 'Memproses nota…', target, allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: () => Swal.showLoading()});
                const response = await fetch(form.action, {method: 'POST', body, credentials: 'same-origin', redirect: 'error', headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.errors ? Object.values(data.errors).flat().join('\n') : data.message || 'Nota belum dapat diproses.');
                }
                await Swal.fire({icon: 'success', title: 'Berhasil', text: data.message, target, confirmButtonText: 'Baik'});
                navigating = true;
                window.location.reload();
            } catch (error) {
                await Swal.fire({icon: 'error', title: 'Nota belum dapat diproses', text: error instanceof TypeError || error instanceof SyntaxError ? 'Respons belum dapat dipastikan. Muat ulang nota sebelum mencoba kembali.' : error.message, target});
            } finally {
                if (!navigating) {
                    saving = false;
                    buttons.forEach(button => { button.disabled = false; });
                }
            }
        });
    });
});
