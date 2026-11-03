@extends('layouts.app')
@section('content')
<div class="container py-3">
    <a href="{{ route('billing.nota-muat.index') }}" class="btn btn-outline-secondary btn-sm mb-3"><i class="fa fa-arrow-left me-1" aria-hidden="true"></i> Kembali ke nota masuk</a>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 fw-bold text-break mb-2">{{ $note->ticket_number }}</h1><p class="text-muted mb-0">Detail nota muat kiriman vendor</p></div>
        <span class="badge bg-warning text-dark p-2">{{ $note->status === 'draft' ? 'Draft' : $note->status }}</span>
    </div>
    <div class="alert alert-info">Nota ini belum mencairkan uang jalan. Data dan foto ditampilkan sesuai kiriman vendor.</div>
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold py-3">Data nota</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Vendor</dt><dd class="col-sm-7 text-break">{{ $note->vendor?->nama ?? '-' }}</dd>
                        <dt class="col-sm-5">Unit</dt><dd class="col-sm-7 text-break">{{ $note->vehicle_number }}</dd>
                        <dt class="col-sm-5">Tambang</dt><dd class="col-sm-7 text-break">{{ $note->mine_name }}</dd>
                        <dt class="col-sm-5">Tanggal muat</dt><dd class="col-sm-7">{{ $note->loaded_on->format('d-m-Y') }}</dd>
                        <dt class="col-sm-5">Pengemudi</dt><dd class="col-sm-7 text-break">{{ $note->driver_name ?: '-' }}</dd>
                        <dt class="col-sm-5">Gross (ton)</dt><dd class="col-sm-7">{{ number_format($note->gross_kg / 1000, 3, ',', '.') }}</dd>
                        <dt class="col-sm-5">Tare (ton)</dt><dd class="col-sm-7">{{ number_format($note->tare_kg / 1000, 3, ',', '.') }}</dd>
                        <dt class="col-sm-5">Netto (ton)</dt><dd class="col-sm-7 fw-bold">{{ number_format($note->net_kg / 1000, 3, ',', '.') }}</dd>
                        <dt class="col-sm-5">Pengirim</dt><dd class="col-sm-7 text-break">{{ $note->creator?->name ?? '-' }}</dd>
                        <dt class="col-sm-5">Dikirim pada</dt><dd class="col-sm-7">{{ $note->created_at->format('d-m-Y H:i') }}</dd>
                    </dl>
                    <hr><h2 class="h6 fw-bold">Catatan</h2><p class="text-break mb-0" style="white-space: pre-wrap">{{ $note->notes ?: '-' }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
                    <h2 class="h6 fw-bold mb-0">Foto nota</h2>
                    <a href="{{ route('billing.nota-muat.photo', $note) }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">Buka foto ukuran penuh <span class="visually-hidden">di tab baru</span></a>
                </div>
                <div class="card-body bg-light text-center">
                    <img src="{{ route('billing.nota-muat.photo', $note) }}" alt="Foto nota muat {{ $note->ticket_number }}" class="img-fluid rounded" style="max-height: 900px; object-fit: contain">
                </div>
                <div class="card-footer bg-white text-muted small">Jika foto tidak tampil, buka foto ukuran penuh untuk memeriksa ketersediaannya.</div>
            </div>
        </div>
    </div>
</div>
@endsection
