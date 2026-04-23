@extends('layouts.app')

@section('content')
<div class="container pb-5">
    <div class="row justify-content-center mb-4 mt-3">
        <div class="col-md-8 text-center">
            <div class="p-4 bg-white rounded-4 shadow-sm border-top border-primary border-4">
                <p class="text-muted text-uppercase fw-bold small mb-1"><i class="fa fa-file-invoice me-2"></i>Nota Tagihan</p>
                <h2 class="fw-bold text-dark mb-0">{{$customer->nama}}</h2>
            </div>
        </div>
    </div>

    <div class="row g-4 justify-content-center mt-2">
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{route('transaksi.nota-tagihan', $customer->id)}}" class="text-decoration-none">
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
        @if ($customer->is_kompensasi_jr)
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{route('billing.nota-tagihan.detail-jenis', ['customer' => $customer->id, 'jenis' => 'kompensasi_jr'])}}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm text-center menu-card py-4">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="icon-wrapper mb-3">
                            <img src="{{asset('images/kompensasi-jr.svg')}}" alt="Kompensasi JR" width="60">
                        </div>
                        <h6 class="fw-bold text-secondary mb-0">KOMPENSASI<br>JALAN RUSAK</h6>
                    </div>
                </div>
            </a>
        </div>
        @endif
        @if ($customer->is_penyesuaian_bbm)
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{route('billing.nota-tagihan.detail-jenis', ['customer' => $customer->id, 'jenis' => 'penyesuaian_bbm'])}}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm text-center menu-card py-4">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="icon-wrapper mb-3">
                            <img src="{{asset('images/penyesuaian-bbm.svg')}}" alt="Penyesuaian BBM" width="60">
                        </div>
                        <h6 class="fw-bold text-secondary mb-0">PENYESUAIAN<br>BBM</h6>
                    </div>
                </div>
            </a>
        </div>
        @endif
        @if ($customer->is_achievement)
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{route('billing.nota-tagihan.detail-jenis', ['customer' => $customer->id, 'jenis' => 'achievement'])}}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm text-center menu-card py-4">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="icon-wrapper mb-3">
                            <img src="{{asset('images/achievement.svg')}}" alt="Achievement" width="60">
                        </div>
                        <h6 class="fw-bold text-secondary mb-0">ACHIEVEMENT</h6>
                    </div>
                </div>
            </a>
        </div>
        @endif
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{route('billing.index')}}" class="text-decoration-none">
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
    /* Styling untuk efek interaktif pada Card */
    body { background-color: #f8fafc; }

    .menu-card {
        border-radius: 16px;
        transition: all 0.3s ease-in-out;
        cursor: pointer;
    }

    /* Efek melayang saat kursor menyentuh card */
    .menu-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1) !important;
    }

    /* Mengubah warna teks saat di-hover */
    .menu-card:hover h6 {
        color: #0d6efd !important; /* Warna biru primary Bootstrap */
    }

    .icon-wrapper {
        height: 70px; /* Menjaga ukuran tinggi icon tetap sama rata */
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush
