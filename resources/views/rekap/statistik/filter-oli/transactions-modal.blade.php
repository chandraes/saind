<div class="modal fade" id="filterRitaseModal" tabindex="-1" aria-labelledby="filterRitaseTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"><div class="modal-content border-0 rounded-4 shadow">
        <div class="modal-header"><h2 id="filterRitaseTitle" class="modal-title fs-5 fw-bold">Transaksi Pembentuk Ritase</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body" data-ritase-content aria-live="polite"></div>
    </div></div>
</div>
@pushOnce('js')
<script src="{{ asset('assets/js/filter-oli-statistics.js') }}" defer></script>
@endPushOnce
