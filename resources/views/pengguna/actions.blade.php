<div class="d-flex justify-content-center gap-2 text-nowrap">
    <button type="button" class="btn btn-outline-primary btn-sm user-edit" data-url="{{ route('pengguna.show', $user->id) }}" data-update-url="{{ route('pengguna.update', $user->id) }}" aria-label="Ubah pengguna {{ $user->username }}"><i class="fa fa-pencil me-1" aria-hidden="true"></i> Ubah</button>
    @if ((int) $user->id !== (int) auth()->id())
        <button type="button" class="btn btn-outline-danger btn-sm user-delete" data-url="{{ route('pengguna.destroy', $user->id) }}" data-name="{{ $user->username }}" aria-label="Hapus pengguna {{ $user->username }}"><i class="fa fa-trash-o me-1" aria-hidden="true"></i> Hapus</button>
    @endif
</div>
