@extends('layouts.app')
@if ($report === 'upah-gendong')
@include('database.upah-gendong.styles')
@endif
@section('content')
<div @class(['container py-4', 'upah-workspace upah-narrow' => $report === 'upah-gendong'])>
    <div @class(['card mx-auto', 'upah-panel' => $report === 'upah-gendong', 'shadow-sm' => $report !== 'upah-gendong']) style="max-width: 640px">
        <div class="card-body p-4">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary mb-3"><i class="fa fa-arrow-left me-2" aria-hidden="true"></i>Dashboard</a>
            @if ($report === 'upah-gendong')<span class="upah-icon d-grid mb-3"><i class="fa fa-truck" aria-hidden="true"></i></span>@endif
            <h1 class="h4 fw-bold">Statistik {{ ucwords(str_replace('-', ' ', $report)) }}</h1>
            <p class="text-muted">{{ $vendors->isNotEmpty() ? 'Pilih nolam dari seluruh vendor untuk melihat statistik.' : 'Pilih kendaraan vendor Anda untuk melihat statistik.' }}</p>
            <form method="get" action="{{ route('statistik.'.$report) }}">
                @if ($vendors->isNotEmpty())
                <div class="mb-3">
                    <label for="statisticsVendor" class="form-label">Vendor</label>
                    <select id="statisticsVendor" class="form-select" aria-label="Vendor">
                        <option value="">Seluruh vendor</option>
                        @foreach ($vendors as $option)<option value="{{ $option->id }}">{{ $option->nama }}@if ($option->nickname) ({{ $option->nickname }})@endif</option>@endforeach
                    </select>
                </div>
                @endif
                <label for="statisticsVehicle" class="form-label">Nolam</label>
                <select id="statisticsVehicle" name="vehicle_id" class="form-select" required aria-label="Nomor lambung"></select>
                <div class="d-flex flex-wrap gap-2 mt-3 upah-action-row">
                    <button type="submit" @class(['btn', 'btn-success upah-primary' => $report === 'upah-gendong', 'btn-primary' => $report !== 'upah-gendong'])><i class="fa fa-bar-chart me-2" aria-hidden="true"></i>{{ $report === 'upah-gendong' ? 'Lihat hasil' : 'Lihat statistik' }}</button>
                    @if ($report === 'upah-gendong' && auth()->user()->role === 'vendor')
                        <a href="{{ route('database.upah-gendong') }}" class="btn btn-outline-secondary upah-primary"><i class="fa fa-cog me-2" aria-hidden="true"></i>Pengaturan Upah Gendong</a>
                    @endif
                </div>
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
            data: params => ({ q: params.term || '', page: params.page || 1, vendor_id: $('#statisticsVendor').val() || undefined }),
            processResults: data => data
        }
    });
    $('#statisticsVendor').select2({width: '100%'}).on('change', () => $('#statisticsVehicle').val(null).trigger('change'));
});
</script>
@endpush
