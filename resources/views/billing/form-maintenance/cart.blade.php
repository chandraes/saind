@extends('layouts.app')
@section('content')
@php
    $isAki = $type === 'aki';
    $title = $isAki ? 'Penggantian Aki' : 'Filter & Oli Mesin';
    $baseRoute = 'billing.form-maintenance.'.$type;
    $vendorName = $isAki ? ($vehicle->nama_vendor ?? '—') : ($vehicle->vendor?->nama ?? '—');
@endphp
<div class="container-fluid maintenance-cart px-3 px-lg-4 py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <p class="cart-eyebrow mb-2">MAINTENANCE KENDARAAN</p>
            <h1 class="h3 fw-bold mb-2">Keranjang {{ $title }}</h1>
            <p class="text-muted mb-0">Periksa penggantian dan pembayaran sebelum dikirim untuk otorisasi.</p>
        </div>
        <a href="{{ route($baseRoute) }}" class="btn btn-outline-secondary cart-button"><i class="fa fa-arrow-left me-2" aria-hidden="true"></i>Kembali ke Form</a>
    </div>
    @include('billing.form-maintenance.filter-oli.feedback')
    <ol class="cart-progress list-unstyled d-flex flex-wrap gap-3 gap-md-4 mb-4" aria-label="Tahapan penggantian">
        <li><span class="cart-step completed"><i class="fa fa-check" aria-hidden="true"></i></span>Pilih item</li>
        <li class="fw-bold text-success" aria-current="step"><span class="cart-step active">2</span>Periksa keranjang</li>
        <li><span class="cart-step">3</span>Otorisasi admin</li>
    </ol>
    <div class="cart-vehicle mb-4 p-3 p-md-4">
        <div class="row align-items-center g-3 g-md-4">
            <div class="col-6 col-md-3"><span class="cart-label">Nomor lambung</span><strong class="h3 d-block mb-0 text-success">{{ $vehicle->nomor_lambung }}</strong></div>
            <div class="col-6 col-md-{{ $isAki ? '3' : '5' }}"><span class="cart-label">Vendor</span><strong class="d-block">{{ $vendorName }}</strong></div>
            @if ($isAki)
                <div class="col-6 col-md-3"><span class="cart-label">Driver</span><strong class="d-block">{{ $vehicle->nama_driver ?? '—' }}</strong></div>
            @endif
            <div class="col-6 col-md-{{ $isAki ? '3' : '4' }}"><span class="cart-label">Rincian penggantian</span><strong class="d-block">{{ $cartItems->count() }} item dalam keranjang</strong></div>
        </div>
        @if ($isAki && $vehicle->pengurus)<div class="small text-muted mt-3 pt-3 border-top">Pengurus: <span class="text-body">{{ $vehicle->pengurus }}</span></div>@endif
    </div>
    <div class="row g-4 align-items-start">
        <div class="col-xl-8">
            <section class="cart-panel" aria-labelledby="cartItemsTitle">
                <div class="p-3 p-md-4 d-flex flex-wrap justify-content-between align-items-center gap-3 border-bottom">
                    <div><h2 id="cartItemsTitle" class="h5 fw-bold mb-1">Rincian penggantian</h2><p class="small text-muted mb-0">Pastikan item dan tanggal penggantian sudah sesuai.</p></div>
                    <form method="post" action="{{ $isAki ? route($baseRoute.'.cart.clear', $vehicle->id) : route($baseRoute.'.cart.clear') }}" data-maintenance-form @if ($isAki) data-maintenance-ajax @endif data-confirm="Kosongkan seluruh keranjang?">
                        @csrf @method('delete')<button class="btn btn-sm btn-outline-danger cart-button" type="submit"><i class="fa fa-trash-o me-2" aria-hidden="true"></i>Kosongkan</button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table cart-items align-middle mb-0"><thead class="table-success"><tr><th scope="col">No</th><th scope="col">{{ $isAki ? 'Posisi Aki' : 'Kategori' }}</th><th scope="col">Merek</th><th scope="col">Kondisi</th><th scope="col">Tanggal Ganti</th><th scope="col" class="text-end">Aksi</th></tr></thead><tbody>
                    @foreach ($cartItems as $item)
                        @php $itemLabel = $isAki ? $item->posisiAki->nama : $item->kategori->nama; @endphp
                        <tr>
                            <td class="text-muted">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="fw-semibold">{{ $itemLabel }}</td><td>{{ $item->merk }}</td>
                            <td><span class="cart-condition">{{ $item->kondisi }}%</span></td>
                            <td><label for="date{{ $item->id }}" class="visually-hidden">Tanggal ganti {{ $itemLabel }}</label><input id="date{{ $item->id }}" form="checkoutForm" type="date" name="items[{{ $item->id }}][tanggal_ganti]" value="{{ old('items.'.$item->id.'.tanggal_ganti', $isAki ? today()->toDateString() : $item->created_at->toDateString()) }}" max="{{ today()->toDateString() }}" class="form-control cart-date" required></td>
                            <td class="text-end"><form method="post" action="{{ route($baseRoute.'.cart.delete', $item->id) }}" data-maintenance-form @if ($isAki) data-maintenance-ajax @endif data-confirm="Hapus {{ $itemLabel }} dari keranjang?">@csrf @method('delete')<button class="btn btn-outline-danger cart-remove" aria-label="Hapus {{ $itemLabel }}" title="Hapus {{ $itemLabel }}" type="submit"><i class="fa fa-trash-o" aria-hidden="true"></i></button></form></td>
                        </tr>
                    @endforeach
                    </tbody></table>
                </div>
                <div class="p-3 p-md-4 border-top d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <span class="small text-muted"><i class="fa fa-calendar-check-o me-2" aria-hidden="true"></i>Tanggal ganti dapat disesuaikan pada setiap item.</span>
                    <a href="{{ route($baseRoute) }}" class="btn btn-sm btn-outline-success cart-button"><i class="fa fa-plus me-2" aria-hidden="true"></i>Tambah Item</a>
                </div>
            </section>
            <div class="cart-note mt-3 d-flex gap-3 p-3"><i class="fa fa-info-circle text-success mt-1" aria-hidden="true"></i><p class="small mb-0 text-muted">Histori penggantian dan pembayaran akan dicatat setelah invoice disetujui oleh admin/SU.</p></div>
        </div>
        <aside class="col-xl-4">
            <section class="cart-panel cart-payment" aria-labelledby="paymentTitle">
                <div class="p-3 p-md-4 border-bottom"><span class="cart-eyebrow">LANGKAH 2 DARI 3</span><h2 id="paymentTitle" class="h5 fw-bold mt-2 mb-1">Informasi pembayaran</h2><p class="small text-muted mb-0">Pilih sumber dana dan lengkapi tujuan pembayaran.</p></div>
                <form id="checkoutForm" method="post" action="{{ route($baseRoute.'.checkout') }}" data-maintenance-form data-confirm="Kirim penggantian {{ $title }} untuk otorisasi?" class="p-3 p-md-4" novalidate>
                    @csrf<input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                    @include('billing.form-maintenance.payment-fields')
                    <div class="pt-3 border-top"><button type="submit" class="btn btn-success cart-button w-100 fw-semibold"><i class="fa fa-paper-plane-o me-2" aria-hidden="true"></i>Kirim untuk Otorisasi</button><p class="small text-muted text-center mt-3 mb-0">Periksa kembali data sebelum melanjutkan.</p></div>
                </form>
            </section>
        </aside>
    </div>
