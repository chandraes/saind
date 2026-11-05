@can('inbox', \App\Models\LoadNoteDraft::class)
<div class="col-md-2 text-center mt-5">
    <a href="{{ route('billing.nota-muat.index') }}" class="text-decoration-none">
        <span class="position-relative d-inline-block align-middle">
            <img src="{{ asset('images/nota-muat-masuk.svg') }}" alt="" width="70" height="70">
            @if (($pendingLoadNoteCount ?? 0) > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-white" aria-label="{{ $pendingLoadNoteCount }} nota menunggu pemeriksaan">{{ $pendingLoadNoteCount }}</span>
            @endif
        </span>
        <h4 class="mt-3">Nota Muat Masuk</h4>
        <span class="text-muted">Kiriman aplikasi mobile</span>
    </a>
</div>
@endcan
