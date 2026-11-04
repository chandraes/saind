<section class="card shadow-sm mt-4" aria-labelledby="note-history-title">
    <div class="card-header bg-white py-3"><h2 class="h6 fw-bold mb-0" id="note-history-title"><i class="fa fa-history me-1" aria-hidden="true"></i> Riwayat pemeriksaan</h2></div>
    <div class="card-body">
        @php($changeLabels = ['vehicle_number' => 'Nomor lambung', 'mine_name' => 'Customer / tambang', 'rute_name' => 'Rute', 'ticket_number' => 'Nomor nota', 'loaded_on' => 'Tanggal muat', 'gross_kg' => 'Gross (kg)', 'tare_kg' => 'Tare (kg)', 'net_kg' => 'Netto (kg)', 'notes' => 'Catatan', 'status' => 'Status', 'transfer_override' => 'Nominal transfer khusus (Rp)', 'transaksi_id' => 'Transaksi'])
        @forelse ($note->events as $event)
            <article class="border-start border-3 ps-3 py-2 mb-3">
                <div class="d-flex flex-wrap justify-content-between gap-2"><strong>{{ match ($event->action) { 'payment_updated' => 'Perubahan nominal uang jalan', 'updated' => 'Koreksi nota', 'rejected' => 'Nota ditolak', 'cancelled' => 'Nota dibatalkan', 'processed' => 'Diteruskan ke uang jalan', default => $event->action } }}</strong><small class="text-muted">{{ $event->created_at->format('d-m-Y H:i:s') }}</small></div>
                <small class="text-muted">{{ $event->actor?->name ?? '-' }}</small><p class="text-break mt-2 mb-1">{{ $event->reason }}</p>
                <details><summary class="small text-primary">Lihat perubahan</summary><dl class="row small mt-2 mb-0">
                    @foreach ($changeLabels as $field => $label)
                        @if (($event->before[$field] ?? null) !== ($event->after[$field] ?? null))
                        <dt class="col-sm-4">{{ $label }}</dt><dd class="col-sm-8 text-break">{{ $field === 'transfer_override' ? (isset($event->before[$field]) ? 'Rp '.number_format($event->before[$field], 0, ',', '.') : 'Pengaturan sistem') : ($event->before[$field] ?? '-') }} <span aria-label="menjadi">→</span> {{ $field === 'transfer_override' ? (isset($event->after[$field]) ? 'Rp '.number_format($event->after[$field], 0, ',', '.') : 'Pengaturan sistem') : ($event->after[$field] ?? '-') }}</dd>
                        @endif
                    @endforeach
                </dl></details>
            </article>
        @empty
            <p class="text-muted mb-0">Belum ada perubahan. Nota masih sesuai kiriman vendor.</p>
        @endforelse
    </div>
</section>
