@extends('layouts.app')

@section('content')
<div class="container pb-5">
    <div class="row justify-content-center mb-4 mt-3">
        <div class="col-md-8 text-center">
            <div class="p-4 bg-white rounded-4 shadow-sm border-top border-primary border-4">
                <p class="text-muted text-uppercase fw-bold small mb-1"><i class="fa fa-file-invoice me-2"></i>Nota Bayar</p>
                <h2 class="fw-bold text-dark mb-0">{{$vendor->nama}}</h2>
            </div>
        </div>
    </div>

    <div class="row g-4 justify-content-center mt-2">
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{route('transaksi.nota-bayar', ['vendor' => $vendor->id])}}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm text-center menu-card py-4">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="icon-wrapper mb-3">
                            <img src="{{asset('images/hpp.svg')}}" alt="HPP" width="60">
                        </div>
                        <h6 class="fw-bold text-secondary mb-0">HPP</h6>
                    </div>
                </div>
            </a>
        </div>

        @php $isDisabledKompensasi = empty($kompensasi_jr) || $kompensasi_jr <= 0; @endphp
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ $isDisabledKompensasi ? 'javascript:void(0)' : route('billing.nota-bayar.detail-jenis', ['vendor' => $vendor->id, 'jenis' => 'kompensasi_jr']) }}"
               class="text-decoration-none {{ $isDisabledKompensasi ? 'disabled-card' : '' }}">
                <div class="card h-100 border-0 shadow-sm text-center menu-card py-4 position-relative">

                    @if(!$isDisabledKompensasi)
                    <span class="position-absolute badge rounded-pill bg-danger shadow-sm" style="top: 15px; right: 15px; font-size: 0.8rem;">
                        {{ $kompensasi_jr }}
                    </span>
                    @endif

                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="icon-wrapper mb-3">
                            <img src="{{asset('images/kompensasi-jr.svg')}}" alt="Kompensasi JR" width="60">
                        </div>
                        <h6 class="fw-bold text-secondary mb-0">KOMPENSASI<br>JALAN RUSAK</h6>
                    </div>
                </div>
            </a>
        </div>

        @php $isDisabledBbm = empty($penyesuaian_bbm) || $penyesuaian_bbm <= 0; @endphp
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ $isDisabledBbm ? 'javascript:void(0)' : route('billing.nota-bayar.detail-jenis', ['vendor' => $vendor->id, 'jenis' => 'penyesuaian_bbm']) }}"
               class="text-decoration-none {{ $isDisabledBbm ? 'disabled-card' : '' }}">
                <div class="card h-100 border-0 shadow-sm text-center menu-card py-4 position-relative">

                    @if(!$isDisabledBbm)
                    <span class="position-absolute badge rounded-pill bg-danger shadow-sm" style="top: 15px; right: 15px; font-size: 0.8rem;">
                        {{ $penyesuaian_bbm }}
                    </span>
                    @endif

                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="icon-wrapper mb-3">
                            <img src="{{asset('images/penyesuaian-bbm.svg')}}" alt="Penyesuaian BBM" width="60">
                        </div>
                        <h6 class="fw-bold text-secondary mb-0">PENYESUAIAN<br>BBM</h6>
                    </div>
                </div>
            </a>
        </div>

        @php $isDisabledAch = empty($achievement) || $achievement <= 0; @endphp
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ $isDisabledAch ? 'javascript:void(0)' : route('billing.nota-bayar.detail-jenis', ['vendor' => $vendor->id, 'jenis' => 'achievement']) }}"
               class="text-decoration-none {{ $isDisabledAch ? 'disabled-card' : '' }}">
                <div class="card h-100 border-0 shadow-sm text-center menu-card py-4 position-relative">

                    @if(!$isDisabledAch)
                    <span class="position-absolute badge rounded-pill bg-danger shadow-sm" style="top: 15px; right: 15px; font-size: 0.8rem;">
                        {{ $achievement }}
                    </span>
                    @endif

                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="icon-wrapper mb-3">
                            <img src="{{asset('images/achievement.svg')}}" alt="Achievement" width="60">
                        </div>
                        <h6 class="fw-bold text-secondary mb-0">ACHIEVEMENT</h6>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{in_array(auth()->user()->role, ['su', 'admin']) ? route('billing.index') : route('home')}}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm text-center menu-card py-4 bg-light">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="icon-wrapper mb-3">
                            <img src="{{asset('images/back.svg')}}" alt="Kembali" width="60">
                        </div>
                        <h6 class="fw-bold text-secondary mb-0">KEMBALI</h6>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    body { background-color: #f8fafc; }

    .menu-card {
        border-radius: 16px;
        transition: all 0.3s ease-in-out;
        cursor: pointer;
    }

    .menu-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1) !important;
    }

    .menu-card:hover h6 {
        color: #0d6efd !important;
    }

    .icon-wrapper {
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* ====== TAMBAHAN CLASS UNTUK MENU KOSONG ====== */
    .disabled-card {
        opacity: 0.45; /* Membuatnya terlihat transparan/pudar */
        cursor: not-allowed; /* Mengubah kursor jadi tanda dilarang */
        pointer-events: none; /* Mematikan semua interaksi klik/hover */
        filter: grayscale(100%); /* Membuat gambar SVG dan teks jadi abu-abu total */
    }
</style>
@endpush
