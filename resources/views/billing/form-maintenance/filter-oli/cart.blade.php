@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h1 class="h3 fw-bold">Keranjang Filter & Oli Mesin</h1><p class="text-muted mb-0">Periksa rincian dan tanggal ganti sebelum mengirim untuk otorisasi.</p></div><a href="{{ route('billing.form-maintenance.filter-oli') }}" class="btn btn-outline-primary rounded-pill">Tambah Item</a></div>
    @include('billing.form-maintenance.filter-oli.feedback')
    <div class="card bg-primary text-white border-0 rounded-4 mb-4"><div class="card-body p-4"><span class="small">Unit kendaraan</span><h2 class="h4 fw-bold mb-1">{{ $vehicle->nomor_lambung }}</h2><span>Vendor: {{ $vehicle->vendor?->nama ?? '—' }}</span></div></div>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-4 d-flex justify-content-between align-items-center"><h2 class="h5 fw-bold mb-0">Rincian penggantian</h2><form method="post" action="{{ route('billing.form-maintenance.filter-oli.cart.clear') }}" data-maintenance-form data-confirm="Kosongkan seluruh keranjang?">@csrf @method('delete')<button class="btn btn-outline-danger btn-sm rounded-pill" type="submit">Kosongkan Keranjang</button></form></div>
        <div class="table-responsive"><table class="table align-middle mb-0"><thead class="table-success"><tr><th>No</th><th>Kategori</th><th>Merek</th><th>Kondisi</th><th>Tanggal Ganti</th><th>Action</th></tr></thead><tbody>
        @foreach ($cartItems as $item)
            <tr><td>{{ $loop->iteration }}</td><td class="fw-semibold">{{ $item->kategori->nama }}</td><td>{{ $item->merk }}</td><td>{{ $item->kondisi }}%</td><td><label for="date{{ $item->id }}" class="visually-hidden">Tanggal ganti {{ $item->kategori->nama }}</label><input id="date{{ $item->id }}" form="checkoutForm" type="date" name="items[{{ $item->id }}][tanggal_ganti]" value="{{ old('items.'.$item->id.'.tanggal_ganti', $item->created_at->format('Y-m-d')) }}" max="{{ today()->format('Y-m-d') }}" class="form-control" required></td><td><form method="post" action="{{ route('billing.form-maintenance.filter-oli.cart.delete', $item->id) }}" data-maintenance-form data-confirm="Hapus item ini dari keranjang?">@csrf @method('delete')<button type="submit" class="btn btn-outline-danger btn-sm" aria-label="Hapus {{ $item->kategori->nama }}"><i class="fa fa-trash" aria-hidden="true"></i></button></form></td></tr>
        @endforeach
        </tbody></table></div>
        <div class="card-body p-4 bg-light">
            <form id="checkoutForm" method="post" action="{{ route('billing.form-maintenance.filter-oli.checkout') }}" data-maintenance-form data-confirm="Kirim penggantian filter & oli mesin untuk otorisasi?" novalidate>
                @csrf
                <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                <h2 class="h5 fw-bold mb-3">Informasi pembayaran</h2>
                <div class="mb-3"><label for="pembayaran" class="form-label fw-semibold">Metode pembayaran</label><select id="pembayaran" name="pembayaran" class="form-select maintenance-select" data-payment-method required><option value="dibayar_sendiri" @selected(old('pembayaran') === 'dibayar_sendiri')>Dibayar Sendiri</option>@if ($kasBesarEnabled)<option value="kas_besar" @selected(old('pembayaran') === 'kas_besar')>Kas Besar</option>@endif</select></div>
                <div data-bank-fields class="row g-3 mb-4 d-none" hidden>
                    <div class="col-md-6"><label for="nominal" class="form-label">Total nominal (Rp)</label><input id="nominal" type="number" name="total_nominal" min="1" max="9999999999999" step="1" value="{{ old('total_nominal') }}" class="form-control"></div>
                    <div class="col-md-6"><label for="bank" class="form-label">Bank tujuan</label><input id="bank" name="nama_bank" maxlength="50" value="{{ old('nama_bank') }}" class="form-control"></div>
                    <div class="col-md-6"><label for="rekening" class="form-label">Nomor rekening</label><input id="rekening" name="nomor_rekening" maxlength="50" value="{{ old('nomor_rekening') }}" class="form-control"></div>
                    <div class="col-md-6"><label for="atasNama" class="form-label">Atas nama</label><input id="atasNama" name="nama_rekening" maxlength="100" value="{{ old('nama_rekening') }}" class="form-control"></div>
                </div>
                <div class="d-flex justify-content-between gap-3 mt-4"><a href="{{ route('billing.form-maintenance.filter-oli') }}" class="btn btn-outline-secondary rounded-pill">Kembali</a><button type="submit" class="btn btn-success rounded-pill px-4">Kirim untuk Otorisasi</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
