<div class="modal fade" id="filterRitaseModal" data-vendor-readonly="{{ auth()->user()->role === 'vendor-operational' ? 'true' : 'false' }}" tabindex="-1" aria-labelledby="filterRitaseTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"><div class="modal-content border-0 rounded-4 shadow">
        <div class="modal-header"><h2 id="filterRitaseTitle" class="modal-title fs-5 fw-bold">Transaksi Pembentuk Ritase</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body" data-ritase-content aria-live="polite"></div>
    </div></div>
</div>
@pushOnce('js')
@if (auth()->user()->role === 'vendor-operational')
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
@once('incremental-data-table')
<script src="{{ asset('assets/js/incremental-data-table.js') }}?v={{ filemtime(public_path('assets/js/incremental-data-table.js')) }}"></script>
@endonce
<script src="{{ asset('assets/js/vendor-statistics-table.js') }}?v={{ filemtime(public_path('assets/js/vendor-statistics-table.js')) }}"></script>
@endif
<script src="{{ asset('assets/js/filter-oli-statistics.js') }}" defer></script>
@endPushOnce
