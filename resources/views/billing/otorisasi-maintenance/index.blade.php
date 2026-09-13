@extends('layouts.app')

@section('content')
<div class="container px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Otorisasi Penggantian Ban</h3>
            <p class="text-muted mb-0">Review dan setujui penerbitan invoice penggantian ban yang masih pending.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    @include('swal')

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
                            <th>JUMLAH BAN</th>
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
                                    @if($item->pembayaran === \App\Models\BanGantiInvoice::PEMBAYARAN_KAS_BESAR)
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
                                        <a href="{{ route('rekap.maintenance.ban-luar.show', $item->id) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="fa fa-eye"></i> Detail
                                        </a>
                                        <form action="{{ route('billing.otorisasi-maintenance.ban-luar.approve', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="button"
                                                    class="btn btn-sm btn-success btn-approve"
                                                    data-low-ritase="{{ json_encode($item->low_ritase_warnings) }}">
                                                <i class="fa fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('billing.otorisasi-maintenance.ban-luar.reject', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-danger btn-reject">
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

@push('js')
<script>
    $(document).ready(function(){
        $('.btn-approve').click(function() {
            let form = $(this).closest('form');
            let lowRitase = $(this).data('low-ritase');

            let htmlContent = '';

            // Tampilkan warning box jika ada ban dengan ritase < 80
            if (lowRitase && lowRitase.length > 0) {
                htmlContent = `
                    <div class="alert alert-warning text-start mb-3 fs-7 border border-warning" style="max-height: 200px; overflow-y: auto;">
                        <strong class="text-danger d-block mb-1">
                            <i class="fa fa-exclamation-triangle me-1"></i> PERINGATAN RITASE RENDAH!
                        </strong>
                        Terdapat ban yang akan diganti dengan ritase di bawah 80:
                        <ul class="mb-0 mt-1 ps-3">
                `;
                lowRitase.forEach(item => {
                    htmlContent += `<li><b>${item.posisi}</b> (${item.merk} - ${item.no_seri}): <span class="badge bg-danger">${item.ritase} Ritase</span></li>`;
                });
                htmlContent += `</ul></div><p class="mb-0">Apakah Anda yakin ingin menyetujui invoice ini?</p>`;
            } else {
                htmlContent = '<p class="mb-0">Saldo Kas Besar (jika ada) akan dipotong dan Log Ban akan diperbarui secara permanen.</p>';
            }

            Swal.fire({
                title: lowRitase && lowRitase.length > 0 ? 'Tetap Approve Invoice?' : 'Setujui Invoice?',
                html: htmlContent,
                icon: lowRitase && lowRitase.length > 0 ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonColor: lowRitase && lowRitase.length > 0 ? '#ffc107' : '#198754',
                confirmButtonText: lowRitase && lowRitase.length > 0 ? 'Ya, Tetap Approve!' : 'Ya, Approve!',
                confirmButtonTextColor: lowRitase && lowRitase.length > 0 ? '#000' : '#fff'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });

        $('.btn-reject').click(function() {
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Tolak Invoice?',
                text: "Invoice ini akan ditandai sebagai ditolak/dibatalkan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Tolak'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    });
</script>
@endpush
