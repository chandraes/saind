<div class="note-alias-context mb-4">
    <span class="text-muted small d-block mb-1">Rute SAIND</span>
    <h3 class="h6 mb-0">{{ $rute->nama }}</h3>
</div>
<p class="small text-muted">Gunakan nama rute yang tercetak pada nota. Satu rute dapat memiliki beberapa alias, dan setiap alias harus unik.</p>
<form id="note-alias-form" method="POST" action="{{ route('rute.nota-alias.store', $rute) }}" data-create-url="{{ route('rute.nota-alias.store', $rute) }}">
    @csrf
    <label for="note-alias-name" class="form-label fw-semibold" id="note-alias-field-label">Tambah alias</label>
    <input id="note-alias-name" name="name" class="form-control" maxlength="191" placeholder="Contoh: STP CPP-02 - SDJ" aria-describedby="note-alias-hint note-alias-error" required>
    <div class="form-text" id="note-alias-hint">Spasi dan tanda baca diabaikan saat pencocokan. Angka tetap dibedakan.</div>
    <div class="invalid-feedback" id="note-alias-error" aria-live="polite"></div>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary" id="note-alias-submit">Tambah alias</button>
        <button type="button" class="btn btn-outline-secondary" id="note-alias-cancel-edit" hidden>Batal edit</button>
    </div>
</form>
<div class="d-flex align-items-center justify-content-between border-top pt-4 mt-4 mb-3">
    <h3 class="h6 mb-0">Alias terdaftar</h3><span class="badge bg-light text-dark border">{{ $rute->noteAliases->count() }}</span>
</div>
<div class="list-group list-group-flush">
    @forelse ($rute->noteAliases as $alias)
        <div class="list-group-item px-0 d-flex align-items-start justify-content-between gap-3">
            <span class="note-alias-value py-2">{{ $alias->name }}</span>
            <div class="d-flex gap-2 flex-shrink-0">
                <button type="button" class="btn btn-outline-primary btn-sm note-alias-edit" data-name="{{ $alias->name }}" data-update-url="{{ route('rute.nota-alias.update', [$rute, $alias]) }}">Edit</button>
                <form method="POST" class="note-alias-delete" action="{{ route('rute.nota-alias.destroy', [$rute, $alias]) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <div class="text-center py-4 text-muted"><i class="fa fa-tags mb-2" aria-hidden="true"></i><p class="mb-0">Belum ada alias untuk rute ini.</p></div>
    @endforelse
</div>
