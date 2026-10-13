<div class="modal fade" id="user-modal" tabindex="-1" aria-labelledby="user-modal-title" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header px-4 py-3">
                <div><h5 class="modal-title fw-bold" id="user-modal-title">Tambah Pengguna</h5><p class="small text-muted mb-0">Lengkapi identitas dan akses akun pengguna.</p></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="user-form" action="{{ route('pengguna.store') }}" method="POST" class="d-flex flex-column overflow-hidden">
                @csrf
                <div class="modal-body p-4">@include('pengguna.edit')</div>
                <div class="modal-footer px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="user-save"><i class="fa fa-check me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
