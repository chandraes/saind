@extends('layouts.app')

@section('content')
<div class="container px-4 py-4">
    <!-- Header Page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Rekap Invoice Penggantian Ban</h3>
            <p class="text-muted mb-0">Daftar riwayat penerbitan invoice penggantian ban yang telah diotorisasi.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('rekap.index') }}" class="btn btn-outline-secondary fw-bold">
                <i class="fa fa-arrow-left me-1"></i> Kembali
            </a>
            <a href="{{ route('billing.form-maintenance.ban-luar') }}" class="btn btn-primary fw-bold">
                <i class="fa fa-plus me-1"></i> Form Penggantian Ban
            </a>
        </div>
    </div>

    @include('swal')

    <!-- RINGKASAN METRIK -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-white border-start border-primary border-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 fw-semibold d-block">TOTAL TRANSAKSI</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($totalTransaksi, 0, ',', '.') }}</h3>
                    </div>
                    <div class="bg-light-primary p-3 rounded-circle text-primary">
                        <i class="fa fa-file-invoice fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-white border-start border-success border-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 fw-semibold d-block">NOMINAL KAS (APPROVED)</span>
                        <h4 class="fw-bold text-success mb-0 mt-1">Rp {{ number_format($totalKasBesar, 0, ',', '.') }}</h4>
                    </div>
                    <div class="bg-light-success p-3 rounded-circle text-success">
                        <i class="fa fa-wallet fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-white border-start border-info border-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 fw-semibold d-block">BAYAR SENDIRI (APPROVED)</span>
                        <h3 class="fw-bold text-info mb-0 mt-1">{{ number_format($countDibayarSendiri, 0, ',', '.') }}</h3>
                    </div>
                    <div class="bg-light-info p-3 rounded-circle text-info">
                        <i class="fa fa-user-check fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-white border-start border-danger border-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-7 fw-semibold d-block">TRANSAKSI DITOLAK</span>
                        <h3 class="fw-bold text-danger mb-0 mt-1">{{ number_format($countRejected, 0, ',', '.') }}</h3>
                    </div>
                    <div class="bg-light-danger p-3 rounded-circle text-danger">
                        <i class="fa fa-times-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTER FORM -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('rekap.maintenance.ban-luar') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold fs-7 mb-1">Tanggal Mulai</label>
                    <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold fs-7 mb-1">Tanggal Selesai</label>
                    <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold fs-7 mb-1">Metode Pembayaran</label>
                    <select name="pembayaran" class="form-select">
                        <option value="">-- Semua Metode --</option>
                        <option value="{{ \App\Models\BanGantiInvoice::PEMBAYARAN_KAS_BESAR }}" {{ $pembayaran === \App\Models\BanGantiInvoice::PEMBAYARAN_KAS_BESAR ? 'selected' : '' }}>Kas Besar</option>
                        <option value="{{ \App\Models\BanGantiInvoice::PEMBAYARAN_DIBAYAR_SENDIRI }}" {{ $pembayaran === \App\Models\BanGantiInvoice::PEMBAYARAN_DIBAYAR_SENDIRI ? 'selected' : '' }}>Dibayar Sendiri</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold fs-7 mb-1">Status Otorisasi</label>
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <option value="{{ \App\Models\BanGantiInvoice::STATUS_APPROVED }}" {{ $statusFilter === \App\Models\BanGantiInvoice::STATUS_APPROVED ? 'selected' : '' }}>Disetujui (Approved)</option>
                        <option value="{{ \App\Models\BanGantiInvoice::STATUS_REJECTED }}" {{ $statusFilter === \App\Models\BanGantiInvoice::STATUS_REJECTED ? 'selected' : '' }}>Ditolak (Rejected)</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <i class="fa fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('rekap.maintenance.ban-luar') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- TABEL DATA -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>NO</th>
                            <th>NO. INVOICE</th>
                            <th>TANGGAL</th>
                            <th>NO. LAMBUNG</th>
                            <th>STATUS</th>
                            <th>PEMBAYARAN</th>
                            <th>TOTAL NOMINAL</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-bold text-primary">{{ $item->no_invoice }}</td>
                                <td>{{ date('d-m-Y', strtotime($item->tanggal)) }}</td>
                                <td><span class="badge bg-dark fs-7">{{ $item->vehicle->nomor_lambung ?? '-' }}</span></td>
                                <td>
                                    @if($item->status === \App\Models\BanGantiInvoice::STATUS_APPROVED)
                                        <span class="badge bg-success fs-7"><i class="fa fa-check-circle me-1"></i> Approved</span>
                                    @else
                                        <span class="badge bg-danger fs-7"><i class="fa fa-times-circle me-1"></i> Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->pembayaran === \App\Models\BanGantiInvoice::PEMBAYARAN_KAS_BESAR)
                                        <span class="badge bg-primary fs-7"><i class="fa fa-university me-1"></i> Kas Besar</span>
                                    @else
                                        <span class="badge bg-secondary fs-7"><i class="fa fa-user me-1"></i> Bayar Sendiri</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-end pe-4">
                                    @if($item->pembayaran === \App\Models\BanGantiInvoice::PEMBAYARAN_KAS_BESAR)
                                        <span class="{{ $item->status === \App\Models\BanGantiInvoice::STATUS_REJECTED ? 'text-decoration-line-through text-muted' : 'text-dark' }}">
                                            Rp {{ number_format($item->total_nominal, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('rekap.maintenance.ban-luar.show', $item->id) }}" class="btn btn-sm btn-outline-primary fw-bold">
                                        <i class="fa fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fa fa-folder-open fa-2x mb-2 d-block"></i>
                                    Tidak ada data rekap invoice yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
