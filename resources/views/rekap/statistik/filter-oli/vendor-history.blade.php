@extends('layouts.app')
@section('content')
@php $canManage = auth()->user()->canRecordMaintenanceDirectly(); @endphp
<div class="container py-4">
    <div class="d-flex flex-wrap gap-3 justify-content-between mb-4"><div><h1 class="h4">Histori {{ $category->nama }}</h1><p>Kendaraan {{ $vehicle->nomor_lambung }}</p></div><a class="btn btn-outline-secondary align-self-start" href="{{ route('statistik.filter-oli', ['vehicle_id' => $vehicle->id]) }}">Kembali</a></div>
    @include('swal')
    @if ($canManage)
    <div class="alert alert-light border shadow-sm mb-4"><i class="fa fa-info-circle text-primary me-2"></i>Perubahan tanggal ganti dan penghapusan histori akan menghitung ulang ritase. Invoice dan catatan pembayaran tetap tersimpan.</div>
    @endif
    <div class="card"><div class="table-responsive"><table id="vendorFilterHistory" class="table table-bordered align-middle"><thead class="table-success"><tr><th>No</th><th>Tanggal ganti</th><th>Merek</th><th>Kondisi</th><th>Ritase</th><th>Limit kategori</th>@if ($canManage)<th class="text-center">Aksi</th>@endif</tr></thead><tbody></tbody></table></div></div>
</div>
@if ($canManage)
<div class="modal fade" id="vendorFilterEditModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white"><h5 class="modal-title">Ubah Tanggal Ganti</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <form method="post" id="vendorFilterEditForm">@csrf @method('PATCH')
            <div class="modal-body py-4">
                <div class="mb-3"><label class="form-label" for="vendorFilterDate">Tanggal ganti</label><input class="form-control" type="date" id="vendorFilterDate" name="replaced_on" max="{{ now()->toDateString() }}" required></div>
                <div class="mb-3"><label class="form-label" for="vendorFilterEditPassword">Password akun Anda</label><input class="form-control" type="password" id="vendorFilterEditPassword" name="password" autocomplete="current-password" required></div>
            </div>
            <div class="modal-footer bg-light"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
        </form>
    </div></div>
</div>
<div class="modal fade" id="vendorFilterDeleteModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow">
        <div class="modal-header bg-danger text-white"><h5 class="modal-title">Hapus Riwayat {{ $category->nama }}</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <form method="post" id="vendorFilterDeleteForm">@csrf @method('DELETE')
            <div class="modal-body py-4">
                <p class="text-muted mb-3">Riwayat tanggal <strong id="vendorFilterDeleteDate"></strong> yang dihapus tidak dapat dikembalikan. Masukkan password akun Anda untuk mengonfirmasi.</p>
                <input class="form-control" type="password" name="password" placeholder="Password akun Anda" autocomplete="current-password" required>
            </div>
            <div class="modal-footer bg-light"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger">Hapus</button></div>
        </form>
    </div></div>
</div>
@endif
@include('rekap.statistik.filter-oli.transactions-modal')
@endsection
@push('css')
<link href="{{ asset('assets/css/dt.min.css') }}" rel="stylesheet">
@endpush
@push('js')
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
@once('incremental-data-table')
<script src="{{ asset('assets/js/incremental-data-table.js') }}?v={{ filemtime(public_path('assets/js/incremental-data-table.js')) }}"></script>
@endonce
<script src="{{ asset('assets/js/vendor-statistics-table.js') }}?v={{ filemtime(public_path('assets/js/vendor-statistics-table.js')) }}"></script>
<script>
$(function () {
    const text = $.fn.dataTable.render.text();
    const columns = [
        {data: null, render: (data, type, row, meta) => meta.row + 1},
        {data: 'tanggal', render: text}, {data: 'merk', render: text},
        {data: 'kondisi', render: value => Number(value) + '%'},
        {data: 'ritase', render: (value, type, row) => '<button class="btn btn-link" type="button" data-bs-toggle="modal" data-bs-target="#filterRitaseModal" data-ritase-url="' + @json(route('statistik.filter-oli.transaksi-ritase', ['log' => 'LOGID'])) .replace('LOGID', Number(row.id)) + '">' + Number(value).toLocaleString('id-ID') + ' rit</button>'},
        {data: null, render: () => @json($category->limit_ritase) + ' rit'}
    ];
    @if ($canManage)
    const updateUrl = @json(route('statistik.filter-oli.vendor-histori.update', [$vehicle->id, 'LOGID']));
    const destroyUrl = @json(route('statistik.filter-oli.vendor-histori.destroy', [$vehicle->id, 'LOGID']));
    columns.push({data: null, orderable: false, className: 'text-center text-nowrap', render: (data, type, row) => {
        const attrs = 'data-id="' + Number(row.id) + '" data-tanggal="' + $('<div>').text(row.tanggal).html() + '"';
        return '<div class="btn-group btn-group-sm"><button type="button" class="btn btn-outline-primary vendor-filter-edit" data-bs-toggle="modal" data-bs-target="#vendorFilterEditModal" ' + attrs + '><i class="fa fa-edit"></i> Edit</button>'
            + '<button type="button" class="btn btn-outline-danger vendor-filter-delete" data-bs-toggle="modal" data-bs-target="#vendorFilterDeleteModal" ' + attrs + '><i class="fa fa-trash"></i> Hapus</button></div>';
    }});
    // Tanggal tabel berformat d-m-Y; input date memerlukan Y-m-d.
    const ymd = value => String(value).split('-').reverse().join('-');
    $(document).on('click', '.vendor-filter-edit', function () {
        $('#vendorFilterEditForm').attr('action', updateUrl.replace('LOGID', $(this).data('id')))[0].reset();
        $('#vendorFilterDate').val(ymd($(this).data('tanggal')));
    });
    $(document).on('click', '.vendor-filter-delete', function () {
        $('#vendorFilterDeleteForm').attr('action', destroyUrl.replace('LOGID', $(this).data('id')))[0].reset();
        $('#vendorFilterDeleteDate').text($(this).data('tanggal'));
    });
    @endif
    vendorStatisticsTable('#vendorFilterHistory', {url: @json(url()->current()), data: {}, columns: columns});
});
</script>
@endpush
