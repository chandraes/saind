@extends('layouts.app')
@section('content')
@php
    $headers = [
        'standby' => ['No', 'Nolam', 'Vendor', 'Waktu sejak bongkar terakhir'],
        'unloading' => ['No', 'Nolam', 'Vendor', 'Rute', 'Waktu sejak muat'],
        'filter_oil' => ['No', 'Nolam', 'Vendor', ...$categories->pluck('nama')->all()],
        'invoice_due' => ['No', 'Tambang', 'Nilai Tagihan', 'Tgl Jatuh Tempo', 'Sisa Hari'],
        'attendance' => ['No', 'Nama', 'Jabatan', 'Tidak masuk berturut-turut', 'Total tidak masuk'],
    ];
@endphp
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Pengingat</h1>
            <p class="text-muted mb-0">Daftar yang perlu ditindaklanjuti. Waktu berjalan dihitung saat halaman dibuka; isi daftar diperbarui berkala.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reminderSettingsModal"><i class="fa fa-sliders me-2"></i>Atur tampilan</button>
            <a class="btn btn-outline-secondary" href="{{ route('home') }}"><i class="fa fa-arrow-left me-2"></i>Dashboard</a>
        </div>
    </div>
    @include('swal')
    @if ($tables === [])
    <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5">
        <img src="{{ asset('images/pengingat.svg') }}" width="64" height="64" alt="" class="mb-3">
        <p class="mb-0">Semua tabel pengingat sedang dinonaktifkan. Aktifkan kembali melalui <strong>Atur tampilan</strong>.</p>
    </div></div>
    @endif
    @foreach ($tables as $table)
    <section class="card border-0 shadow-sm mb-4" id="reminder-{{ $table['key'] }}" aria-labelledby="reminder-title-{{ $table['key'] }}">
        <div class="card-header bg-white p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h2 class="h6 fw-bold mb-0" id="reminder-title-{{ $table['key'] }}">{{ $loop->iteration }}. {{ $table['title'] }}
                <span class="badge {{ $table['count'] > 0 ? 'bg-danger' : 'bg-secondary' }} ms-1" data-reminder-count>{{ $table['count'] }}</span>
            </h2>
            <span class="small text-muted">{{ $table['refreshed_at'] ? 'Diperbarui '.$table['refreshed_at']->format('d-m-Y H:i') : 'Belum pernah direkap' }}</span>
        </div>
        <div class="card-body p-3">
            @if ($table['key'] === 'attendance' && ! $attendanceEnabled)
            <div class="alert alert-light border small mb-3"><i class="fa fa-info-circle text-primary me-1"></i>Sinkron absensi belum aktif, sehingga tabel ini belum terisi.</div>
            @endif
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0" id="reminder-table-{{ $table['key'] }}">
                    <thead class="table-success"><tr>@foreach ($headers[$table['key']] as $header)<th class="text-center text-nowrap">{{ $header }}</th>@endforeach</tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>
    @endforeach
</div>
<div class="modal fade" id="reminderSettingsModal" tabindex="-1" aria-labelledby="reminderSettingsTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="reminderSettingsTitle">Atur tampilan pengingat</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <form method="post" action="{{ route('reminders.settings') }}" id="reminderSettingsForm">
            @csrf
            <div class="modal-body">
                <p class="small text-muted">Aktifkan tabel yang ingin dilihat dan atur urutannya dengan tombol panah. Pengaturan hanya berlaku untuk akun Anda.</p>
                <ul class="list-group" id="reminderSettingsList">
                    @foreach ($layout as $item)
                    <li class="list-group-item d-flex align-items-center gap-2" data-key="{{ $item['key'] }}">
                        <div class="form-check form-switch mb-0 flex-grow-1">
                            <input class="form-check-input" type="checkbox" role="switch" id="reminder-enabled-{{ $item['key'] }}" value="{{ $item['key'] }}" @checked($item['enabled'])>
                            <label class="form-check-label" for="reminder-enabled-{{ $item['key'] }}">{{ $item['title'] }}</label>
                        </div>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Urutan {{ $item['title'] }}">
                            <button type="button" class="btn btn-outline-secondary" data-move="-1" aria-label="Naikkan {{ $item['title'] }}"><i class="fa fa-arrow-up"></i></button>
                            <button type="button" class="btn btn-outline-secondary" data-move="1" aria-label="Turunkan {{ $item['title'] }}"><i class="fa fa-arrow-down"></i></button>
                        </div>
                    </li>
                    @endforeach
                </ul>
                <div id="reminderSettingsFields"></div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check me-1"></i>Simpan</button>
            </div>
        </form>
    </div></div>
