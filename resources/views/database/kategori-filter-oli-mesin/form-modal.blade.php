<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Title" aria-hidden="true" @if ($showErrors) data-reopen-modal @endif>
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 rounded-4 shadow" data-category-form novalidate action="{{ $isEditing ? route('database.kategori-filter-oli-mesin.update', $item) : route('database.kategori-filter-oli-mesin.store') }}" method="post">
            @csrf
            @if ($isEditing)
                @method('patch')
                <input type="hidden" name="kategori_id" value="{{ $item->id }}">
            @endif
            <div class="modal-header border-0 px-4 pt-4">
                <h2 class="modal-title fs-5 fw-bold" id="{{ $modalId }}Title">{{ $isEditing ? 'Edit Kategori' : 'Tambah Kategori' }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body px-4">
                <p class="text-muted small mb-4">Atur nama kategori dan batas ritase sebagai acuan perawatan mesin.</p>
                <div class="mb-3">
                    <label for="{{ $modalId }}Nama" class="form-label fw-semibold">Nama kategori</label>
                    <input id="{{ $modalId }}Nama" name="nama" value="{{ $showErrors ? old('nama') : ($isEditing ? $item->nama : '') }}" class="form-control {{ $showErrors && $errors->has('nama') ? 'is-invalid' : '' }}" maxlength="255" placeholder="Contoh: Filter Solar JAF20" required>
                    @if ($showErrors) @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                </div>
                <div>
                    <label for="{{ $modalId }}Limit" class="form-label fw-semibold">Limit ritase</label>
                    <div class="input-group has-validation">
                        <input id="{{ $modalId }}Limit" name="limit_ritase" type="number" min="1" max="4294967295" step="1" value="{{ $showErrors ? old('limit_ritase') : ($isEditing ? $item->limit_ritase : '') }}" class="form-control {{ $showErrors && $errors->has('limit_ritase') ? 'is-invalid' : '' }}" placeholder="Masukkan jumlah ritase" required>
                        <span class="input-group-text bg-light">rit</span>
                        @if ($showErrors) @error('limit_ritase')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
                    </div>
                    <div class="form-text">Gunakan bilangan bulat, minimal 1 rit.</div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">{{ $isEditing ? 'Simpan Perubahan' : 'Simpan Kategori' }}</button>
            </div>
        </form>
    </div>
</div>
