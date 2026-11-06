@extends('layouts.app')
@section('content')
<div class="container pb-5">
    <div class="row justify-content-center mb-4 mt-3"><div class="col-md-8 text-center"><div class="p-4 bg-white rounded-4 shadow-sm border-top border-primary border-4">
        <p class="text-muted text-uppercase fw-bold small mb-1"><i class="fa fa-file-invoice me-2" aria-hidden="true"></i>Nota Bayar</p><h2 class="fw-bold text-dark mb-0">{{ $vendorName }}</h2>
    </div></div></div>
    <div class="row g-4 justify-content-center mt-2">
        @foreach (['utama' => ['HPP', 'hpp.svg'], 'kompensasi_jr' => ['KOMPENSASI JALAN RUSAK', 'kompensasi-jr.svg'], 'penyesuaian_bbm' => ['PENYESUAIAN BBM', 'penyesuaian-bbm.svg'], 'achievement' => ['ACHIEVEMENT', 'achievement.svg']] as $kind => [$label, $icon])
            @php($disabled = $kind !== 'utama' && !$counts[$kind])
            <div class="col-6 col-md-4 col-lg-2"><a href="{{ $disabled ? '#' : route('vendor-operational.nota-bayar', ['kind' => $kind]) }}" class="text-decoration-none {{ $disabled ? 'disabled-card' : '' }}" @if ($disabled) aria-disabled="true" tabindex="-1" @endif>
                <div class="card h-100 border-0 shadow-sm text-center menu-card py-4 position-relative">
                    @if ($kind !== 'utama' && !$disabled)<span class="position-absolute badge rounded-pill bg-danger shadow-sm" style="top: 15px; right: 15px">{{ $counts[$kind] }}</span>@endif
                    <div class="card-body d-flex flex-column align-items-center justify-content-center"><div class="icon-wrapper mb-3"><img src="{{ asset('images/'.$icon) }}" alt="" width="60"></div><h6 class="fw-bold text-secondary mb-0">{{ $label }}</h6></div>
                </div>
            </a></div>
        @endforeach
        <div class="col-6 col-md-4 col-lg-2"><a href="{{ route('home') }}" class="text-decoration-none"><div class="card h-100 border-0 shadow-sm text-center menu-card py-4 bg-light"><div class="card-body d-flex flex-column align-items-center justify-content-center"><div class="icon-wrapper mb-3"><img src="{{ asset('images/back.svg') }}" alt="" width="60"></div><h6 class="fw-bold text-secondary mb-0">KEMBALI</h6></div></div></a></div>
    </div>
</div>
@endsection
@include('billing.nota-bayar.menu-styles')
