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
                <div class="user-role-filter">
                    <label for="user-search" class="form-label small fw-bold">Cari pengguna</label>
                    <input type="search" id="user-search" class="form-control" maxlength="100" placeholder="Nama atau username…">
                </div>
                @if ($isSu)
                    <div class="btn-group" role="group" aria-label="Kelompok akun">
                        <button type="button" class="btn btn-outline-success active" data-user-scope="active" aria-pressed="true">Akun Aktif</button>
                        <button type="button" class="btn btn-outline-success" data-user-scope="inactive" aria-pressed="false">Akun Nonaktif</button>
                    </div>
                @endif
                <div class="user-role-filter">
                    <label for="user-role-filter" class="form-label small fw-bold">Filter role</label>
                    <select id="user-role-filter" class="form-select">
                        <option value="">Semua role</option>
                        @foreach ($roles as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="user" class="table table-bordered table-hover align-middle w-100">
                    <thead class="table-success"><tr><th>No</th><th>Username</th><th>Nama</th><th>Role</th><th>Vendor / Customer / Driver</th><th class="text-center">Aksi</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="user-list-feedback" class="text-center small py-2" role="status" aria-live="polite"></div>
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
<script src="{{ asset('assets/js/incremental-data-table.js') }}?v={{ filemtime(public_path('assets/js/incremental-data-table.js')) }}"></script>
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

    const tableView = createIncrementalDataTable('#user', {
        data: [], ordering: true, order: [[3, 'asc']],
        columns: [
            {data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
            {data: 'username', render: textRenderer},
            {data: 'name', render: textRenderer},
            {data: 'role', render: (value, type) => type === 'display' ? '<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">' + escapeText(roles[value] || value) + '</span>' : value},
            {data: null, orderable: false, render: (value, type, row) => escapeText(row.vendor_name || row.customer_name || row.driver_name || '—')},
            {data: 'action', orderable: false, searchable: false}
        ],
        language: {emptyTable: 'Tidak ada pengguna yang sesuai.'}
    });
    const table = tableView.grid;
    const listFeedback = $('#user-list-feedback');
    let accountScope = 'active';
    let listRequest = null;
    let offset = 0, exhausted = false, failed = false, listRevision = 0;
    let restore = null;
    let sortKey = JSON.stringify(table.order());
    function loadUsers() {
        if (listRequest || exhausted) return;
        failed = false;
        listFeedback.text('Memuat pengguna…');
        const revision = listRevision;
        listRequest = $.ajax({
            url: @json(route('pengguna.index')), dataType: 'json',
            data: {start: offset, length: 25, order: table.order().map(([column, dir]) => ({column, dir})),
                role_filter: $('#user-role-filter').val(), account_scope: accountScope, search: {value: $('#user-search').val()}}
        }).done(response => {
            if (revision !== listRevision) return;
            tableView.add(response.data);
            offset += response.data.length;
            exhausted = response.data.length === 0 || offset >= response.recordsFiltered;
            if (restore && (exhausted || offset >= restore.rows)) {
                tableView.body.scrollTop = restore.top;
                tableView.body.scrollLeft = restore.left;
                restore = null;
            }
            listFeedback.empty();
        }).fail((xhr, status) => {
            if (revision !== listRevision || status === 'abort') return;
            failed = true;
            listFeedback.text('Daftar pengguna gagal dimuat. ').append(
                $('<button type="button" class="btn btn-outline-primary btn-sm">Coba lagi</button>').on('click', loadUsers)
            );
        }).always(() => {
            if (revision !== listRevision) return;
            listRequest = null;
            checkUsers();
        });
    }
    function checkUsers() {
        if (!failed && (restore || tableView.nearEnd())) loadUsers();
    }
    function reloadUsers(keepPosition = false) {
        restore = keepPosition ? {rows: offset, top: tableView.body.scrollTop, left: tableView.body.scrollLeft} : null;
        listRevision++;
        listRequest?.abort();
        listRequest = null;
        offset = 0;
        exhausted = false;
        table.clear().draw(false);
        tableView.body.scrollTop = 0;
        loadUsers();
    }

    let searchTimer;
    $('#user-search').on('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => reloadUsers(), 350);
    });
    $('#user-role-filter').select2({width: '100%'}).on('change', () => reloadUsers());
    table.on('order.dt', () => {
        const next = JSON.stringify(table.order());
        if (next === sortKey) return;
        sortKey = next;
        queueMicrotask(() => reloadUsers());
    });
    $('[data-user-scope]').on('click', function () {
        if (accountScope === this.dataset.userScope) return;
        accountScope = this.dataset.userScope;
        $('[data-user-scope]').removeClass('active').attr('aria-pressed', 'false');
        $(this).addClass('active').attr('aria-pressed', 'true');
        reloadUsers();
    });
    tableView.watch(checkUsers);
    loadUsers();
    $('#user-role').select2({width: '100%', dropdownParent: $('#user-modal')}).on('change', toggleRelations);
    ['vendor', 'customer', 'driver'].forEach(type => {
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
        ['vendor', 'customer', 'driver'].forEach(type => {
            const relationRoles = {vendor: ['vendor', 'vendor-induk', 'vendor-operational'], customer: ['customer', 'customer-admin'], driver: ['driver']};
            const active = relationRoles[type].includes(role);
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
        $('#user-vendor, #user-customer, #user-driver').empty().trigger('change');
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
                ['vendor', 'customer', 'driver'].forEach(type => {
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
                reloadUsers(editing);
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
    let changingAccess = false;
    $('#user').on('click', '.user-access-action', async function () {
        if (changingAccess || saving) return;
        changingAccess = true;
        const button = $(this);
        const remove = button.hasClass('user-delete');
        const reactivate = button.hasClass('user-reactivate');
        const username = button.data('name');
        const verb = remove ? 'Hapus' : reactivate ? 'Aktifkan kembali' : 'Nonaktifkan';
        const description = remove ? ' akan dihapus permanen.'
            : reactivate ? ' dapat login kembali setelah diaktifkan.'
                : ' akan kehilangan akses. Riwayat akun tetap tersimpan.';
        const confirmation = await Swal.fire({
            icon: 'warning', title: verb + ' pengguna?',
            text: 'Akun "' + username + '"' + description,
            showCancelButton: true, confirmButtonText: 'Ya, ' + verb.toLowerCase(),
            cancelButtonText: 'Batal', confirmButtonColor: remove ? '#dc3545' : '#198754',
        });
        if (!confirmation.isConfirmed) {
            changingAccess = false;
            return;
        }
        button.prop('disabled', true);
        Swal.fire({title: 'Memproses…', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading()});
        $.ajax({url: button.data('url'), method: remove ? 'DELETE' : 'PATCH', headers: {'X-CSRF-TOKEN': csrf, Accept: 'application/json'}})
            .done(response => {
                reloadUsers(true);
                Swal.fire({icon: 'success', title: 'Berhasil', text: response.message});
            }).fail(xhr => {
                if (xhr.status === 422 && xhr.responseJSON?.errors) Swal.fire({icon: 'error', title: 'Tidak berhasil', text: Object.values(xhr.responseJSON.errors).flat().join('\n')});
                else showError(xhr);
            }).always(() => { changingAccess = false; button.prop('disabled', false); });
    });
});
</script>
@endpush
