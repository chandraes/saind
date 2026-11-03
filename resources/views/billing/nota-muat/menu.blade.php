@can('reviewAny', \App\Models\LoadNoteDraft::class)
<div class="col-md-2 text-center mt-5">
    <a href="{{ route('billing.nota-muat.index') }}" class="text-decoration-none">
        <img src="{{ asset('images/uang-jalan.svg') }}" alt="" width="70">
        <h4 class="mt-3">Nota Muat Masuk</h4>
        <span class="text-muted">Kiriman aplikasi mobile</span>
    </a>
</div>
@endcan
