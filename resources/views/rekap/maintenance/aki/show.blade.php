@extends('layouts.app')

@section('content')
@php
    $previousUrl = url()->previous();
    $currentUrl  = url()->current();

    if ($previousUrl && $previousUrl !== $currentUrl && !str_contains($previousUrl, 'update')) {
        session(['aki_show_back_url' => $previousUrl]);
    }

    $backUrl = session('aki_show_back_url', route('billing.otorisasi-maintenance.aki'));

    $userRole = auth()->user()->role ?? '';
    $canEditDate = in_array($userRole, ['su', 'admin']) && $invoice->status === \App\Models\AkiGantiInvoice::STATUS_PENDING;
@endphp

<div class="container px-4 py-4">
    <!-- Header Page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fa fa-arrow-left me-1"></i> Kembali
            </a>
            <h3 class="fw-bold mb-0">Detail Invoice Aki: {{ $invoice->no_invoice }}</h3>
        </div>
    </div>

    @include('swal')

    <!-- ROW 1: INFORMASI KENDARAAN & STATUS PEMBAYARAN -->
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
                                @if($invoice->status === \App\Models\AkiGantiInvoice::STATUS_APPROVED)
                                    <span class="badge bg-success fs-7"><i class="fa fa-check-circle me-1"></i> Disetujui</span>
                                @elseif($invoice->status === \App\Models\AkiGantiInvoice::STATUS_REJECTED)
                                    <span class="badge bg-danger fs-7"><i class="fa fa-times-circle me-1"></i> Ditolak</span>
                                @else
                                    <span class="badge bg-warning text-dark fs-7"><i class="fa fa-clock me-1"></i> Pending</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal Invoice</td>
                            <td class="fw-bold text-end">{{ date('d-m-Y', strtotime($invoice->tanggal ?? $invoice->created_at)) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Metode Pembayaran</td>
                            <td class="text-end">
                                @if($invoice->pembayaran === \App\Models\AkiGantiInvoice::PEMBAYARAN_KAS_BESAR)
                                    <span class="badge bg-primary fs-7">Kas Besar</span>
                                @else
                                    <span class="badge bg-secondary fs-7">Dibayar Sendiri</span>
                                @endif
                            </td>
                        </tr>
                        @if($invoice->pembayaran === \App\Models\AkiGantiInvoice::PEMBAYARAN_KAS_BESAR)
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

    <!-- ROW 2: RINCIAN PERBANDINGAN AKI -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-car-battery text-info me-2"></i> Rincian Perbandingan Aki
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-center mb-0 fs-7">
                            <thead>
                                <tr class="table-light">
                                    <th rowspan="2" class="align-middle">NO</th>
                                    <th rowspan="2" class="align-middle">POSISI</th>
                                    <th colspan="3" class="bg-warning bg-opacity-25 border-bottom-0 text-dark fw-bold">AKI LAMA (DILEPAS)</th>
                                    <th colspan="3" class="bg-success bg-opacity-25 border-bottom-0 text-dark fw-bold">AKI BARU (DIPASANG)</th>
                                    <th rowspan="2" class="align-middle bg-light border-start">TGL GANTI AKI</th>
                                </tr>
                                <tr>
                                    <!-- Header Aki Lama -->
                                    <th class="bg-warning bg-opacity-10">MERK</th>
                                    <th class="bg-warning bg-opacity-10">NO. SERI</th>
                                    <th class="bg-warning bg-opacity-10">KONDISI</th>
                                    <!-- Header Aki Baru -->
                                    <th class="bg-success bg-opacity-10">MERK</th>
                                    <th class="bg-success bg-opacity-10">NO. SERI</th>
                                    <th class="bg-success bg-opacity-10">KONDISI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->details as $index => $detail)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-bold text-primary">
                                        {{ $detail->posisiAki->nama ?? ('Posisi '.$detail->posisi_aki_id) }}
                                        @if($canEditDate)
                                        <button class="btn text-primary p-0 ms-1" data-bs-toggle="modal"
                                            data-bs-target="#modalEditDetailItem{{ $detail->id }}"
                                            title="Edit Aki Baru">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        @endif
                                    </td>

                                    <!-- Data Aki Lama -->
                                    <td class="text-uppercase fw-semibold">{{ $detail->merk_lama }}</td>
                                    <td class="text-uppercase">{{ $detail->no_seri_lama }}</td>
                                    <td>{{ is_numeric($detail->kondisi_lama) ? $detail->kondisi_lama.'%' : $detail->kondisi_lama }}</td>

                                    <!-- Data Aki Baru -->
                                    <td class="fw-bold text-uppercase">{{ $detail->merk }}</td>
                                    <td class="text-uppercase">{{ $detail->no_seri }}</td>
                                    <td><span class="badge bg-info text-dark">{{ $detail->kondisi }}%</span></td>

                                    <!-- Tanggal Ganti Aki -->
                                    <td class="text-nowrap border-start bg-light bg-opacity-50 fw-semibold">
                                        <span>{{ date('d-m-Y', strtotime($detail->created_at)) }}</span>
                                        @if($canEditDate)
                                        <button class="btn btn-link btn-sm text-primary p-0 ms-1" data-bs-toggle="modal"
                                            data-bs-target="#modalEditTanggalItem{{ $detail->id }}"
                                            title="Edit Tanggal Posisi Ini">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        @endif
                                    </td>
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

<!-- MODAL EDIT TANGGAL & DETAIL ITEM (HANYA ROLE SU/ADMIN & STATUS PENDING) -->
@if($canEditDate)
@foreach($invoice->details as $detail)
<!-- Modal Edit Tanggal -->
<div class="modal fade" id="modalEditTanggalItem{{ $detail->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <form action="{{ route('rekap.maintenance.aki.detail.update-tanggal', $detail->id) }}" method="POST" class="form-confirm-edit">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-header-title fw-bold mb-0">Edit Tgl Ganti ({{ $detail->posisiAki->nama ?? 'Posisi '.$detail->posisi_aki_id }})</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-semibold">Tanggal Ganti Aki</label>
                        <input type="date" class="form-control" name="tanggal_ganti" value="{{ date('Y-m-d', strtotime($detail->created_at)) }}" required>
                        <small class="text-muted fs-8 mt-1 d-block">Tanggal ini akan menjadi tanggal resmi Log Aki setelah disetujui (Approve).</small>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Detail Item -->
<div class="modal fade" id="modalEditDetailItem{{ $detail->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <form action="{{ route('rekap.maintenance.aki.detail.update', $detail->id) }}" method="POST" class="form-confirm-edit">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-header-title fw-bold mb-0">
                        Edit Aki Baru ({{ $detail->posisiAki->nama ?? 'Posisi '.$detail->posisi_aki_id }})
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-semibold">Merk Baru</label>
                        <input type="text" class="form-control form-control-sm text-uppercase" name="merk" value="{{ $detail->merk }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-semibold">No. Seri Baru</label>
                        <input type="text" class="form-control form-control-sm text-uppercase" name="no_seri" value="{{ $detail->no_seri }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-semibold">Kondisi (%)</label>
                        <input type="number" class="form-control form-control-sm input-kondisi" name="kondisi" value="{{ $detail->kondisi }}" min="1" max="100" required>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endif
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.input-kondisi').forEach(function (input) {
        input.addEventListener('keydown', function (e) {
            if (['e', 'E', '+', '-', '.'].includes(e.key)) {
                e.preventDefault();
            }
        });

        input.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value === '') return;
            let val = parseInt(this.value, 10);
            if (val > 100) this.value = 100;
            else if (val < 1) this.value = 1;
        });

        input.addEventListener('blur', function () {
            if (this.value === '' || parseInt(this.value, 10) < 1) {
                this.value = 1;
            }
        });
    });

    document.querySelectorAll('.form-confirm-edit').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Perubahan',
                text: 'Apakah kamu yakin ingin menyimpan perubahan ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    });
});
</script>
@endpush
