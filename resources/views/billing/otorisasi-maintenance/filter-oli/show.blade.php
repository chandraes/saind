@extends('layouts.app')
@section('content')
@php
    $canEdit = in_array(auth()->user()->role, ['su', 'admin']) && $invoice->status === \App\Models\FilterOliGantiInvoice::STATUS_PENDING;
@endphp
<div class="container px-4 py-4">
    <!-- Header Page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ $backUrl ?? route('billing.otorisasi-maintenance.filter-oli') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fa fa-arrow-left me-1"></i> Kembali
            </a>
            <h3 class="fw-bold mb-0">Detail Invoice: {{ $invoice->no_invoice }}</h3>
        </div>
    </div>

    @include('billing.form-maintenance.filter-oli.feedback')

    <!-- ROW 1: INFORMASI KENDARAAN & STATUS PEMBAYARAN (KIRI - KANAN) -->
    <div class="row g-3 mb-4">
        <!-- Informasi Kendaraan -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
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
        </div>

        <!-- Status & Pembayaran -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="fa fa-receipt text-warning me-2"></i> Status & Pembayaran
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted">Status Otorisasi</td>
                            <td class="text-end">
                                @if($invoice->status === \App\Models\FilterOliGantiInvoice::STATUS_APPROVED)
                                <span class="badge bg-success fs-7"><i class="fa fa-check-circle me-1"></i>
                                    Disetujui</span>
                                @elseif($invoice->status === \App\Models\FilterOliGantiInvoice::STATUS_REJECTED)
                                <span class="badge bg-danger fs-7"><i class="fa fa-times-circle me-1"></i>
                                    Ditolak</span>
                                @else
                                <span class="badge bg-warning text-dark fs-7"><i class="fa fa-clock me-1"></i>
                                    Pending</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal Invoice</td>
                            <td class="fw-bold text-end">{{ date('d-m-Y', strtotime($invoice->tanggal ??
                                $invoice->created_at)) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Metode Pembayaran</td>
                            <td class="text-end">
                                @if($invoice->pembayaran === 'kas_besar')
                                <span class="badge bg-primary fs-7">Kas Besar</span>
                                @else
                                <span class="badge bg-secondary fs-7">Dibayar Sendiri</span>
                                @endif
                            </td>
                        </tr>
                        @if($invoice->pembayaran === 'kas_besar')
                        <tr>
                            <td class="text-muted">Total Nominal</td>
                            <td class="fw-bold text-success text-end fs-6">
                                Rp {{ number_format($invoice->total_nominal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3"><h2 class="h5 fw-bold mb-0"><i class="fa fa-cubes text-info me-2" aria-hidden="true"></i>Rincian Perbandingan Filter & Oli Mesin</h2></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center mb-0">
                <thead class="table-success">
                    <tr><th rowspan="2" class="align-middle">NO</th><th rowspan="2" class="align-middle">KATEGORI</th><th colspan="2" class="bg-warning bg-opacity-25">LAMA (DIGANTI)</th><th colspan="2">BARU (DIPASANG)</th><th rowspan="2" class="align-middle">RITASE SAAT INI</th><th rowspan="2" class="align-middle">TANGGAL GANTI</th></tr>
                    <tr><th class="bg-warning bg-opacity-25">MEREK</th><th class="bg-warning bg-opacity-25">RITASE</th><th>MEREK</th><th>KONDISI</th></tr>
                </thead>
                <tbody>
                    @foreach ($invoice->details as $detail)
                        @php($previous = $previousLogs->get($detail->id))
                        <tr>
                            <td>{{ $loop->iteration }}</td><td class="fw-bold text-primary">{{ $detail->kategori->nama }}
                                @if ($canEdit)<button class="btn btn-link btn-sm p-0 ms-1" data-bs-toggle="modal" data-bs-target="#editDetail{{ $detail->id }}" aria-label="Edit {{ $detail->kategori->nama }}"><i class="fa fa-edit" aria-hidden="true"></i></button>@endif
                            </td>
                            <td class="bg-warning bg-opacity-10">{{ $previous?->merk ?? '—' }}</td>
                            <td class="bg-warning bg-opacity-10">@if ($previous)<span class="{{ (float) $previous->ritase < $detail->limit_ritase ? 'badge bg-danger' : 'fw-semibold' }}">{{ number_format((float) $previous->ritase, 1, ',', '.') }} rit</span>@else — @endif</td>
                            <td class="fw-semibold">{{ $detail->merk }}</td><td><span class="badge bg-info text-dark">{{ $detail->kondisi }}%</span></td><td><button type="button" class="btn btn-link btn-sm fw-semibold text-nowrap" data-bs-toggle="modal" data-bs-target="#ritaseDetail{{ $detail->id }}">{{ number_format((float) $detail->ritase, 1, ',', '.') }} rit <i class="fa fa-list-ul ms-1" aria-hidden="true"></i></button></td><td class="text-nowrap">{{ $detail->created_at->format('d-m-Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @if ($invoice->pembayaran === 'kas_besar')
        <div class="card border-0 shadow-sm mt-4"><div class="card-body"><h2 class="h6 fw-bold">Detail Transfer</h2><p class="mb-0">{{ $invoice->nama_bank }} · {{ $invoice->nomor_rekening }} · {{ $invoice->nama_rekening }}</p></div></div>
    @endif
</div>
@foreach ($invoice->details as $detail)
    <div class="modal fade" id="ritaseDetail{{ $detail->id }}" tabindex="-1" aria-labelledby="ritaseTitle{{ $detail->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content rounded-4">
            <div class="modal-header"><h2 class="modal-title fs-5" id="ritaseTitle{{ $detail->id }}">Transaksi Ritase · {{ $detail->kategori->nama }}</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body">
                @if ($invoice->status === \App\Models\FilterOliGantiInvoice::STATUS_APPROVED && !$detail->filter_oli_log_id)
                    <p class="text-muted small">Histori penggantian ini telah dihapus. Nilai ritase terakhir pada invoice tetap tersimpan; catatan transaksi histori sudah dihapus.</p>
                @else
                    <p class="text-muted small">Transaksi tidak void sejak tanggal ganti sampai sebelum penggantian berikutnya. Jarak &gt;50 km dihitung 1 rit; jarak ≤50 km dihitung 0,5 rit.</p>
                @endif
                <div class="table-responsive"><table class="table table-hover align-middle"><thead class="table-success"><tr><th>No</th><th>Transaksi</th><th>Uang Jalan</th><th>Tanggal</th><th>Rute</th><th>Jarak</th><th>Ritase</th></tr></thead><tbody>
                    @forelse ($ritaseTransactions->get($detail->id) as $transaction)
                        <tr><td>{{ $loop->iteration }}</td><td>#{{ $transaction->id }}</td><td>UJ{{ $transaction->nomor_uang_jalan }}</td><td>{{ \Illuminate\Support\Carbon::parse($transaction->created_at)->format('d-m-Y H:i') }}</td><td>{{ $transaction->rute }}</td><td>{{ number_format((float) $transaction->jarak, 1, ',', '.') }} km</td><td>{{ number_format((float) $transaction->nilai_ritase, 1, ',', '.') }}</td></tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada transaksi yang menambah ritase.</td></tr>
                    @endforelse
                </tbody><tfoot><tr><th colspan="6" class="text-end">Total Ritase</th><th>{{ number_format((float) $detail->ritase, 1, ',', '.') }}</th></tr></tfoot></table></div>
            </div>
        </div></div>
    </div>
@endforeach
@if ($canEdit)
    @foreach ($invoice->details as $detail)
        <div class="modal fade" data-bs-focus="false" id="editDetail{{ $detail->id }}" tabindex="-1" aria-labelledby="editDetailTitle{{ $detail->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4 border-0">
                <form method="post" action="{{ route('billing.otorisasi-maintenance.filter-oli.detail.update', $detail->id) }}" data-maintenance-form data-confirm="Simpan perubahan detail penggantian?" novalidate>
                    @csrf @method('patch')
                    <div class="modal-header"><h2 id="editDetailTitle{{ $detail->id }}" class="modal-title fs-5">Edit {{ $detail->kategori->nama }}</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label" for="merk{{ $detail->id }}">Merek</label><input id="merk{{ $detail->id }}" name="merk" class="form-control" maxlength="100" value="{{ $detail->merk }}" required></div>
                        <div class="mb-3"><label class="form-label" for="kondisi{{ $detail->id }}">Kondisi (%)</label><input id="kondisi{{ $detail->id }}" name="kondisi" type="number" class="form-control" min="1" max="100" step="1" value="{{ $detail->kondisi }}" required></div>
                        <div><label class="form-label" for="tanggal{{ $detail->id }}">Tanggal ganti</label><input id="tanggal{{ $detail->id }}" name="tanggal_ganti" type="date" class="form-control" max="{{ today()->format('Y-m-d') }}" value="{{ $detail->created_at->format('Y-m-d') }}" required></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan Perubahan</button></div>
                </form>
            </div></div>
        </div>
    @endforeach
@endif
@endsection
