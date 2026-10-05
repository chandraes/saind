@extends('layouts.app')
@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 fw-bold mb-1">Penggantian Filter & Oli Mesin</h1><p class="text-muted mb-0">Pilih unit, catat penggantian, lalu kirim untuk otorisasi.</p></div>
        <div class="d-flex gap-2">
            <a href="{{ route('billing.form-maintenance.filter-oli.confirm') }}" class="btn btn-primary rounded-pill"><i class="fa fa-shopping-cart me-2" aria-hidden="true"></i>Keranjang <span class="badge bg-white text-primary ms-1">{{ $cartItems->count() }}</span></a>
            <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary rounded-pill">Kembali</a>
        </div>
    </div>
    @include('billing.form-maintenance.filter-oli.feedback')
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">Data penggantian</h2>
                    <form method="get" action="{{ route('billing.form-maintenance.filter-oli') }}" class="mb-3">
                        <label for="selectVehicle" class="form-label fw-semibold">Kendaraan</label>
                        <div class="d-flex gap-2">
                            <select id="selectVehicle" name="vehicle_id" class="form-select maintenance-select" required @disabled($cartItems->isNotEmpty())>
                                <option value="">Pilih nomor lambung</option>
                                @foreach ($vehicles as $unit)
                                    <option value="{{ $unit->id }}" @selected($vehicle?->id === $unit->id)>{{ $unit->nomor_lambung }}</option>
                                @endforeach
                            </select>
                            @if ($cartItems->isEmpty())<button class="btn btn-outline-primary" type="submit">Pilih</button>@endif
                        </div>
                    </form>
                    @if ($cartItems->isNotEmpty())
                        <div class="alert alert-warning small">Unit terkunci selama keranjang terisi.</div>
                        <form method="post" action="{{ route('billing.form-maintenance.filter-oli.cart.clear') }}" data-maintenance-form data-confirm="Kosongkan keranjang untuk mengganti unit?">@csrf @method('delete')<button class="btn btn-outline-danger btn-sm mb-3" type="submit">Kosongkan Keranjang</button></form>
                    @endif
                    @if ($vehicle)
                        <form action="{{ route('billing.form-maintenance.filter-oli.cart.add') }}" method="post" data-maintenance-form data-confirm="Tambahkan penggantian ini ke keranjang?" novalidate>
                            @csrf
                            <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                            <div class="mb-3"><label for="category" class="form-label fw-semibold">Kategori</label>
                                <select id="category" name="kategori_filter_oli_mesin_id" class="form-select maintenance-select" required>
                                    <option value="">Pilih kategori</option>
                                    @foreach ($kategori as $category)
                                        <option value="{{ $category->id }}" @selected(old('kategori_filter_oli_mesin_id') == $category->id) @disabled($cartItems->contains('kategori_filter_oli_mesin_id', $category->id))>{{ $category->nama }} · {{ $category->limit_ritase }} rit</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3"><label for="merk" class="form-label fw-semibold">Merek</label><input id="merk" name="merk" class="form-control" maxlength="100" value="{{ old('merk') }}" placeholder="Merek filter atau oli" required></div>
                            <div class="row g-3 mb-4">
                                <div class="col-6"><label for="kondisi" class="form-label fw-semibold">Kondisi awal (%)</label><input id="kondisi" name="kondisi" type="number" class="form-control" min="1" max="100" step="1" value="{{ old('kondisi', 100) }}" required></div>
                                <div class="col-6"><label for="tanggalGanti" class="form-label fw-semibold">Tanggal ganti</label><input id="tanggalGanti" name="tanggal_ganti" type="date" class="form-control" max="{{ today()->format('Y-m-d') }}" value="{{ old('tanggal_ganti', today()->format('Y-m-d')) }}" required></div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill"><i class="fa fa-cart-plus me-2" aria-hidden="true"></i>Tambahkan ke Keranjang</button>
                        </form>
                    @else
                        <p class="text-muted small mb-0">Pilih kendaraan untuk mulai mencatat penggantian.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4"><span class="text-muted small">Unit kendaraan</span><h2 class="h4 fw-bold mb-1">{{ $vehicle?->nomor_lambung ?? 'Belum dipilih' }}</h2><span class="text-muted">Vendor: {{ $vehicle?->vendor?->nama ?? '—' }}</span></div></div>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4"><h2 class="h5 fw-bold mb-1">Penggantian terakhir</h2><p class="text-muted small mb-0">Data penggantian yang sudah disetujui untuk unit ini.</p></div>
                <div class="table-responsive"><table class="table align-middle mb-0"><thead class="table-success"><tr><th>No</th><th>Kategori</th><th>Merek</th><th>Kondisi</th><th>Ritase</th><th>Tanggal Ganti</th></tr></thead><tbody>
                @forelse ($logs as $log)
                    <tr><td>{{ $loop->iteration }}</td><td class="fw-semibold">{{ $log->kategori->nama }}</td><td>{{ $log->merk }}</td><td>{{ $log->kondisi }}%</td><td>{{ number_format((float) $log->ritase, 1, ',', '.') }} rit</td><td>{{ $log->created_at->format('d-m-Y') }}</td></tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">Belum ada histori penggantian untuk unit ini.</td></tr>
                @endforelse
                </tbody></table></div>
            </div>
        </div>
    </div>
</div>
@endsection
