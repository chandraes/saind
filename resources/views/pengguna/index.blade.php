@extends('layouts.app')

@section('content')
<div class="container pengguna-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="text-success small fw-bold text-uppercase mb-1">Pengaturan aplikasi</div>
            <h2 class="fw-bold mb-1">Manajemen Pengguna</h2>
            <p class="text-muted mb-0">Kelola akun, role, dan akses vendor atau customer.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('pengaturan') }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Pengaturan</a>
            <button type="button" id="user-create" class="btn btn-success"><i class="fa fa-plus me-1"></i> Tambah Pengguna</button>
        </div>
    </div>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div><h5 class="fw-bold mb-1">Daftar Pengguna</h5><span class="text-muted small">Cari akun atau saring berdasarkan role.</span></div>
                <div class="user-role-filter">
                    <label for="user-role-filter" class="form-label small fw-bold">Filter role</label>
                    <select id="user-role-filter" class="form-select">
                        <option value="">Semua role</option>
                        @foreach ($roles as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="user" class="table table-hover align-middle w-100">
                    <thead class="table-success"><tr><th>No</th><th>Username</th><th>Nama</th><th>Role</th><th>Vendor / Customer</th><th class="text-center">Aksi</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    @include('swal')
    @include('pengguna.create')
</div>
@endsection

@push('css')
<link href="{{ asset('assets/css/dt.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet">
<style>
    .pengguna-page .card { overflow: hidden; }
    .pengguna-page .user-role-filter { width: 240px; max-width: 100%; }
    .pengguna-page .table thead th { padding: 1rem .75rem; white-space: nowrap; }
    .pengguna-page .table td { padding: .9rem .75rem; }
    .pengguna-page .select2-container { max-width: 100%; }
    .pengguna-page .select2-container--default .select2-selection--single { min-height: 38px; border-color: #ced4da; border-radius: .375rem; }
    .pengguna-page .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px; }
    .pengguna-page .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
    #user-modal .modal-header { background: #edf7f1; }
    #user-modal .modal-footer { background: #f8faf9; }
    #user-modal .invalid-feedback { display: block; }
</style>
@endpush

@push('js')
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script>
$(function () {
    const roles = @json($roles);
    const storeUrl = @json(route('pengguna.store'));
    const modalElement = document.getElementById('user-modal');
    const form = document.getElementById('user-form');
    let editing = false;
    let saving = false;
    let detailRequest = null;
    const csrf = $('meta[name="csrf-token"]').attr('content');
    const textRenderer = $.fn.dataTable.render.text();
    const escapeText = value => $('<span>').text(value == null ? '' : value).html();
    const showError = xhr => Swal.fire({icon: 'error', title: 'Tidak berhasil', text: xhr.status === 419 ? 'Sesi telah berakhir. Muat ulang halaman lalu coba kembali.' : xhr.status >= 500 ? 'Terjadi gangguan server. Silakan coba kembali.' : (xhr.responseJSON && xhr.responseJSON.message) || 'Data tidak dapat dimuat. Periksa koneksi lalu coba kembali.'});

    const table = $('#user').DataTable({
        processing: true, serverSide: true, deferRender: true, searchDelay: 500,
        pageLength: 10, lengthMenu: [10, 25, 50, 100], order: [[3, 'asc']],
        ajax: {url: @json(route('pengguna.index')), data: data => { data.role_filter = $('#user-role-filter').val(); }, error: showError},
        columns: [
            {data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
            {data: 'username', render: textRenderer},
            {data: 'name', render: textRenderer},
            {data: 'role', render: value => '<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">' + escapeText(roles[value] || value) + '</span>'},
            {data: null, orderable: false, render: (value, type, row) => escapeText(row.vendor_name || row.customer_name || '—')},
            {data: 'action', orderable: false, searchable: false}
        ],
        language: {processing: 'Memuat pengguna…', search: 'Cari:', searchPlaceholder: 'Nama atau username…', lengthMenu: 'Tampilkan _MENU_ pengguna', info: 'Menampilkan _START_–_END_ dari _TOTAL_ pengguna', infoEmpty: 'Belum ada pengguna', infoFiltered: '(disaring dari _MAX_ pengguna)', emptyTable: 'Belum ada pengguna. Tambahkan akun pertama Anda.', zeroRecords: 'Tidak ada pengguna yang sesuai pencarian.', paginate: {first: 'Awal', last: 'Akhir', next: 'Berikutnya', previous: 'Sebelumnya'}}
    });
    $('#user-role-filter').select2({width: '100%'}).on('change', () => table.ajax.reload());
    $('#user_length select').select2({width: 'auto', minimumResultsForSearch: Infinity});
    $('#user-role').select2({width: '100%', dropdownParent: $('#user-modal')}).on('change', toggleRelations);
    ['vendor', 'customer'].forEach(type => {
        $('#user-' + type).select2({
            width: '100%', dropdownParent: $('#user-modal'), placeholder: 'Cari ' + type + '…', allowClear: true,
            ajax: {url: @json(route('pengguna.options')), dataType: 'json', delay: 350,
                data: params => ({type: type, q: params.term || '', page: params.page || 1}),
                processResults: data => data,
                error: xhr => { if (xhr.statusText !== 'abort') showError(xhr); }
            },
            language: {searching: () => 'Mencari…', noResults: () => 'Data tidak ditemukan', loadingMore: () => 'Memuat data berikutnya…'}
        });
    });

    function toggleRelations() {
        const role = $('#user-role').val();
        ['vendor', 'customer'].forEach(type => {
            const active = type === 'vendor' ? ['vendor', 'vendor-operational'].includes(role) : ['customer', 'customer-admin'].includes(role);
            $('#user-' + type + '-group').prop('hidden', !active);
            $('#user-' + type).prop('disabled', !active).prop('required', active);
            if (!active) $('#user-' + type).val(null).trigger('change');
        });
    }
    function clearErrors() {
        $(form).find('.is-invalid').removeClass('is-invalid');
        $(form).find('[data-error]').text('');
    }
    function resetForm() {
        form.reset(); clearErrors();
        $('#user-vendor, #user-customer').empty().trigger('change');
        $('#user-role').val('user').trigger('change');
        $('#user-password').attr('type', 'password').prop('required', !editing);
        $('#user-password-toggle i').attr('class', 'fa fa-eye');
        $('#user-password-toggle').attr('aria-label', 'Tampilkan password');
        $('#user-password-help').text(editing ? 'Kosongkan jika password tidak diubah.' : 'Gunakan minimal 6 karakter.');
        $('#user-modal-title').text(editing ? 'Ubah Pengguna' : 'Tambah Pengguna');
        form.action = storeUrl;
    }
    function showModal() {
        $('#user-modal').modal('show');
    }
    $('#user-create').on('click', function () {
        if (detailRequest) detailRequest.abort();
        editing = false; resetForm(); showModal();
    });
    $('#user').on('click', '.user-edit', function () {
        if (detailRequest) detailRequest.abort();
        const button = $(this).prop('disabled', true);
        detailRequest = $.ajax({url: button.data('url'), dataType: 'json'})
            .done(response => {
                editing = true; resetForm();
                const user = response.data;
                form.action = button.data('update-url');
                ['name', 'username', 'email'].forEach(field => $('#user-' + field).val(user[field] || ''));
                $('#user-role').val(user.role).trigger('change');
                ['vendor', 'customer'].forEach(type => {
                    if (user[type]) $('#user-' + type).append(new Option(user[type].nama, user[type].id, true, true)).trigger('change');
                });
                showModal();
            }).fail(xhr => { if (xhr.statusText !== 'abort') showError(xhr); })
            .always(() => { button.prop('disabled', false); });
    });
    $('#user-password-toggle').on('click', function () {
        const show = $('#user-password').attr('type') === 'password';
        $('#user-password').attr('type', show ? 'text' : 'password');
        $(this).attr('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password').find('i').attr('class', show ? 'fa fa-eye-slash' : 'fa fa-eye');
    });
    $(modalElement).on('shown.bs.modal', () => $('#user-name').trigger('focus'));
    $(modalElement).on('hide.bs.modal', event => { if (saving) event.preventDefault(); });
    $(form).on('submit', async function (event) {
        event.preventDefault();
        if (saving || !form.reportValidity()) return;
        const confirmation = await Swal.fire({icon: 'question', title: editing ? 'Simpan perubahan pengguna?' : 'Tambahkan pengguna?', showCancelButton: true, confirmButtonText: 'Ya, simpan', cancelButtonText: 'Batal', confirmButtonColor: '#198754'});
        if (!confirmation.isConfirmed || saving) return;
        clearErrors(); saving = true;
        const payload = $(form).serializeArray();
        if (editing) payload.push({name: '_method', value: 'PUT'});
        $('#user-save').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan…');
        $.ajax({url: form.action, method: 'POST', data: payload, headers: {Accept: 'application/json'}})
            .done(response => {
                saving = false;
                $('#user-modal').modal('hide');
                table.ajax.reload(null, !editing);
                Swal.fire({icon: 'success', title: 'Berhasil', text: response.message});
            }).fail(xhr => {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    Object.entries(errors).forEach(([field, messages]) => {
                        $(form).find('[name="' + field + '"]').addClass('is-invalid');
                        $(form).find('[data-error="' + field + '"]').text(messages[0]);
                    });
                    Swal.fire({icon: 'error', title: 'Periksa kembali isian', text: Object.values(errors).flat().join('\n')});
                } else showError(xhr);
            }).always(() => { saving = false; $('#user-save').prop('disabled', false).html('<i class="fa fa-check me-1"></i> Simpan'); });
    });
    $('#user').on('click', '.user-delete', async function () {
        const button = $(this);
        const confirmation = await Swal.fire({icon: 'warning', title: 'Hapus pengguna?', text: 'Akun "' + button.data('name') + '" akan dihapus. Tindakan ini tidak dapat dibatalkan.', showCancelButton: true, confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc3545'});
        if (!confirmation.isConfirmed) return;
        button.prop('disabled', true);
        $.ajax({url: button.data('url'), method: 'DELETE', headers: {'X-CSRF-TOKEN': csrf, Accept: 'application/json'}})
            .done(response => {
                const info = table.page.info();
                if (info.end - info.start === 1 && info.page > 0) table.page(info.page - 1);
                table.ajax.reload(null, false);
                Swal.fire({icon: 'success', title: 'Berhasil', text: response.message});
            }).fail(xhr => {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) Swal.fire({icon: 'error', title: 'Tidak dapat dihapus', text: Object.values(xhr.responseJSON.errors).flat().join('\n')});
                else showError(xhr);
            }).always(() => button.prop('disabled', false));
    });
});
</script>
@endpush
