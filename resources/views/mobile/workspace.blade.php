@extends('layouts.app')
@section('content')
<div class="container py-4" style="max-width: 880px">
    <a href="{{ route('home') }}" class="btn btn-outline-secondary mb-4">Kembali</a>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <p class="text-uppercase text-success fw-bold small">Aplikasi mobile · Koneksi ruang kerja</p>
            <h1 class="h3 fw-bold">Hubungkan DSGroup Hauling</h1>
            <p class="text-muted">Buka aplikasi, pilih Tambah ruang kerja, lalu pindai QR berikut atau masukkan alamat website.</p>
            <div class="row g-4 align-items-center mt-2">
                <div class="col-md-5 text-center">
                    <canvas id="workspace-qr" class="img-fluid border rounded" aria-label="QR koneksi {{ $identity['name'] }}" role="img" data-workspace-name="{{ $identity['name'] }}" data-connection="{{ json_encode($connection, JSON_UNESCAPED_SLASHES) }}"></canvas>
                    <p id="workspace-qr-status" role="status" class="small text-muted mt-2">Menyiapkan QR…</p>
                </div>
                <div class="col-md-7">
                    <h2 class="h4">{{ $identity['name'] }}</h2>
                    <p>{{ $identity['company_name'] }}</p>
                    <label for="workspace-address" class="form-label fw-bold">Alamat website</label>
                    <input id="workspace-address" class="form-control" readonly value="{{ $baseUrl }}" onclick="this.select()">
                    <p class="small text-muted mt-2">Pastikan nama perusahaan dan alamat ini sama dengan yang muncul di aplikasi sebelum login.</p>
                    <div class="d-flex flex-wrap gap-2 my-3" role="group" aria-label="Unduh dan bagikan koneksi ruang kerja">
                        <button type="button" id="workspace-qr-download" class="btn btn-success" disabled><i class="fa fa-download me-1" aria-hidden="true"></i> Unduh QR</button>
                        <button type="button" id="workspace-qr-share" class="btn btn-outline-success" disabled><i class="fa fa-share-alt me-1" aria-hidden="true"></i> Bagikan</button>
                    </div>
                    <p id="workspace-qr-feedback" class="small text-muted" role="status" aria-live="polite"></p>
                    <p class="small mb-0">QR ini hanya berisi informasi koneksi. Setiap pengguna tetap harus login dengan akun SAIND miliknya.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
@vite('resources/js/workspace-qr.js')
@endpush