</div>
@endsection
@push('css')
<style>
.maintenance-cart{max-width:1480px;color:#24342f}.cart-eyebrow{font-size:.72rem;font-weight:800;letter-spacing:.12em;color:#64776e}.cart-label{display:block;font-size:.8rem;color:#64776e;margin-bottom:.35rem}.cart-vehicle{background:#f4f8f5;border:1px solid #e0e9e3;border-left:4px solid #198754;border-radius:14px}.cart-panel{background:#fff;border:1px solid #e1e8e4;border-radius:16px;box-shadow:0 4px 20px rgba(27,61,43,.04);overflow:hidden}.cart-progress{font-size:.85rem;color:#738078}.cart-progress li{display:flex;align-items:center;gap:.6rem}.cart-step{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:#f0f3f1;color:#748178;font-weight:700;font-size:.8rem}.cart-step.active{background:#198754;color:#fff}.cart-step.completed{background:#e7f3ec;color:#198754}.cart-button{border-radius:9px;min-height:44px;display:inline-flex;align-items:center;justify-content:center}.cart-items th{font-size:.76rem;font-weight:700;text-transform:uppercase;letter-spacing:.025em;padding:14px 16px;white-space:nowrap}.cart-items td{padding:18px 16px;font-size:.9rem;border-color:#edf1ee}.cart-condition{display:inline-block;background:#edf6f0;color:#216b42;padding:5px 9px;border-radius:7px;font-size:.78rem;font-weight:700}.cart-remove{width:44px;height:44px;border-radius:9px}.cart-date{min-width:145px;font-size:.85rem;min-height:44px}.cart-note{background:#f5f8f6;border:1px solid #e5ede7;border-radius:12px}.cart-payment .form-control,.cart-payment .form-select{min-height:44px;border-radius:8px;border-color:#dce4df}.cart-payment .form-label{font-size:.85rem;font-weight:600}.cart-payment .select2-container .select2-selection{min-height:44px;display:flex;align-items:center;border-color:#dce4df;border-radius:8px}.cart-payment .input-group-text{background:#f5f8f6;border-color:#dce4df;font-weight:600}.cart-payment [data-maintenance-money]{font-weight:700;font-size:1.2rem;font-variant-numeric:tabular-nums}.cart-payment [data-uj-balance]{font-variant-numeric:tabular-nums}@media(min-width:1200px){.cart-payment{position:sticky;top:24px}}@media(max-width:575px){.maintenance-cart h1{font-size:1.4rem}.cart-items th,.cart-items td{padding:12px}.cart-progress{font-size:.78rem;gap:.75rem!important}.cart-step{width:24px;height:24px}}
</style>
@endpush
