<section class="card shadow-sm mb-4" aria-labelledby="note-payment-title">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <h2 class="h6 fw-bold mb-0" id="note-payment-title"><i class="fa fa-money me-2" aria-hidden="true"></i>Estimasi uang jalan</h2>
        <span class="badge {{ $preview['held'] ? 'bg-success' : 'bg-secondary' }}">UJ ditahan {{ $preview['held'] ? 'aktif' : 'nonaktif' }}</span>
    </div>
    <div class="card-body">
        <p class="small text-muted mb-3">Mengikuti pengaturan kendaraan dan kesepakatan vendor–rute saat ini, seperti form uang jalan keluar. Nominal akhir diperiksa kembali saat diproses. Estimasi ini bukan bukti pencairan atau nominal historis transaksi.</p>
        @foreach ($preview['warnings'] as $warning)
            <div class="alert alert-warning py-2" role="alert"><i class="fa fa-exclamation-triangle me-2" aria-hidden="true"></i>{{ $warning }}</div>
        @endforeach
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <caption class="visually-hidden">Rincian estimasi uang jalan untuk nota {{ $note->ticket_number }}</caption>
                        <thead class="table-success"><tr><th scope="col">Komponen</th><th scope="col" class="text-end">Nominal</th></tr></thead>
                        <tbody>
                            @foreach (['gross' => 'Total uang jalan (kotor)', 'sparepart' => 'UJ Ditahan Sparepart', 'ban' => 'UJ Ditahan Ban', 'transfer' => 'Sisa transfer (netto)'] as $key => $label)
                            <tr class="{{ $key === 'transfer' ? 'fw-bold' : '' }}">
                                <th scope="row" class="fw-normal">{{ $label }}@if (in_array($key, ['sparepart', 'ban']) && ! $preview['held'])<span class="d-block small text-muted">Tidak diaktifkan pada kendaraan</span>@endif</th>
                                <td class="text-end text-nowrap {{ $key === 'transfer' ? 'text-success' : '' }}">{{ $preview[$key] === null ? 'Belum tersedia' : 'Rp '.number_format($preview[$key], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-5">
                <h3 class="h6 fw-bold mb-3"><i class="fa fa-user me-2" aria-hidden="true"></i>Driver dan tujuan transfer</h3>
                <dl class="row mb-0">
                    @foreach (['driver' => 'Driver terdaftar', 'driver_phone' => 'Telepon driver', 'recipient' => 'Nama rekening', 'bank' => 'Bank', 'account' => 'Nomor rekening'] as $key => $label)
                        <dt class="col-sm-5 small text-muted">{{ $label }}</dt><dd class="col-sm-7 text-break">{{ $preview[$key] ?: 'Belum tersedia' }}</dd>
                    @endforeach
                </dl>
                <p class="small text-muted mb-0">Saat UJ ditahan aktif, tujuan transfer mengikuti rekening driver jika driver terhubung; selain itu mengikuti data kendaraan.</p>
            </div>
        </div>
    </div>
</section>
