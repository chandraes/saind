@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/dt.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.min.css') }}">
@endpush
@push('js')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
@once('incremental-data-table')
<script src="{{ asset('assets/js/incremental-data-table.js') }}?v={{ filemtime(public_path('assets/js/incremental-data-table.js')) }}"></script>
@endonce
<script src="{{ asset('assets/js/load-note-inbox.js') }}?v={{ filemtime(public_path('assets/js/load-note-inbox.js')) }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.jQuery?.fn.select2) jQuery('.readonly-select').select2({width: '100%'});
    const modalElement = document.getElementById('readonly-detail-modal');
    const body = document.getElementById('readonly-detail-body');
    let pending;
    document.getElementById('note-inbox-table').addEventListener('click', async event => {
        const link = event.target.closest('.js-readonly-detail');
        if (!link) return;
        event.preventDefault();
        pending?.abort();
        const request = new AbortController();
        pending = request;
        body.textContent = 'Memuat detail…';
        body.setAttribute('aria-busy', 'true');
        bootstrap.Modal.getOrCreateInstance(modalElement).show();
        async function load() {
            body.textContent = 'Memuat detail…';
            body.setAttribute('aria-busy', 'true');
            try {
                const response = await fetch(link.href, {signal: request.signal, headers: {Accept: 'text/html'}});
                if (!response.ok) throw new Error('load failed');
                body.innerHTML = await response.text();
            } catch (error) {
                if (error.name === 'AbortError') return;
                body.textContent = 'Detail gagal dimuat. ';
                const retry = document.createElement('button');
                retry.type = 'button'; retry.className = 'btn btn-outline-primary btn-sm'; retry.textContent = 'Coba ulang';
                retry.addEventListener('click', load); body.append(retry);
            } finally { if (pending === request) body.setAttribute('aria-busy', 'false'); }
        }
        await load();
    });
    modalElement.addEventListener('hidden.bs.modal', () => pending?.abort());
});
</script>
@endpush
