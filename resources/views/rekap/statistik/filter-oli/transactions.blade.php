<h3 class="h6 fw-bold">{{ $log->kategori->nama }} · Ganti {{ $log->created_at->format('d-m-Y') }}</h3>
<p class="small text-muted">Transaksi tidak void sejak tanggal ganti sampai sebelum penggantian berikutnya, atau sampai sekarang untuk penggantian terakhir. Jarak &gt;50 km = 1 rit; jarak ≤50 km = 0,5 rit.</p>
<div class="table-responsive"><table class="table table-hover align-middle"><thead class="table-success"><tr><th>No</th><th>Transaksi</th><th>Uang Jalan</th><th>Tanggal</th><th>Rute</th><th>Jarak</th><th>Ritase</th></tr></thead><tbody>
    @forelse ($transactions as $transaction)
        <tr><td>{{ $loop->iteration }}</td><td>#{{ $transaction->id }}</td><td>UJ{{ $transaction->nomor_uang_jalan }}</td><td class="text-nowrap">{{ \Illuminate\Support\Carbon::parse($transaction->created_at)->format('d-m-Y H:i') }}</td><td>{{ $transaction->rute }}</td><td class="text-nowrap">{{ number_format((float) $transaction->jarak, 1, ',', '.') }} km</td><td>{{ number_format((float) $transaction->nilai_ritase, 1, ',', '.') }}</td></tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada transaksi yang menambah ritase pada periode ini.</td></tr>
    @endforelse
</tbody><tfoot><tr><th colspan="6" class="text-end">Total ritase</th><th class="text-nowrap">{{ number_format((float) $transactions->sum('nilai_ritase'), 1, ',', '.') }} rit</th></tr></tfoot></table></div>
