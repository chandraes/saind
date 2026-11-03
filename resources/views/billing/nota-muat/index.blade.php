@extends('layouts.app')
@section('content')
<div class="container py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary btn-sm mb-3"><i class="fa fa-arrow-left me-1" aria-hidden="true"></i> Kembali ke Billing</a>
            <h1 class="h3 fw-bold mb-2">Nota Muat Masuk</h1>
            <p class="text-muted mb-0">Kiriman vendor dari aplikasi mobile untuk seluruh vendor pada SAIND ini.</p>
        </div>
        <span class="badge bg-light text-dark border p-3">{{ number_format($notes->total(), 0, ',', '.') }} nota ditemukan</span>
    </div>
    <div class="alert alert-info">Nota tersimpan sebagai draft. Pengiriman nota belum mencairkan uang jalan.</div>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('billing.nota-muat.index') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="search" class="form-label">Cari nota</label>
                    <input id="search" name="search" type="search" class="form-control @error('search') is-invalid @enderror" value="{{ old('search', $filters['search'] ?? '') }}" maxlength="100" placeholder="Nomor tiket, lambung, atau tambang">
                    @error('search')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
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
            <table class="table table-hover align-middle mb-0">
                <caption class="px-3">Nota terbaru ditampilkan lebih dahulu. Berat dalam ton.</caption>
                <thead class="table-light"><tr>
                    <th scope="col">Tiket / tanggal muat</th><th scope="col">Vendor / unit</th><th scope="col">Tambang</th><th scope="col">Netto (ton)</th><th scope="col">Pengirim / waktu kirim</th><th scope="col">Status</th><th scope="col">Aksi</th>
                </tr></thead>
                <tbody>
                    @forelse($notes as $note)
                    <tr>
                        <td class="text-break"><strong>{{ $note->ticket_number }}</strong><br><small class="text-muted">{{ $note->loaded_on->format('d-m-Y') }}</small></td>
                        <td class="text-break">{{ $note->vendor?->nama ?? '-' }}<br><small class="text-muted">Unit {{ $note->vehicle_number }}</small></td>
                        <td class="text-break">{{ $note->mine_name }}</td>
                        <td class="text-nowrap">{{ number_format($note->net_kg / 1000, 3, ',', '.') }}</td>
                        <td class="text-break">{{ $note->creator?->name ?? '-' }}<br><small class="text-muted">{{ $note->created_at->format('d-m-Y H:i') }}</small></td>
                        <td><span class="badge bg-warning text-dark">{{ $note->status === 'draft' ? 'Draft' : $note->status }}</span></td>
                        <td><a href="{{ route('billing.nota-muat.show', $note) }}" class="btn btn-outline-primary btn-sm" aria-label="Lihat detail nota {{ $note->ticket_number }}">Detail</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">Belum ada nota yang sesuai. Nota akan tampil setelah vendor mengirimkannya dari aplikasi mobile.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($notes->hasPages())
        <div class="card-footer bg-white">{{ $notes->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
</div>
@endsection
