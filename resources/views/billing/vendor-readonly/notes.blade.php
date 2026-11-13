@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center"><div class="col-md-12 text-center"><h1><u>{{ $title }}</u></h1><h2><u>{{ $vendorName }}</u></h2></div></div>
    <div class="d-flex flex-wrap gap-4 align-items-center mt-3 mb-4">
        <a href="{{ route('home') }}" class="d-inline-flex align-items-center gap-2"><img src="{{ asset('images/dashboard.svg') }}" alt="" width="30">Dashboard</a>
        <a href="{{ route('vendor-operational.nota-bayar', $vendorQuery) }}" class="d-inline-flex align-items-center gap-2"><img src="{{ asset('images/back.svg') }}" alt="" width="30">Kembali</a>
        @if ($vendorQuery)<a href="{{ route('vendor-operational.nota-bayar') }}" class="d-inline-flex align-items-center gap-2"><i class="fa fa-exchange" aria-hidden="true"></i>Ganti vendor</a>@endif
    </div>
    @php
        $activeRoute = $filters['rute_id'] ? $routes->firstWhere('id', $filters['rute_id']) : null;
        $filtered = $filters['search'] !== '' || $activeRoute;
    @endphp
    {{-- Cari (teks bebas, tekan Cari/Enter) dan Filter rute (langsung diterapkan) dipisah; tinggi kontrol seragam. --}}
    <form method="GET" id="note-filter" class="card shadow-sm border-0 mb-3 note-filter" role="search" aria-label="Cari dan filter nota bayar">
        <div class="card-body">
            <input type="hidden" name="kind" value="{{ $filters['kind'] }}">
            @foreach ($vendorQuery as $name => $value)<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endforeach
            @if ($filters['sort'])<input type="hidden" name="sort" value="{{ $filters['sort'] }}"><input type="hidden" name="dir" value="{{ $filters['dir'] }}">@endif
            <div class="row g-3 align-items-end">
                <div class="col-lg-6">
                    <label class="form-label small fw-semibold text-muted text-uppercase mb-1" for="search"><i class="fa fa-search me-1" aria-hidden="true"></i>Cari</label>
                    <div class="input-group">
                        <input id="search" name="search" type="search" class="form-control" value="{{ $filters['search'] }}" maxlength="100" placeholder="Nomor nota, nolam, atau nopol">
                        <button type="submit" class="btn btn-primary px-4">Cari</button>
                    </div>
                </div>
                <div class="col-lg-4 ms-lg-auto">
                    <label class="form-label small fw-semibold text-muted text-uppercase mb-1" for="rute_id"><i class="fa fa-filter me-1" aria-hidden="true"></i>Filter rute</label>
                    <select id="rute_id" name="rute_id" class="form-select readonly-select" data-auto-submit>
                        <option value="">Semua rute</option>
                        @foreach ($routes as $route)<option value="{{ $route->id }}" @selected($filters['rute_id'] === $route->id)>{{ $route->nama }}</option>@endforeach
                    </select>
                </div>
                <div class="col-lg-auto">
                    <a class="btn btn-outline-secondary w-100 {{ $filtered ? '' : 'disabled' }}" @if (!$filtered) aria-disabled="true" tabindex="-1" @endif href="{{ route('vendor-operational.nota-bayar', ['kind' => $filters['kind']] + $vendorQuery) }}"><i class="fa fa-undo me-1" aria-hidden="true"></i>Reset</a>
                </div>
            </div>
            @if ($filtered)
            <div class="d-flex flex-wrap gap-2 mt-3 small" aria-label="Filter aktif">
                @if ($filters['search'] !== '')<span class="badge rounded-pill text-bg-light border fw-normal py-2 px-3"><i class="fa fa-search me-1" aria-hidden="true"></i>"{{ $filters['search'] }}"</span>@endif
                @if ($activeRoute)<span class="badge rounded-pill text-bg-light border fw-normal py-2 px-3"><i class="fa fa-filter me-1" aria-hidden="true"></i>{{ $activeRoute->nama }}</span>@endif
            </div>
            @endif
        </div>
    </form>
    @if ($errors->any())<p class="text-danger">{{ $errors->first() }}</p>@endif
</div>
<div class="container-fluid mt-3">
    @include('billing.vendor-readonly.table', ['showAction' => false])
</div>
@include('billing.vendor-readonly.modal')
@endsection
@include('billing.vendor-readonly.scripts')
@push('css')
<style>
/* Select2 setinggi input Bootstrap agar Cari dan Filter rute sejajar. */
.note-filter .select2-container .select2-selection--single{height:auto;padding:.375rem 0;border:1px solid var(--bs-border-color,#ced4da);border-radius:var(--bs-border-radius,.375rem);display:flex;align-items:center}
.note-filter .select2-container .select2-selection--single .select2-selection__rendered{line-height:var(--bs-body-line-height,1.5);padding-left:.75rem;padding-right:2rem}
.note-filter .select2-container .select2-selection--single .select2-selection__arrow{height:100%;right:.5rem}
.note-filter .select2-container--focus .select2-selection--single,.note-filter .select2-container--open .select2-selection--single{border-color:#86b7fe;box-shadow:0 0 0 .25rem rgba(13,110,253,.25)}
</style>
@endpush
@push('js')
<script>
// Filter rute langsung diterapkan saat dipilih (Select2 memicu event change jQuery).
document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('rute_id');
    if (!select) return;
    const apply = () => select.form.requestSubmit();
    window.jQuery ? jQuery(select).on('change', apply) : select.addEventListener('change', apply);
});
</script>
@endpush
