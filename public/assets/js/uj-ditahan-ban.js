document.addEventListener('DOMContentLoaded', () => {
    const element = document.querySelector('[data-ban-table]');
    if (!element) return;
    const money = value => 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(value || 0));
    const text = jQuery.fn.dataTable.render.text();
    const amount = {render: (value, type) => type === 'display' ? `<span class="ban-amount">${money(value)}</span>` : value, className: 'text-end text-nowrap'};
    const details = element.dataset.kind === 'details';
    const columns = details ? [
        {data: 'no'}, {data: 'tanggal', render: text}, {data: 'keterangan', render: text}, {data: 'driver', render: text},
        {data: 'nominal_masuk', ...amount}, {data: 'nominal_keluar', ...amount}, {data: 'rekening', render: text},
        {data: 'proof_url', render: value => value ? `<a class="btn btn-sm btn-outline-success ban-detail-btn" target="_blank" rel="noopener" href="${text.display(value)}"><i class="fa fa-file-pdf-o me-1" aria-hidden="true"></i> PDF</a>` : '—'},
    ] : [
        {data: 'no'}, {data: 'kendaraan', render: (value, type) => type === 'display' ? `<span class="ban-vehicle"><i class="fa fa-truck" aria-hidden="true"></i>${text.display(value)}</span>` : value}, {data: 'total_masuk', ...amount}, {data: 'total_keluar', ...amount}, {data: 'saldo', ...amount},
        {data: 'detail_url', render: (value, type, row) => `<div class="d-flex gap-2 flex-wrap"><a class="btn btn-sm btn-outline-success ban-detail-btn" href="${text.display(value)}"><i class="fa fa-eye me-1" aria-hidden="true"></i> Lihat detail</a>${row.cutoff ? '<button type="button" class="btn btn-sm btn-outline-danger ban-detail-btn" data-bs-toggle="modal" data-bs-target="#cutoff-ban"><i class="fa fa-scissors me-1" aria-hidden="true"></i> Cutoff saldo</button>' : ''}</div>`},
    ];
    jQuery.fn.dataTable.ext.errMode = 'none';
    jQuery(element).on('error.dt', () => Swal.fire({icon: 'error', title: 'Data gagal dimuat', text: 'Muat ulang halaman atau periksa koneksi Anda.'}));
    const table = jQuery(element).DataTable({
        processing: true, serverSide: true, deferRender: true, ordering: false,
        searchDelay: 400, pageLength: 25, scrollY: '460px', scrollX: true, scroller: {displayBuffer: 3}, dom: 'frti',
        ajax: {url: element.dataset.url, dataSrc: response => {
            document.querySelectorAll('[data-ban-count]').forEach(label => label.textContent = new Intl.NumberFormat('id-ID').format(response.recordsFiltered));
            for (const key of ['masuk', 'keluar', 'saldo']) {
                document.querySelectorAll(`[data-total-${key}]`).forEach(cell => cell.textContent = money(response.totals[key]));
                if (!details) document.querySelectorAll(`[data-card-${key}]`).forEach(cell => cell.textContent = money(response.totals[key]));
            }
            return response.data;
        }}, columns,
        language: {search: 'Cari:', searchPlaceholder: details ? 'Uraian / rekening' : 'Nomor lambung', processing: 'Memuat data…', emptyTable: '<i class="fa fa-folder-open-o ban-empty-icon" aria-hidden="true"></i><span class="ban-empty-label">Belum ada data pada periode ini</span><span class="ban-empty-help">Pilih periode lain untuk menelusuri pencatatan UJ Ditahan Ban.</span>', zeroRecords: 'Data tidak ditemukan.', info: 'Menampilkan _START_–_END_ dari _TOTAL_ data', infoEmpty: 'Belum ada data', infoFiltered: '(dari _MAX_ data)'},
    });
    const cutoffModal = document.getElementById('cutoff-ban');
    if (cutoffModal) cutoffModal.addEventListener('show.bs.modal', event => {
        const row = table.row(event.relatedTarget.closest('tr')).data();
        const form = cutoffModal.querySelector('[data-cutoff-form]');
        if (!row?.cutoff) { event.preventDefault(); return; }
        form.action = row.cutoff.url;
        form.elements.request_token.value = row.cutoff.token;
        form.dataset.confirm = `Cutoff seluruh saldo ${row.kendaraan} periode ${row.cutoff.periode}? Saldo akan menjadi nol.`;
        const fields = {...row.cutoff, vehicle: row.kendaraan, saldo: money(row.saldo)};
        for (const key of ['vehicle', 'periode', 'saldo', 'driver', 'bank', 'rekening', 'nama']) {
            cutoffModal.querySelector(`[data-cutoff-${key}]`).textContent = fields[key];
        }
    });

});
