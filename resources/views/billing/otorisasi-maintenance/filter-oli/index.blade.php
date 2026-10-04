@extends('layouts.app')

@section('content')
<div class="container px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Otorisasi Penggantian Filter & Oli Mesin</h3>
            <p class="text-muted mb-0">Review dan setujui penerbitan invoice penggantian filter & oli mesin yang masih pending.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    @include('billing.form-maintenance.filter-oli.feedback')

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>NO</th>
                            <th>NO. INVOICE</th>
                            <th>TANGGAL INPUT</th>
                            <th>NO. LAMBUNG</th>
                            <th>METODE PEMBAYARAN</th>
                            <th>TOTAL NOMINAL</th>
                            <th>JUMLAH ITEM</th>
                            <th>AKSI OTORISASI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-bold text-primary">{{ $item->no_invoice }}</td>
                                <td>{{ date('d-m-Y', strtotime($item->tanggal ?? $item->created_at)) }}</td>
                                <td><span class="badge bg-dark fs-7">{{ $item->vehicle->nomor_lambung ?? '-' }}</span></td>
                                <td>
                                    @if($item->pembayaran === 'kas_besar')
                                        <span class="badge bg-primary fs-7">Kas Besar</span>
                                    @else
                                        <span class="badge bg-secondary fs-7">Dibayar Sendiri</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-end pe-3">
                                    Rp {{ number_format($item->total_nominal, 0, ',', '.') }}
                                </td>
                                <td><span class="badge bg-info text-dark fs-7">{{ $item->details->count() }} Item</span></td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('billing.otorisasi-maintenance.filter-oli.show', $item->id) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="fa fa-eye"></i> Detail
                                        </a>
                                        <form action="{{ route('billing.otorisasi-maintenance.filter-oli.approve', $item->id) }}" method="POST" class="d-inline" data-maintenance-form data-confirm="Setujui invoice ini? Histori dan pembayaran akan diproses." data-warning="{{ $item->ritase_warning }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-check"></i> Approve</button>
                                        </form>
                                        <form action="{{ route('billing.otorisasi-maintenance.filter-oli.reject', $item->id) }}" method="POST" class="d-inline" data-maintenance-form data-confirm="Tolak invoice ini? Penggantian tidak akan dicatat.">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fa fa-times"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fa fa-check-circle fa-2x mb-2 d-block text-success"></i>
                                    Tidak ada antrean invoice yang membutuhkan otorisasi.
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

