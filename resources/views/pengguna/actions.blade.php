<div class="d-flex flex-wrap justify-content-center gap-2">
    @unless ($inactive)
        <button type="button" class="btn btn-outline-primary btn-sm user-edit" data-url="{{ route('pengguna.show', $user->id) }}" data-update-url="{{ route('pengguna.update', $user->id) }}" aria-label="Ubah pengguna {{ $user->username }}"><i class="fa fa-pencil me-1" aria-hidden="true"></i> Ubah</button>
        @if ((int) $user->id !== (int) auth()->id())
            <button type="button" class="btn btn-outline-secondary btn-sm user-access-action" data-url="{{ route('pengguna.deactivate', $user->id) }}" data-name="{{ $user->username }}" aria-label="Nonaktifkan pengguna {{ $user->username }}"><i class="fa fa-ban me-1" aria-hidden="true"></i> Nonaktifkan</button>
        @endif
    @endunless
    @if ($isSu && $inactive)
        <button type="button" class="btn btn-outline-success btn-sm user-access-action user-reactivate" data-url="{{ route('pengguna.reactivate', $user->id) }}" data-name="{{ $user->username }}" aria-label="Aktifkan kembali pengguna {{ $user->username }}"><i class="fa fa-check me-1" aria-hidden="true"></i> Aktifkan Kembali</button>
    @endif
    @if ($isSu)
        <button type="button" class="btn btn-outline-danger btn-sm user-access-action user-delete" data-url="{{ route('pengguna.destroy', $user->id) }}" data-name="{{ $user->username }}" aria-label="Hapus pengguna {{ $user->username }}"><i class="fa fa-trash-o me-1" aria-hidden="true"></i> Hapus</button>
    @endif
</div>
