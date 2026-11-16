{{--
    Pop-up "Ganti" akun vendor pada statistik Ban Luar, Aki, dan Filter & Oli: mencatat
    penggantian langsung ke riwayat kendaraan (sama dengan aplikasi mobile).
    Parameter: $kind ('ban' | 'aki' | 'filter-oli'), $vehicle. Tombol pemicu memakai
    class "vendor-ganti-btn" dengan data-option-id dan data-option-name.
--}}
@php
    $tire = $kind === 'ban';
    $filter = $kind === 'filter-oli';
    $label = ['ban' => 'Ban', 'aki' => 'Aki', 'filter-oli' => 'Filter & Oli'][$kind];
    $optionField = $filter ? 'category_id' : 'position_id';
    $action = route(['ban' => 'statistik.ban-luar.ganti', 'aki' => 'statistik.aki.ganti', 'filter-oli' => 'statistik.filter-oli.ganti'][$kind], $vehicle->id);
@endphp
<div class="modal fade" id="vendorGantiModal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="vendorGantiTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="vendorGantiTitle">Ganti {{ $label }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form method="post" action="{{ $action }}" id="vendorGantiForm">
                @csrf
                <input type="hidden" name="{{ $optionField }}" id="vendorGantiOption" value="{{ old($optionField) }}">
                <input type="hidden" id="vendorGantiOptionName" value="{{ old('option_name') }}" name="option_name">
                <div class="modal-body py-4">
                    <p class="mb-1">Nolam <strong>{{ $vehicle->nomor_lambung }}</strong> · {{ $filter ? 'Kategori' : 'Posisi' }} <strong id="vendorGantiOptionLabel">{{ old('option_name') }}</strong></p>
                    <p class="small text-muted mb-3">Langsung tercatat sebagai riwayat kendaraan.</p>
                    @if ($tire)
                    <div class="btn-group w-100 mb-3" role="group" aria-label="Sumber ban">
                        <input type="radio" class="btn-check" name="source" id="vendorGantiBaru" value="baru" @checked(old('source', 'baru') === 'baru')>
                        <label class="btn btn-outline-primary" for="vendorGantiBaru">Ban baru</label>
                        <input type="radio" class="btn-check" name="source" id="vendorGantiSerep" value="serep" @checked(old('source') === 'serep')>
                        <label class="btn btn-outline-primary" for="vendorGantiSerep">Dari serep</label>
                    </div>
                    <p class="small text-warning-emphasis d-none" id="vendorGantiSerepNote">Ban serep dipasang ke posisi ini dan ban lama di posisi tersebut menjadi serep.</p>
                    @endif
                    <div id="vendorGantiFields">
                        <div class="mb-3">
                            <label for="vendorGantiBrand" class="form-label">Merk</label>
                            <input type="text" class="form-control text-uppercase" name="brand" id="vendorGantiBrand" maxlength="100" value="{{ old('brand') }}" required>
                        </div>
                        @unless ($filter)
                        <div class="mb-3">
                            <label for="vendorGantiSerial" class="form-label">Nomor seri{{ $tire ? '' : ' (opsional)' }}</label>
                            <input type="text" class="form-control text-uppercase" name="serial_number" id="vendorGantiSerial" maxlength="100" value="{{ old('serial_number') }}" @required($tire)>
                        </div>
                        @endunless
                        <div class="mb-3">
                            <label for="vendorGantiCondition" class="form-label">Kondisi</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="condition" id="vendorGantiCondition" min="1" max="100" value="{{ old('condition', 100) }}" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="vendorGantiDate" class="form-label">Tanggal ganti</label>
                        <input type="date" class="form-control" name="replaced_on" id="vendorGantiDate" max="{{ now()->toDateString() }}" value="{{ old('replaced_on', now()->toDateString()) }}" required>
                    </div>
                    @if ($tire || $filter)
                    <p class="small text-muted mb-0">Ritase dihitung otomatis dari perjalanan sejak tanggal ganti.</p>
                    @endif
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-check me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('js')
<script>
$(function () {
    const spare = @json(\App\Services\MaintenanceLogWriter::SPARE_TIRE_POSITION);
    const modal = $('#vendorGantiModal');
    function sync() {
        const fromSpare = $('#vendorGantiSerep').is(':checked');
        // Posisi serep tidak bisa diisi dari ban serep.
        const isSpare = Number($('#vendorGantiOption').val()) === spare;
        $('#vendorGantiSerep').prop('disabled', isSpare);
        if (isSpare && fromSpare) $('#vendorGantiBaru').prop('checked', true);
        const hide = $('#vendorGantiSerep').is(':checked');
        $('#vendorGantiFields').toggleClass('d-none', hide).find('input').prop('disabled', hide);
        $('#vendorGantiSerepNote').toggleClass('d-none', !hide);
    }
    $(document).on('click', '.vendor-ganti-btn', function () {
        const form = $('#vendorGantiForm')[0];
        form.reset();
        $('#vendorGantiBrand, #vendorGantiSerial').val('');
        $('#vendorGantiCondition').val(100);
        $('#vendorGantiDate').val(@json(now()->toDateString()));
        $('#vendorGantiOption').val($(this).data('option-id'));
        $('#vendorGantiOptionName').val($(this).data('option-name'));
        $('#vendorGantiOptionLabel').text($(this).data('option-name'));
        sync();
    });
    $('input[name="source"]').on('change', sync);
    $('#vendorGantiForm').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({title: 'Simpan penggantian?', text: 'Data langsung tercatat sebagai riwayat kendaraan.', icon: 'question',
            showCancelButton: true, confirmButtonText: 'Ya, simpan', cancelButtonText: 'Batal'})
            .then(result => { if (result.isConfirmed) form.submit(); });
    });
    @if ($errors->any() && old($optionField))
    sync();
    modal.modal('show');
    @endif
});
</script>
@endpush
