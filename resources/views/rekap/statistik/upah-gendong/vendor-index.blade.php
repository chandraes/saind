@extends('layouts.app')
@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap gap-3 justify-content-between mb-4"><div><h1 class="h3">Statistik Upah Gendong</h1><p class="text-muted">Kendaraan {{ $ug->vehicle->nomor_lambung }} · {{ $ug->nama_driver }} · Pengurus {{ $ug->nama_pengurus }}</p></div><a href="{{ route('home') }}" class="btn btn-outline-secondary align-self-start">Dashboard</a></div>
    <a href="{{ route('vendor-operational.statistics.vehicles', ['report' => 'upah-gendong']) }}" class="btn btn-outline-primary mb-3"><i class="fa fa-exchange me-2" aria-hidden="true"></i>Ganti kendaraan</a>
    <div class="card mb-4"><div class="card-body">
        <p>Bank {{ $ug->bank }} · Rekening {{ $ug->nama_rek }} · {{ $ug->no_rek }} · Minimum {{ $ug->tonase_min }} ton · Upah Rp{{ number_format($ug->nominal, 0, ',', '.') }}/ton</p>
        <form method="get" class="row g-3">
            <input type="hidden" name="vehicle_id" value="{{ $vehicle }}">
            <div class="col-md-3"><label class="form-label" for="vendorMonth">Bulan</label><select id="vendorMonth" class="form-select" name="bulan">@for ($month = 1; $month <= 12; $month++)<option value="{{ $month }}" @selected($month == $bulan)>{{ \Carbon\Carbon::createFromDate(2000, $month, 1)->locale('id')->monthName }}</option>@endfor</select></div>
            <div class="col-md-3"><label class="form-label" for="vendorYear">Tahun</label><select id="vendorYear" class="form-select" name="tahun">@foreach ($dataTahun->pluck('tahun')->push($tahun)->unique()->sortDesc() as $year)<option value="{{ $year }}" @selected($year == $tahun)>{{ $year }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label" for="vendorRange">Rentang tanggal (opsional)</label><input id="vendorRange" class="form-control" name="tanggal_filter" value="{{ $tanggal_filter }}" placeholder="Pilih rentang tanggal" readonly></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100" type="submit">Tampilkan</button></div>
        </form>
    </div></div>
    <div class="table-responsive"><table id="vendorUpahTable" class="table table-bordered align-middle"><thead class="table-success"><tr><th>No</th><th>Hari</th><th>Tanggal</th><th>Rute</th><th>Jarak (km)</th><th>Tonase &lt;50 km</th><th>Tonase ≥50 km</th><th>Kelebihan tonase</th><th>Upah gendong</th></tr></thead><tbody></tbody><tfoot><tr><th colspan="4">Grand Total</th><th>{{ number_format($summary->jarak, 1, ',', '.') }}</th><th>{{ number_format($summary->under_50, 2, ',', '.') }}</th><th>{{ number_format($summary->over_50, 2, ',', '.') }}</th><th>{{ number_format($summary->kelebihan, 2, ',', '.') }}</th><th>Rp{{ number_format($summary->nominal, 0, ',', '.') }}</th></tr></tfoot></table></div>
</div>
@endsection
@push('css')
<link href="{{ asset('assets/css/dt.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/js/flatpickr/flatpickr.min.css') }}" rel="stylesheet">
@endpush
@push('js')
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/flatpickr/flatpickr.js') }}"></script>
@once('incremental-data-table')
<script src="{{ asset('assets/js/incremental-data-table.js') }}?v={{ filemtime(public_path('assets/js/incremental-data-table.js')) }}"></script>
@endonce
<script src="{{ asset('assets/js/vendor-statistics-table.js') }}?v={{ filemtime(public_path('assets/js/vendor-statistics-table.js')) }}"></script>
<script>
$(function () {
    $('#vendorMonth, #vendorYear').select2({width: '100%'});
    flatpickr('#vendorRange', {mode: 'range', dateFormat: 'd-m-Y'});
    const text = $.fn.dataTable.render.text();
    vendorStatisticsTable('#vendorUpahTable', {url: @json(request()->fullUrl()), data: {}, columns: [
        {data: null, render: (data, type, row, meta) => meta.row + 1},
        {data: 'hari', render: text}, {data: 'id_tanggal', render: text}, {data: 'rute', render: text}, {data: 'jarak', render: text},
        {data: 'under_50', render: text}, {data: 'over_50', render: text}, {data: 'kelebihan', render: text},
        {data: 'nominal', render: value => 'Rp' + Number(value).toLocaleString('id-ID')}
    ]});
});
</script>
@endpush
