<div class="route-row-actions">
    <button type="button" class="btn btn-outline-secondary btn-sm route-edit" data-id="{{ $rute->id }}" data-name="{{ $rute->nama }}" data-distance="{{ $rute->jarak }}" data-amount="{{ $rute->uang_jalan }}" data-held="{{ $rute->uj_ditahan }}" aria-label="Edit rute {{ $rute->nama }}">Edit</button>
    <form action="{{ route('rute.destroy', $rute) }}" method="POST" class="deleteForm d-inline">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-outline-danger btn-sm" aria-label="Hapus rute {{ $rute->nama }}" title="Hapus rute"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
    </form>
</div>
