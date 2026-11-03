@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div><h1 class="h3 fw-bold mb-2">Kesepakatan Uang Jalan Vendor</h1><p class="text-muted mb-0">{{ $vendor->nama }} · Atur nominal per rute untuk masing-masing dana.</p></div>
        <a href="{{ route('vendor.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>
    @include('swal')
    @if ($errors->any())<div class="alert alert-danger" role="alert"><strong>Periksa kembali isian berikut:</strong><ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form id="formUangJalan" action="{{ $editing ? route('uj.vendor.uang-jalan.update', $vendor->id) : route('uj.vendor.uang-jalan.store') }}" method="post">
        @csrf<input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-body border-bottom p-4"><h2 class="h5 fw-bold mb-1">Nominal kesepakatan per rute</h2><p class="small text-muted mb-0">UJ Ditahan Sparepart dan UJ Ditahan Ban dicatat sebagai dua dana yang terpisah.</p></div>
            <div class="table-responsive"><table class="table align-middle mb-0 vendor-uj-table"><thead class="table-success"><tr><th scope="col">No</th><th scope="col">Rute</th><th scope="col">Harga Uang Jalan (Rp)</th><th scope="col">UJ Ditahan Sparepart (Rp)</th><th scope="col">UJ Ditahan Ban (Rp)</th></tr></thead><tbody>
            @foreach ($rutes as $rute)
                @php
                    $index = $loop->index;
                    $record = $editing ? $vendorUjMap->get($rute->id) : null;
                    $amounts = [
                        'uang_jalan' => ['label' => 'Harga uang jalan', 'value' => $record?->hk_uang_jalan ?? $rute->uang_jalan ?? 0],
                        'uj_ditahan' => ['label' => 'UJ Ditahan Sparepart', 'value' => $record?->uj_ditahan ?? ($editing ? 0 : ($rute->uj_ditahan ?? 0))],
                        'uj_ditahan_ban' => ['label' => 'UJ Ditahan Ban', 'value' => $record?->uj_ditahan_ban ?? 0],
                    ];
                @endphp
                <tr><td class="text-muted">{{ $loop->iteration }}</td><td class="fw-semibold">{{ $rute->nama }}<input type="hidden" name="rute_id[{{ $index }}]" value="{{ $rute->id }}"></td>
                @foreach ($amounts as $field => $amount)
                    <td><label for="display_{{ $field }}_{{ $index }}" class="visually-hidden">{{ $amount['label'] }} · {{ $rute->nama }}</label><input id="display_{{ $field }}_{{ $index }}" type="text" inputmode="numeric" autocomplete="off" class="form-control text-end" data-vendor-money data-raw-field="{{ $field }}_{{ $index }}" value="{{ old($field.'.'.$index, $amount['value']) }}" required><input type="hidden" id="{{ $field }}_{{ $index }}" name="{{ $field }}[{{ $index }}]" value="{{ old($field.'.'.$index, $amount['value']) }}"></td>
                @endforeach
                </tr>
            @endforeach
            </tbody></table></div>
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3"><p class="small text-muted mb-0">Nominal menggunakan rupiah penuh dengan pemisah ribuan titik.</p><button type="submit" class="btn btn-success px-4"><i class="fa fa-save me-2" aria-hidden="true"></i>Simpan Kesepakatan</button></div>
    </form>
</div>
@endsection
@push('css')
<style>.vendor-uj-table th{font-size:.8rem;padding:16px;white-space:nowrap}.vendor-uj-table td{padding:12px 16px}.vendor-uj-table .form-control{min-width:140px;min-height:44px;font-variant-numeric:tabular-nums}.vendor-uj-table td:nth-child(2){min-width:200px}</style>
@endpush
@push('js')
<script src="{{ asset('assets/js/vendor-uang-jalan.js') }}?v={{ filemtime(public_path('assets/js/vendor-uang-jalan.js')) }}" defer></script>
@endpush
