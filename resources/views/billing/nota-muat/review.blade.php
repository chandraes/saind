@if ($note->status === 'draft')
<div class="d-flex flex-wrap align-items-center gap-2 mb-4">
    <a class="btn btn-primary" href="{{ route('kas-uang-jalan.keluar', ['load_note_id' => $note->id]) }}"><i class="fa fa-arrow-right me-1" aria-hidden="true"></i> Teruskan ke uang jalan</a>
    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#note-edit-modal"><i class="fa fa-pencil me-1" aria-hidden="true"></i> Edit nota</button>
    <div class="ms-sm-auto d-flex gap-2">
        <button type="button" class="btn btn-outline-danger" data-note-decision="reject" data-bs-toggle="modal" data-bs-target="#note-decision-modal">Tolak</button>
        <button type="button" class="btn btn-outline-secondary" data-note-decision="cancel" data-bs-toggle="modal" data-bs-target="#note-decision-modal">Batalkan</button>
    </div>
</div>
<div class="modal fade" id="note-edit-modal" tabindex="-1" aria-labelledby="note-edit-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content border-0 shadow">
        <div class="modal-header"><h2 class="modal-title fs-5" id="note-edit-title">Koreksi nota {{ $note->ticket_number }}</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <form method="POST" action="{{ route('billing.nota-muat.update', $note) }}" data-note-review data-confirm="Simpan koreksi nota {{ $note->ticket_number }}?" novalidate>
            @csrf @method('PATCH')
            <input type="hidden" name="revision" value="{{ $note->revision }}">
            <div class="modal-body">
                <p class="text-muted small">Vendor: <strong>{{ $note->vendor?->nama }}</strong>. Foto asli tetap menjadi bukti; perubahan dicatat pada riwayat.</p>
                <div class="row g-3">
                    <div class="col-md-6"><label for="note-ticket" class="form-label">Nomor nota</label><input id="note-ticket" name="ticket_number" class="form-control" value="{{ $note->ticket_number }}" maxlength="100" required></div>
                    <div class="col-md-6"><label for="note-date" class="form-label">Tanggal muat (StampOut)</label><input id="note-date" name="loaded_on" type="date" class="form-control" value="{{ $note->loaded_on->format('Y-m-d') }}" required></div>
                    <div class="col-md-6"><label for="note-unit" class="form-label">Nomor lambung</label><select id="note-unit" name="vehicle_id" class="form-select note-search-select" required>
                        @foreach ($vehicles as $vehicle)<option value="{{ $vehicle->id }}" @selected($note->vehicle_id === $vehicle->id)>{{ $vehicle->nomor_lambung }}</option>@endforeach
                    </select></div>
                    <div class="col-md-6"><label for="note-customer" class="form-label">Customer / tambang</label><select id="note-customer" name="customer_id" class="form-select note-search-select" required>
                        <option value="">Pilih customer</option>@foreach ($customers as $customer)<option value="{{ $customer->id }}" @selected($note->customer_id === $customer->id)>{{ $customer->nama }}</option>@endforeach
                    </select></div>
                    <div class="col-12"><label for="note-route" class="form-label">Rute SAIND</label><select id="note-route" name="rute_id" class="form-select note-search-select" required>
                        <option value="">Pilih rute</option>@foreach ($rutes as $rute)<option value="{{ $rute->id }}" @selected($note->rute_id === $rute->id)>{{ $rute->nama }}</option>@endforeach
                    </select></div>
                    <div class="col-md-6"><label for="note-gross" class="form-label">Gross (ton)</label><input id="note-gross" name="gross_weight" type="number" min="0.001" max="1000" step="0.001" inputmode="decimal" class="form-control" value="{{ number_format($note->gross_kg / 1000, 3, '.', '') }}" required></div>
                    <div class="col-md-6"><label for="note-tare" class="form-label">Tare (ton)</label><input id="note-tare" name="tare_weight" type="number" min="0" max="1000" step="0.001" inputmode="decimal" class="form-control" value="{{ number_format($note->tare_kg / 1000, 3, '.', '') }}" required></div>
                    <div class="col-12"><label for="note-notes" class="form-label">Catatan nota</label><textarea id="note-notes" name="notes" class="form-control" maxlength="1000" rows="2">{{ $note->notes }}</textarea></div>
                    <div class="col-12"><label for="note-edit-reason" class="form-label">Alasan koreksi <span class="text-danger">*</span></label><textarea id="note-edit-reason" name="reason" class="form-control" maxlength="1000" rows="2" required placeholder="Jelaskan bagian yang dikoreksi berdasarkan foto"></textarea></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Kembali</button><button type="submit" class="btn btn-primary">Simpan koreksi</button></div>
        </form>
    </div></div>
</div>
<div class="modal fade" id="note-decision-modal" tabindex="-1" aria-labelledby="note-decision-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow">
        <div class="modal-header"><h2 class="modal-title fs-5" id="note-decision-title">Keputusan nota</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <form method="POST" action="{{ route('billing.nota-muat.decide', $note) }}" data-note-review data-note-decision-form novalidate>
            @csrf<input type="hidden" name="revision" value="{{ $note->revision }}"><input type="hidden" name="action">
            <div class="modal-body"><p>Nota <strong>{{ $note->ticket_number }}</strong>. Tindakan ini mengakhiri pemeriksaan nota dan membuka blokir nota tertunda kendaraan. Aturan kendaraan lainnya tetap berlaku.</p>
                <label for="note-decision-reason" class="form-label">Alasan <span class="text-danger">*</span></label><textarea id="note-decision-reason" name="reason" class="form-control" maxlength="1000" rows="3" required></textarea>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Kembali</button><button type="submit" class="btn btn-danger" id="note-decision-submit">Simpan keputusan</button></div>
        </form>
    </div></div>
</div>
@endif
