@can('reviewAny', \App\Models\LoadNoteDraft::class)
<div class="col-md-2 text-center mt-5">
    <a href="{{ route('billing.nota-muat.index') }}" class="text-decoration-none">
        <img src="{{ asset('images/nota-muat-masuk.svg') }}" alt="" width="70" height="70">
        <h4 class="mt-3">Nota Muat Masuk
            @if (($pendingLoadNoteCount ?? 0) > 0)
                <span class="badge bg-danger rounded-pill ms-1" aria-label="{{ $pendingLoadNoteCount }} nota menunggu pemeriksaan">{{ $pendingLoadNoteCount }}</span>
            @endif
        </h4>
        <span class="text-muted">Kiriman aplikasi mobile</span>
    </a>
</div>
@endcan
