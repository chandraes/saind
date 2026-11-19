@extends('layouts.app')
@include('database.upah-gendong.styles')

@section('content')
<div class="container upah-workspace upah-narrow py-4">
    <a href="{{ route('statistik.upah-gendong') }}" class="upah-back d-inline-block mb-3">
        <i class="fa fa-arrow-left me-2" aria-hidden="true"></i>Pilih kendaraan lain
    </a>
    <section class="upah-panel p-3 p-md-4" aria-labelledby="upah-status-title">
        <div class="d-flex align-items-center gap-3 mb-3">
            <span class="upah-icon flex-shrink-0"><i class="fa fa-truck" aria-hidden="true"></i></span>
            <div>
                <p class="small text-muted mb-0">No. Lambung</p>
                <p class="fw-bold fs-5 mb-0">{{ $vehicle->nomor_lambung }}</p>
            </div>
        </div>
        <h1 id="upah-status-title" class="mb-2">Upah Gendong belum diaktifkan</h1>
        @if (auth()->user()->role === 'vendor')
            <p class="text-muted">Aktifkan pengaturan kendaraan ini agar hasil Upah Gendong dapat ditampilkan.</p>
            <a href="{{ route('database.upah-gendong') }}" class="btn btn-success upah-primary"><i class="fa fa-cog me-2" aria-hidden="true"></i>Pengaturan Upah Gendong</a>
        @else
            <p class="text-muted mb-0">Hubungi pengelola Upah Gendong untuk pengaturan kendaraan ini.</p>
        @endif
    </section>
</div>
@endsection
