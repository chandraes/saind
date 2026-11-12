@extends('layouts.app')
@section('content')
<div class="container py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="{{ $canReview ? route('billing.index') : route('home') }}" class="btn btn-outline-secondary btn-sm mb-3"><i class="fa fa-arrow-left me-1" aria-hidden="true"></i> Kembali</a>
            <h1 class="h3 fw-bold mb-2">Nota Muat Masuk</h1>
            <p class="text-muted mb-0">{{ $canReview ? 'Kiriman vendor dari aplikasi mobile untuk seluruh vendor pada SAIND ini.' : ($canFilterVendor ? 'Pantau kiriman dan progres nota seluruh vendor. Halaman ini hanya untuk melihat.' : 'Pantau kiriman dan progres nota vendor Anda. Halaman ini hanya untuk melihat.') }}</p>
        </div>
        <span class="badge bg-light text-dark border p-3">{{ number_format($notes->total(), 0, ',', '.') }} nota ditemukan</span>
    </div>
    <div class="alert alert-info">Pengiriman nota dari aplikasi mobile tidak otomatis mencairkan uang jalan. Pantau status dan riwayat pemeriksaan pada detail nota.</div>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('billing.nota-muat.index') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="search" class="form-label">Cari nota</label>
                    <input id="search" name="search" type="search" class="form-control @error('search') is-invalid @enderror" value="{{ old('search', $filters['search'] ?? '') }}" maxlength="100" placeholder="Nomor tiket, lambung, atau tambang">
                    @error('search')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                @if ($canFilterVendor)
                <div class="col-md-4">
                    <label for="vendor_id" class="form-label">Vendor</label>
                    <select id="vendor_id" name="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror">
                        <option value="">Seluruh vendor</option>
                        @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" @selected((string) old('vendor_id', $filters['vendor_id'] ?? '') === (string) $vendor->id)>{{ $vendor->nama }}</option>
                        @endforeach
                    </select>
                    @error('vendor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                @endif
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Terapkan</button>
                    <a href="{{ route('billing.nota-muat.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
            @error('page')<p class="text-danger mt-2 mb-0">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table id="note-inbox-table" class="table table-bordered table-hover align-middle mb-0">
                <caption class="px-3">Nota terbaru ditampilkan lebih dahulu. Berat dalam ton.</caption>
                <thead class="table-success"><tr>
                    <th scope="col" class="text-center text-nowrap" style="width: 48px">No.</th><th scope="col">Tiket / tanggal muat</th><th scope="col">Vendor / unit</th><th scope="col">Tambang</th><th scope="col">Netto (ton)</th><th scope="col">Pengirim / waktu kirim</th><th scope="col">Status</th><th scope="col">Aksi</th>
                </tr></thead>
                <tbody>
                    @include('billing.nota-muat.rows')
                </tbody>
            </table>
        </div>
        <div id="note-inbox-loading" class="card-footer bg-white text-center" role="status" data-next="{{ $notes->nextPageUrl() }}">{{ $notes->hasMorePages() ? 'Memuat nota berikutnya saat Anda menggulir…' : 'Seluruh nota telah ditampilkan.' }}</div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
@once('incremental-data-table')
<script src="{{ asset('assets/js/incremental-data-table.js') }}?v={{ filemtime(public_path('assets/js/incremental-data-table.js')) }}"></script>
@endonce
<script src="{{ asset('assets/js/load-note-inbox.js') }}?v={{ filemtime(public_path('assets/js/load-note-inbox.js')) }}"></script>
@endpush
@push('css')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.min.css') }}">
@endpush
