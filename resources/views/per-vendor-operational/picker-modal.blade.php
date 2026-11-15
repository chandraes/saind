{{-- Pop-up "Pilih ..." (select2) sebelum membuka menu per vendor/customer. --}}
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="{{ $id }}-title"><i class="fa {{ $icon }} me-2" aria-hidden="true"></i>{{ $title }}</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <form action="{{ $action }}" method="get" @isset($pathTemplate) data-path-template="{{ $pathTemplate }}" @endisset>
            <div class="modal-body">
                <label for="{{ $id }}-select" class="form-label fw-semibold">{{ $label }} <span class="text-danger">*</span></label>
                <select class="form-select dashboard-picker-select" id="{{ $id }}-select" name="{{ $name }}" required data-placeholder="{{ $placeholder }}">
                    <option value="" selected disabled>{{ $placeholder }}</option>
                    @foreach ($options as $value => $text)
                    <option value="{{ $value }}">{{ $text }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary px-4">Lanjutkan <i class="fa fa-arrow-right ms-1" aria-hidden="true"></i></button>
            </div>
        </form>
    </div></div>
</div>
