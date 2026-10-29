@extends('layouts.app')
@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 fw-bold mb-1">Otorisasi Penggantian {{ $type === 'aki' ? 'Aki' : 'Filter & Oli Mesin' }}</h1><p class="text-muted mb-0">Periksa rincian penggantian dan saldo kendaraan sebelum menyetujui.</p></div>
        <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>
    @include('billing.form-maintenance.filter-oli.feedback')
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden"><div class="card-body">
        <table id="maintenance-authorization" class="table table-hover align-middle w-100"><thead class="table-success"><tr><th>No</th><th>No. Invoice</th><th>Tanggal</th><th>No. Lambung</th><th>Pembayaran</th><th>Nominal</th><th>Saldo UJ Ditahan Sparepart</th><th>Item</th><th>Aksi</th></tr></thead><tbody></tbody></table>
        <p class="small text-muted mt-3 mb-0">Saldo yang ditampilkan adalah saldo saat daftar dimuat. Sistem memeriksa ulang saldo ketika invoice disetujui.</p>
    </div></div>
</div>
@endsection
@push('css')
<link href="{{ asset('assets/css/dt.min.css') }}" rel="stylesheet">
<style>#maintenance-authorization td{height:58px;white-space:nowrap}#maintenance-authorization th{white-space:nowrap}.dataTables_info{font-size:.875rem;color:#6c757d}</style>
<link href="https://cdn.datatables.net/scroller/2.2.0/css/scroller.bootstrap5.min.css" rel="stylesheet">
@endpush
@push('js')
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
<script src="https://cdn.datatables.net/scroller/2.2.0/js/dataTables.scroller.min.js"></script>
<script>
jQuery(function ($) {
    $.fn.dataTable.ext.errMode = 'none';
    const text = $.fn.dataTable.render.text();
    const rupiah = (value) => 'Rp ' + Number(value).toLocaleString('id-ID');
    $('#maintenance-authorization').on('error.dt', function () { Swal.fire({icon: 'error', title: 'Daftar gagal dimuat', text: 'Periksa koneksi, lalu muat ulang halaman.'}); }).DataTable({
        processing: true, serverSide: true, deferRender: true, ordering: false, dom: 'frti',
        scrollY: '460px', scrollX: true, scrollCollapse: true, pageLength: 25, searchDelay: 400,
        scroller: {displayBuffer: 3, serverWait: 350, rowHeight: 58}, ajax: @json(route('billing.otorisasi-maintenance.'.$type)),
        columns: [
            {data: 'no', searchable: false}, {data: 'no_invoice', render: text},
            {data: 'tanggal', render: text}, {data: 'vehicle', render: text}, {data: 'pembayaran', render: text},
            {data: 'nominal', className: 'text-end text-nowrap', render: rupiah},
            {data: 'saldo_uj', className: 'text-end text-nowrap', render: (value, type, row) => type === 'display' ? '<span class="' + (row.saldo_kurang ? 'text-danger fw-bold' : 'text-success') + '">' + rupiah(value) + '</span>' : value},
            {data: 'jumlah', searchable: false}, {data: 'actions', searchable: false}
        ],
        language: {search: 'Cari invoice atau kendaraan:', emptyTable: 'Tidak ada invoice yang menunggu otorisasi.', info: 'Menampilkan _START_–_END_ dari _TOTAL_ invoice', infoEmpty: 'Tidak ada invoice', processing: 'Memuat…', zeroRecords: 'Invoice tidak ditemukan.'}
    });
});
</script>
@endpush
