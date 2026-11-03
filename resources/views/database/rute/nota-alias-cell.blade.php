<div class="note-alias-cell">
    <div class="note-alias-preview">
        @forelse ($rute->noteAliases as $alias)
            <span class="note-alias-label">{{ $alias->name }}</span>
        @empty
            <span class="text-muted small">Belum ada alias</span>
        @endforelse
    </div>
    <button type="button" class="btn btn-outline-primary btn-sm note-alias-trigger" data-note-alias-url="{{ route('rute.nota-alias.index', $rute) }}" aria-haspopup="dialog" aria-controls="note-alias-modal" aria-label="Kelola alias rute {{ $rute->nama }}">
        <i class="fa fa-plus" aria-hidden="true"></i> Tambah / edit
    </button>
</div>
