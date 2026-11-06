@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card shadow-sm mx-auto" style="max-width: 640px">
        <div class="card-body p-4">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary mb-3"><i class="fa fa-arrow-left me-2" aria-hidden="true"></i>Dashboard</a>
            <h1 class="h4 fw-bold">Statistik {{ ucwords(str_replace('-', ' ', $report)) }}</h1>
            <p class="text-muted">Pilih kendaraan vendor Anda untuk melihat statistik.</p>
            <form method="get" action="{{ route('statistik.'.$report) }}">
                <label for="statisticsVehicle" class="form-label">Nomor lambung</label>
                <select id="statisticsVehicle" name="vehicle_id" class="form-select" required aria-label="Nomor lambung"></select>
                <button type="submit" class="btn btn-primary mt-3"><i class="fa fa-bar-chart me-2" aria-hidden="true"></i>Lihat statistik</button>
            </form>
        </div>
    </div>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.min.css') }}">
@endpush
@push('js')
<script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
<script>
$(function () {
    $('#statisticsVehicle').select2({
        width: '100%', placeholder: 'Cari nomor lambung',
        ajax: {
            url: @json(route('vendor-operational.statistics.vehicles', ['report' => $report])),
            dataType: 'json', delay: 250,
            data: params => ({ q: params.term || '', page: params.page || 1 }),
            processResults: data => data
        }
    });
});
</script>
@endpush