</div>
@endsection
@push('css')
<link href="{{ asset('assets/css/dt.min.css') }}" rel="stylesheet">
@endpush
@push('js')
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
@once('incremental-data-table')
<script src="{{ asset('assets/js/incremental-data-table.js') }}?v={{ filemtime(public_path('assets/js/incremental-data-table.js')) }}"></script>
@endonce
<script src="{{ asset('assets/js/vendor-statistics-table.js') }}?v={{ filemtime(public_path('assets/js/vendor-statistics-table.js')) }}"></script>
<script>
$(function () {
    const text = $.fn.dataTable.render.text();
    const no = {data: null, className: 'text-center', render: (data, type, row, meta) => meta.row + 1};
    const elapsed = value => value.days + ' hari ' + value.hours + ' jam';
    const date = value => String(value).split('-').reverse().join('-');
    const rupiah = value => 'Rp ' + Math.round(Number(value)).toLocaleString('id-ID');
    // Angka negatif (lewat limit / lewat jatuh tempo) ditandai merah.
    const signed = (value, suffix) => '<span class="fw-bold ' + (value < 0 ? 'text-danger' : 'text-warning-emphasis') + '">' + Number(value).toLocaleString('id-ID') + suffix + '</span>';
    const columns = {
        standby: [no, {data: 'nomor_lambung', render: text}, {data: 'vendor', render: text},
            {data: 'elapsed', className: 'text-nowrap fw-semibold', render: elapsed}],
        unloading: [no, {data: 'nomor_lambung', render: text}, {data: 'vendor', render: text}, {data: 'rute', render: text},
            {data: 'elapsed', className: 'text-nowrap fw-semibold', render: elapsed}],
        filter_oil: [no, {data: 'nomor_lambung', render: text}, {data: 'vendor', render: text},
            ...@json($categories->pluck('id')->all()).map(id => ({data: 'sisa', className: 'text-center',
                render: sisa => sisa && sisa[id] !== undefined ? signed(sisa[id], ' rit') : '-'}))],
        invoice_due: [no, {data: 'customer', render: text}, {data: 'total_tagihan', className: 'text-end text-nowrap', render: rupiah},
            {data: 'jatuh_tempo', className: 'text-center text-nowrap', render: date},
            {data: 'sisa_hari', className: 'text-center text-nowrap', render: value => signed(value, ' hari')}],
        attendance: [no, {data: 'nama', render: text}, {data: 'jabatan', render: text},
            {data: 'berturut', className: 'text-center', render: value => Number(value) + ' hari'},
            {data: 'total', className: 'text-center', render: value => Number(value) + ' hari'}],
    };
    // Atur tampilan: naik/turun urutan, lalu kirim order[] (semua tabel) & enabled[] (yang aktif).
    $('#reminderSettingsList').on('click', '[data-move]', function () {
        const item = $(this).closest('li');
        Number($(this).data('move')) < 0 ? item.prev().before(item) : item.next().after(item);
        $(this).trigger('focus');
    });
    $('#reminderSettingsForm').on('submit', function () {
        const fields = $('#reminderSettingsFields').empty();
        $('#reminderSettingsList li').each(function () {
            const key = $(this).data('key');
            fields.append($('<input type="hidden" name="order[]">').val(key));
            if ($(this).find('input[type=checkbox]').is(':checked')) fields.append($('<input type="hidden" name="enabled[]">').val(key));
        });
    });
    const labels = {loading: 'Memuat pengingat...', done: 'Seluruh pengingat telah ditampilkan.', empty: 'Tidak ada pengingat.', failed: 'Pengingat gagal dimuat. '};
    @foreach ($tables as $table)
    vendorStatisticsTable('#reminder-table-{{ $table['key'] }}', {url: @json(route('reminders.data', $table['key'])), data: {},
        columns: columns[@json($table['key'])], labels: labels});
    @endforeach
});
</script>
@endpush
