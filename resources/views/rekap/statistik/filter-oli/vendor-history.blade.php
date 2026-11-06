@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap gap-3 justify-content-between mb-4"><div><h1 class="h4">Histori {{ $category->nama }}</h1><p>Kendaraan {{ $vehicle->nomor_lambung }}</p></div><a class="btn btn-outline-secondary align-self-start" href="{{ route('statistik.filter-oli', ['vehicle_id' => $vehicle->id]) }}">Kembali</a></div>
    <div class="card"><div class="table-responsive"><table id="vendorFilterHistory" class="table table-bordered align-middle"><thead class="table-success"><tr><th>No</th><th>Tanggal ganti</th><th>Merek</th><th>Kondisi</th><th>Ritase</th><th>Limit kategori</th></tr></thead><tbody></tbody></table></div></div>
</div>
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
    vendorStatisticsTable('#vendorFilterHistory', {
        url: @json(url()->current()), data: {}, columns: [
            {data: null, render: (data, type, row, meta) => meta.row + 1},
            {data: 'tanggal', render: text}, {data: 'merk', render: text},
            {data: 'kondisi', render: value => Number(value) + '%'},
            {data: 'ritase', render: (value, type, row) => '<button class="btn btn-link" type="button" data-bs-toggle="modal" data-bs-target="#filterRitaseModal" data-ritase-url="' + @json(route('statistik.filter-oli.transaksi-ritase', ['log' => 'LOGID'])) .replace('LOGID', Number(row.id)) + '">' + Number(value).toLocaleString('id-ID') + ' rit</button>'},
            {data: null, render: () => @json($category->limit_ritase) + ' rit'}
        ]
    });
});
</script>
@endpush
