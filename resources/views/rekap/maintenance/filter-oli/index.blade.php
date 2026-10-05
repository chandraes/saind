@extends('layouts.app')
@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div><h1 class="h3 fw-bold mb-1">Rekap Filter & Oli Mesin</h1><p class="text-muted mb-0">Invoice penggantian yang sudah disetujui atau ditolak.</p></div>
        <a class="btn btn-outline-secondary" href="{{ route('rekap.index') }}"><i class="fa fa-arrow-left me-2"></i>Rekap</a>
    </div>
    @include('billing.form-maintenance.filter-oli.feedback')
    <div class="row g-3 mb-4">
        @foreach (['total' => 'Total invoice', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'cash' => 'Kas Besar · disetujui'] as $key => $label)
            <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small mb-2">{{ $label }}</div><div class="fs-4 fw-bold {{ $key === 'approved' ? 'text-success' : ($key === 'rejected' ? 'text-danger' : '') }}">{{ $key === 'cash' ? 'Rp '.number_format($summary[$key], 0, ',', '.') : $summary[$key] }}</div></div></div></div>
        @endforeach
    </div>
    <form method="get" class="card border-0 shadow-sm mb-4"><div class="card-body"><div class="row g-3 align-items-end">
        <div class="col-md-3"><label for="startDate" class="form-label">Tanggal invoice dari</label><input id="startDate" class="form-control" type="date" name="start_date" value="{{ $startDate }}" required></div>
        <div class="col-md-3"><label for="endDate" class="form-label">Sampai tanggal</label><input id="endDate" class="form-control" type="date" name="end_date" value="{{ $endDate }}" required></div>
        <div class="col-md-2"><label for="reportStatus" class="form-label">Status</label><select id="reportStatus" class="form-select maintenance-select" name="status"><option value="">Semua status</option><option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Disetujui</option><option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Ditolak</option></select></div>
        <div class="col-md-2"><label for="reportPayment" class="form-label">Pembayaran</label><select id="reportPayment" class="form-select maintenance-select" name="pembayaran"><option value="">Semua metode</option><option value="dibayar_sendiri" @selected(($filters['pembayaran'] ?? '') === 'dibayar_sendiri')>Dibayar sendiri</option><option value="kas_besar" @selected(($filters['pembayaran'] ?? '') === 'kas_besar')>Kas Besar</option></select></div>
        <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary" type="submit">Tampilkan</button><a href="{{ route('rekap.maintenance.filter-oli') }}" class="btn btn-outline-secondary" aria-label="Reset filter"><i class="fa fa-refresh"></i></a></div>
    </div></div></form>
    <div class="card border-0 shadow-sm"><div class="card-header bg-white p-3"><h2 class="h6 fw-bold mb-0">Daftar invoice</h2></div><div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="table-success"><tr><th>No</th><th>Invoice</th><th>Tanggal</th><th>Kendaraan / Vendor</th><th>Item</th><th>Status</th><th>Pembayaran</th><th>Nominal</th><th>Action</th></tr></thead><tbody>
        @forelse ($invoices as $invoice)
            <tr><td>{{ $invoices->firstItem() + $loop->index }}</td><td class="fw-semibold text-nowrap">{{ $invoice->no_invoice }}</td><td class="text-nowrap">{{ $invoice->created_at->format('d-m-Y') }}</td><td><strong>{{ $invoice->vehicle->nomor_lambung }}</strong><span class="d-block small text-muted">{{ $invoice->vehicle->vendor->nama ?? '—' }}</span></td><td>{{ $invoice->details_count }}</td><td><span class="badge {{ $invoice->status === 'approved' ? 'bg-success' : 'bg-danger' }}">{{ $invoice->status === 'approved' ? 'Disetujui' : 'Ditolak' }}</span></td><td>{{ $invoice->pembayaran === 'kas_besar' ? 'Kas Besar' : 'Dibayar sendiri' }}</td><td class="text-nowrap">Rp {{ number_format((float) $invoice->total_nominal, 0, ',', '.') }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('rekap.maintenance.filter-oli.show', $invoice->id) }}">Detail</a></td></tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-5">Belum ada invoice yang sesuai dengan filter.</td></tr>
        @endforelse
        </tbody></table></div><div class="card-body">{{ $invoices->links('pagination::bootstrap-5') }}</div></div>
</div>
@endsection
