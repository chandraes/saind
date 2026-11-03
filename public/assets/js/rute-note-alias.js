/* global Swal */
window.setupRouteNoteAliases = function (table) {
    const element = document.getElementById('note-alias-modal');
    const content = document.getElementById('note-alias-content');
    const feedback = document.getElementById('note-alias-feedback');
    let url;
    let busy = false;
    let generation = 0;
    let lastTrigger;

    function message(text, error = false) {
        feedback.className = 'alert ' + (error ? 'alert-danger' : 'alert-success');
        feedback.textContent = text;
    }

    async function load() {
        const current = ++generation;
        content.innerHTML = '<div class="py-5 text-center" role="status"><span class="spinner-border text-primary" aria-hidden="true"></span><span class="d-block mt-3 text-muted">Memuat alias rute…</span></div>';
        try {
            const response = await fetch(url, {credentials: 'same-origin', redirect: 'error', headers: {'X-Requested-With': 'XMLHttpRequest'}});
            if (!response.ok) throw new Error('Alias belum dapat dimuat. Tutup dan coba kembali.');
            const html = await response.text();
            if (current !== generation) return;
            content.innerHTML = html;
            content.querySelector('[name="name"]')?.focus();
        } catch (error) {
            if (current !== generation) return;
            content.textContent = '';
            message(error.message || 'Koneksi terputus. Tutup dan coba kembali.', true);
        }
    }

    document.getElementById('rute-table').addEventListener('click', function (event) {
        const button = event.target.closest('[data-note-alias-url]');
        if (!button || busy) return;
        lastTrigger = button;
        url = button.dataset.noteAliasUrl;
        feedback.className = 'alert d-none';
        window.jQuery(element).modal('show');
        load();
    });
    element.addEventListener('shown.bs.modal', () => content.querySelector('[name="name"]')?.focus());
    element.addEventListener('hide.bs.modal', event => { if (busy) event.preventDefault(); });
    element.addEventListener('hidden.bs.modal', () => {
        generation++;
        content.textContent = '';
        const trigger = lastTrigger?.isConnected ? lastTrigger : [...document.querySelectorAll('[data-note-alias-url]')].find(button => button.dataset.noteAliasUrl === url);
        trigger?.focus();
    });

    function resetEditor() {
        const form = document.getElementById('note-alias-form');
        form.action = form.dataset.createUrl;
        delete form.dataset.editing;
        form.querySelector('[name="name"]').value = '';
        form.querySelector('[name="name"]').classList.remove('is-invalid');
        form.querySelector('[name="name"]').removeAttribute('aria-invalid');
        document.getElementById('note-alias-field-label').textContent = 'Tambah alias';
        document.getElementById('note-alias-submit').textContent = 'Tambah alias';
        document.getElementById('note-alias-cancel-edit').hidden = true;
    }

    content.addEventListener('click', function (event) {
        if (busy) return;
        const edit = event.target.closest('.note-alias-edit');
        if (edit) {
            const form = document.getElementById('note-alias-form');
            form.action = edit.dataset.updateUrl;
            form.dataset.editing = 'true';
            const input = form.querySelector('[name="name"]');
            input.value = edit.dataset.name;
            input.classList.remove('is-invalid');
            input.removeAttribute('aria-invalid');
            document.getElementById('note-alias-field-label').textContent = 'Edit alias';
            document.getElementById('note-alias-submit').textContent = 'Simpan perubahan';
            document.getElementById('note-alias-cancel-edit').hidden = false;
            input.focus();
        } else if (event.target.closest('#note-alias-cancel-edit')) {
            resetEditor();
            document.getElementById('note-alias-name').focus();
        }
    });

    content.addEventListener('submit', async function (event) {
        const form = event.target;
        event.preventDefault();
        if (busy || !form.matches('#note-alias-form, .note-alias-delete')) return;
        const deleting = form.matches('.note-alias-delete');
        // Guard before awaiting confirmation, so repeated taps cannot open duplicate requests.
        busy = true;
        const editing = Boolean(form.dataset.editing);
        const result = await Swal.fire({
            title: deleting ? 'Hapus alias ini?' : editing ? 'Simpan perubahan alias?' : 'Tambahkan alias ini?',
            text: deleting ? 'Rute utama tetap tersimpan. Nota baru tidak dapat menggunakan alias yang dihapus.' : 'Pastikan nama alias sesuai dengan referensi yang tercetak pada nota.',
            icon: deleting ? 'warning' : 'question', target: element, showCancelButton: true,
            confirmButtonText: deleting ? 'Hapus alias' : editing ? 'Simpan perubahan' : 'Tambah alias',
            cancelButtonText: 'Batal', returnFocus: true
        });
        if (!result.isConfirmed) { busy = false; return; }
        const data = new FormData(form);
        if (form.dataset.editing) data.set('_method', 'PATCH');
        const buttons = [...element.querySelectorAll('button, input')];
        buttons.forEach(button => { button.disabled = true; });
        const submit = form.querySelector('[type="submit"]');
        const label = submit.textContent;
        submit.textContent = 'Menyimpan…';
        feedback.className = 'alert d-none';
        Swal.fire({title: deleting ? 'Menghapus alias…' : 'Menyimpan alias…', target: element, allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: () => Swal.showLoading()});
        try {
            const response = await fetch(form.action, {method: 'POST', body: data, credentials: 'same-origin', redirect: 'error', headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
            const payload = await response.json();
            if (!response.ok) {
                if (response.status === 422 && !deleting && payload.errors?.name) {
                    const input = form.querySelector('[name="name"]');
                    input.classList.add('is-invalid');
                    input.setAttribute('aria-invalid', 'true');
                    document.getElementById('note-alias-error').textContent = payload.errors.name[0];
                }
                throw new Error(payload.errors?.name?.[0] || payload.message || 'Perubahan belum dapat disimpan.');
            }
            message(payload.message);
            table.ajax.reload(null, false);
            await load();
            await Swal.fire({icon: 'success', title: 'Berhasil', text: payload.message, target: element, confirmButtonText: 'Baik'});
        } catch (error) {
            const errorMessage = error instanceof TypeError || error instanceof SyntaxError ? 'Respons belum dapat dipastikan. Periksa daftar alias sebelum mencoba kembali.' : error.message;
            message(errorMessage, true);
            await Swal.fire({icon: 'error', title: 'Perubahan belum dapat diselesaikan', text: errorMessage, target: element, confirmButtonText: 'Baik'});
        } finally {
            buttons.forEach(button => { button.disabled = false; });
            if (submit.isConnected) submit.textContent = label;
            busy = false;
            content.querySelector('[name="name"]')?.focus();
        }
    });
};
