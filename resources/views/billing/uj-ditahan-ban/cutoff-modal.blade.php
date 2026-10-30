<div class="modal fade ban-modal ban-modal--cutoff" id="cutoff-ban" tabindex="-1" aria-labelledby="cutoff-ban-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow">
        <div class="modal-header"><h5 class="modal-title fw-bold" id="cutoff-ban-title"><i class="fa fa-scissors" aria-hidden="true"></i>Cutoff UJ Ditahan Ban</h5><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <form method="POST" data-cutoff-form data-maintenance-form data-confirm="Catat seluruh saldo periode ini keluar ke rekening driver? Saldo akan menjadi nol." novalidate>
            @csrf
            <input type="hidden" name="request_token" value="">
            <div class="modal-body p-4">
                <p>Seluruh saldo kendaraan <strong data-cutoff-vehicle></strong> periode <strong data-cutoff-periode></strong> dicatat keluar ke rekening driver.</p>
                <div class="bg-light rounded p-3 mb-3"><div class="text-muted small">Saldo yang akan di-cutoff</div><div class="fs-4 fw-bold text-danger" data-cutoff-saldo></div></div>
                <dl class="row mb-0"><dt class="col-4">Driver</dt><dd class="col-8" data-cutoff-driver></dd><dt class="col-4">Bank</dt><dd class="col-8" data-cutoff-bank></dd><dt class="col-4">No. rekening</dt><dd class="col-8" data-cutoff-rekening></dd><dt class="col-4">Atas nama</dt><dd class="col-8" data-cutoff-nama></dd></dl>
                <div class="small text-muted mt-3">Rekening driver wajib lengkap. Saldo dan rekening diperiksa ulang saat disimpan.</div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger"><i class="fa fa-check-circle me-2" aria-hidden="true"></i>Proses cutoff</button></div>
        </form>
    </div></div>
</div>
