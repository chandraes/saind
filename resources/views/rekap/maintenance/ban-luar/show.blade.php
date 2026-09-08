@extends('layouts.app')

@section('content')
@php
    $previousUrl = url()->previous();
    $currentUrl  = url()->current();

    // Jika previous URL ada, tidak sama dengan current URL, gunakan previous URL. Jika tidak, fallback ke rekap ban luar.
    $backUrl = ($previousUrl && $previousUrl !== $currentUrl)
        ? $previousUrl
        : route('rekap.maintenance.ban-luar');
@endphp

<div class="container px-4 py-4">
    <!-- Header Page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fa fa-arrow-left me-1"></i> Kembali
            </a>
            <h3 class="fw-bold mb-0">Detail Invoice: {{ $invoice->no_invoice }}</h3>
        </div>
        <div>
            {{-- <button type="button" onclick="window.print()" class="btn btn-outline-dark fw-bold">
                <i class="fa fa-print me-1"></i> Cetak / Print
            </button> --}}
        </div>
    </div>

    <div class="row">
        <!-- RINCIAN UNIT KENDARAAN & STATUS INVOICE -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="fa fa-truck text-primary me-2"></i> Informasi Kendaraan
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted">Nomor Lambung</td>
                            <td class="fw-bold text-end">{{ $invoice->vehicle->nomor_lambung ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Vendor</td>
                            <td class="fw-bold text-end">{{ $invoice->vehicle->vendor->nama ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="fa fa-receipt text-warning me-2"></i> Status & Pembayaran
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted">Tanggal Transaksi</td>
                            <td class="fw-bold text-end">{{ date('d-m-Y', strtotime($invoice->tanggal)) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Metode Pembayaran</td>
                            <td class="text-end">
                                @if($invoice->pembayaran === \App\Models\BanGantiInvoice::PEMBAYARAN_KAS_BESAR)
                                    <span class="badge bg-primary fs-7">Kas Besar</span>
                                @else
                                    <span class="badge bg-secondary fs-7">Dibayar Sendiri</span>
                                @endif
                            </td>
                        </tr>
                        @if($invoice->pembayaran === \App\Models\BanGantiInvoice::PEMBAYARAN_KAS_BESAR)
                            <tr>
                                <td class="text-muted">Total Nominal</td>
                                <td class="fw-bold text-success text-end fs-6">
                                    Rp {{ number_format($invoice->total_nominal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endif
                    </table>

                    @if($invoice->pembayaran === \App\Models\BanGantiInvoice::PEMBAYARAN_DIBAYAR_SENDIRI)
                        <div class="alert alert-light border-0 bg-light mt-3 mb-0 fs-7 text-muted">
                            <i class="fa fa-info-circle me-1"></i> Biaya penggantian ban ditanggung langsung oleh unit / dibayar mandiri.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- DAFTAR BAN YANG DIGANTI -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-cubes text-info me-2"></i> Rincian Item Ban yang Diganti
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-center mb-0">
                            <thead class="table-success">
                                <tr>
                                    <th>NO</th>
                                    <th>POSISI BAN</th>
                                    <th>SUMBER BAN</th>
                                    <th>MEREK BAN</th>
                                    <th>NO. SERI BAN</th>
                                    <th>KONDISI BAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->details as $index => $detail)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="fw-bold text-primary">{{ $detail->posisiBan->nama ?? '-' }}</td>
                                        <td>
                                            @if($detail->sumber_ban === 'serep')
                                                <span class="badge bg-warning text-dark fs-7">Ban Serep</span>
                                            @else
                                                <span class="badge bg-success fs-7">Ban Baru/Luar</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold text-uppercase">{{ $detail->merk }}</td>
                                        <td class="text-uppercase">{{ $detail->no_seri }}</td>
                                        <td><span class="badge bg-info text-dark fs-7">{{ $detail->kondisi }}%</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
