window.vendorStatisticsTable = function (selector, options) {
    const view = createIncrementalDataTable(selector, {data: [], columns: options.columns});
    const status = $('<div class="text-center py-3" role="status" aria-live="polite"></div>').insertAfter($(selector).closest('.table-responsive'));
    let busy = false, finished = false, failed = false, offset = 0;
    function load() {
        if (busy || finished) return;
        busy = true;
        failed = false;
        status.text('Memuat riwayat...');
        $.ajax({url: options.url, dataType: 'json', data: {...options.data, start: offset, length: 25}})
            .done(function (response) {
                view.add(response.data);
                offset += response.data.length;
                finished = offset >= response.recordsFiltered || response.data.length === 0;
                status.text(finished ? (offset ? 'Seluruh riwayat telah ditampilkan.' : 'Belum ada riwayat.') : '');
            }).fail(function () {
                failed = true;
                status.text('Riwayat gagal dimuat. ').append($('<button type="button" class="btn btn-outline-primary btn-sm">Coba lagi</button>').on('click', load));
            }).always(function () { busy = false; if (!finished && !failed) check(); });
    }
    function check() {
        if (!failed && view.nearEnd()) load();
    }
    view.watch(check);
    load();
};

window.vendorStatisticsAudit = function (container, url, filterOil) {
    const target = $(container).empty();
    const labels = filterOil ? ['Transaksi', 'Uang jalan', 'Tanggal', 'Rute', 'Jarak', 'Ritase'] : ['Tanggal', 'Uang jalan', 'Rute', 'Jarak', 'Ritase'];
    const table = $('<table class="table table-bordered align-middle"><thead class="table-success"><tr></tr></thead><tbody></tbody></table>');
    labels.forEach(label => table.find('thead tr').append($('<th>').text(label)));
    target.append($('<div class="table-responsive">').append(table));
    const text = $.fn.dataTable.render.text();
    const columns = filterOil ? [{data: 'id', render: text}, {data: 'nomor_uang_jalan', render: text}, {data: 'created_at', render: text}] : [{data: 'tanggal', render: text}, {data: 'nomor_uang_jalan', render: text}];
    columns.push({data: 'rute', render: text}, {data: 'jarak', render: text}, {data: 'nilai_ritase', render: text});
    vendorStatisticsTable(table, {url: url, data: {}, columns: columns});
};
