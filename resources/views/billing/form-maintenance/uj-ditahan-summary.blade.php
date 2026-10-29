@if ($invoice->pembayaran === 'uj_ditahan')
<div class="card border-0 shadow-sm mb-4"><div class="card-body p-4">
    <h2 class="h5 fw-bold">Pembayaran dari UJ Ditahan Sparepart</h2>
    <p class="mb-1">Saldo kendaraan saat ini: <strong class="{{ $ujDitahanBalance >= (int) $invoice->total_nominal ? 'text-success' : 'text-danger' }}">Rp {{ number_format($ujDitahanBalance, 0, ',', '.') }}</strong></p>
    @if ($invoice->status === 'pending')
        <p class="small text-muted mb-0">Saldo akan diperiksa ulang dan dipotong saat persetujuan, mulai dari bulan paling lama.</p>
        @if ($ujDitahanBalance < (int) $invoice->total_nominal)<p class="text-danger mb-0 mt-2">Saldo saat ini tidak mencukupi untuk menyetujui invoice ini.</p>@endif
    @endif
    @if ($ujDitahanPayments->isNotEmpty())
    <div class="table-responsive mt-3"><table class="table table-sm mb-0"><thead class="table-success"><tr><th>Bulan sumber saldo</th><th class="text-end">Nominal dipotong</th><th>Tanggal pemotongan</th></tr></thead><tbody>
    @foreach ($ujDitahanPayments as $payment)
        <tr><td>{{ str_pad((string) $payment->ujDitahan->bulan, 2, '0', STR_PAD_LEFT) }}/{{ $payment->ujDitahan->tahun }}</td><td class="text-end">Rp {{ number_format($payment->nominal, 0, ',', '.') }}</td><td>{{ $payment->created_at->format('d-m-Y H:i') }}</td></tr>
    @endforeach
    </tbody></table></div>
    @endif
</div></div>
@endif
