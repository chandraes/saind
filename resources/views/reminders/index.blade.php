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
    $total = array_sum(array_column($tables, 'count'));
@endphp
<div class="container py-4 reminder-page">
    <header class="reminder-hero">
        <div class="reminder-hero-main">
            <span class="reminder-hero-icon"><img src="{{ asset('images/pengingat.svg') }}" width="40" height="40" alt=""></span>
            <div>
                <p class="reminder-eyebrow">DASHBOARD PENGINGAT</p>
                <h1>Pengingat</h1>
                <p>Daftar yang perlu ditindaklanjuti. Waktu berjalan dihitung saat halaman dibuka; isi daftar mengikuti rekap terakhir.</p>
            </div>
        </div>
        <div class="reminder-hero-actions">
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#reminderSettingsModal"><i class="fa fa-sliders me-2" aria-hidden="true"></i>Atur tampilan</button>
            <a class="btn btn-outline-light" href="{{ route('home') }}"><i class="fa fa-arrow-left me-2" aria-hidden="true"></i>Dashboard</a>
        </div>
    </header>
    @include('swal')

    @if ($tables === [])
    <div class="reminder-empty">
        <img src="{{ asset('images/pengingat.svg') }}" width="64" height="64" alt="" class="mb-3">
        <p class="mb-0">Semua tabel pengingat sedang dinonaktifkan. Aktifkan kembali melalui <strong>Atur tampilan</strong>.</p>
    </div>
    @else
    <nav class="reminder-summary" aria-label="Ringkasan pengingat">
        <div class="reminder-stat reminder-stat-total">
            <span class="reminder-stat-icon"><i class="fa fa-bell" aria-hidden="true"></i></span>
            <span><span class="reminder-stat-value d-block">{{ $total }}</span><span class="reminder-stat-label">Total pengingat</span></span>
        </div>
        @foreach ($tables as $table)
        <a class="reminder-stat" href="#reminder-{{ $table['key'] }}" data-tone="{{ $table['key'] }}">
            <span class="reminder-stat-icon"><i class="fa {{ $table['icon'] }}" aria-hidden="true"></i></span>
            <span><span class="reminder-stat-value d-block">{{ $table['count'] }}</span><span class="reminder-stat-label">{{ $table['label'] }}</span></span>
        </a>
        @endforeach
    </nav>
    @endif

    @foreach ($tables as $table)
    <section class="reminder-card" id="reminder-{{ $table['key'] }}" data-tone="{{ $table['key'] }}" aria-labelledby="reminder-title-{{ $table['key'] }}">
        <div class="reminder-card-head">
            <div class="reminder-card-title">
                <span class="reminder-card-icon"><i class="fa {{ $table['icon'] }}" aria-hidden="true"></i></span>
                <div>
                    <span class="reminder-label">{{ $table['label'] }}</span>
                    <h2 id="reminder-title-{{ $table['key'] }}">{{ $table['title'] }}
                        <span class="reminder-count {{ $table['count'] > 0 ? '' : 'is-zero' }}" data-reminder-count>{{ $table['count'] }}</span>
                    </h2>
                    <p class="reminder-card-desc">{{ $table['description'] }}</p>
                </div>
            </div>
            <span class="reminder-updated"><i class="fa fa-clock-o" aria-hidden="true"></i>{{ $table['refreshed_at'] ? 'Diperbarui '.$table['refreshed_at']->format('d-m-Y H:i') : 'Belum pernah direkap' }}</span>
        </div>
        <div class="reminder-card-body">
            @if ($table['key'] === 'attendance' && ! $attendanceEnabled)
            <div class="reminder-note"><i class="fa fa-info-circle mt-1" aria-hidden="true"></i><span>Sinkron absensi belum aktif, sehingga tabel ini belum terisi.</span></div>
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

<div class="modal fade reminder-settings reminder-page" id="reminderSettingsModal" tabindex="-1" aria-labelledby="reminderSettingsTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content border-0 shadow">
        <div class="modal-header">
            <h5 class="modal-title" id="reminderSettingsTitle"><i class="fa fa-sliders me-2" aria-hidden="true"></i>Atur tampilan pengingat</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <form method="post" action="{{ route('reminders.settings') }}" id="reminderSettingsForm">
            @csrf
            <div class="modal-body">
                <p class="reminder-settings-hint"><i class="fa fa-arrows-v" aria-hidden="true"></i><span>Seret ikon <i class="fa fa-bars" aria-hidden="true"></i> untuk mengubah urutan dan gunakan sakelar untuk menampilkan atau menyembunyikan tabel. Pengaturan hanya berlaku untuk akun Anda.</span></p>
                <ul class="reminder-sort" id="reminderSettingsList">
                    @foreach ($layout as $item)
                    <li class="reminder-sort-item {{ $item['enabled'] ? '' : 'is-off' }}" data-key="{{ $item['key'] }}" data-tone="{{ $item['key'] }}">
                        <span class="reminder-sort-handle" title="Seret untuk mengubah urutan" aria-hidden="true"><i class="fa fa-bars"></i></span>
                        <span class="reminder-sort-order" data-order>{{ $loop->iteration }}</span>
                        <span class="reminder-sort-icon"><i class="fa {{ $item['icon'] }}" aria-hidden="true"></i></span>
                        <label class="reminder-sort-text mb-0" for="reminder-enabled-{{ $item['key'] }}">
                            <strong>{{ $item['label'] }}</strong>
                            <small>{{ $item['title'] }}</small>
                        </label>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="reminder-enabled-{{ $item['key'] }}" value="{{ $item['key'] }}" @checked($item['enabled'])>
                        </div>
                        <div class="reminder-sort-move" role="group" aria-label="Urutan {{ $item['label'] }}">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-move="-1" aria-label="Naikkan {{ $item['label'] }}"><i class="fa fa-chevron-up"></i></button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-move="1" aria-label="Turunkan {{ $item['label'] }}"><i class="fa fa-chevron-down"></i></button>
                        </div>
                    </li>
                    @endforeach
                </ul>
                <div id="reminderSettingsFields"></div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fa fa-check me-1" aria-hidden="true"></i>Simpan</button>
            </div>
        </form>
    </div></div>
</div>
@endsection
@push('css')
<link href="{{ asset('assets/css/dt.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/reminders.css') }}?v={{ filemtime(public_path('assets/css/reminders.css')) }}" rel="stylesheet">
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
    const no = {data: null, className: 'text-center text-muted', render: (data, type, row, meta) => meta.row + 1};
    const pill = (label, tone) => '<span class="rm-pill rm-pill-' + tone + '">' + label + '</span>';
    const number = value => Number(value).toLocaleString('id-ID');
    // Durasi: kuning bila baru melewati ambang, merah bila sudah lama ($dangerDays).
    const elapsed = dangerDays => value => pill(value.days + ' hari ' + value.hours + ' jam', value.days >= dangerDays ? 'danger' : 'warn');
    const date = value => String(value).split('-').reverse().join('-');
    const rupiah = value => 'Rp ' + Math.round(Number(value)).toLocaleString('id-ID');
    const columns = {
        standby: [no, {data: 'nomor_lambung', className: 'fw-semibold', render: text}, {data: 'vendor', render: text},
            {data: 'elapsed', className: 'text-center', render: elapsed(7)}],
        unloading: [no, {data: 'nomor_lambung', className: 'fw-semibold', render: text}, {data: 'vendor', render: text}, {data: 'rute', render: text},
            {data: 'elapsed', className: 'text-center', render: elapsed(3)}],
        filter_oil: [no, {data: 'nomor_lambung', className: 'fw-semibold', render: text}, {data: 'vendor', render: text},
            ...@json($categories->pluck('id')->all()).map(id => ({data: 'sisa', className: 'text-center',
                render: sisa => sisa && sisa[id] !== undefined ? pill(number(sisa[id]) + ' rit', sisa[id] < 0 ? 'danger' : 'warn') : '<span class="rm-muted">-</span>'}))],
        invoice_due: [no, {data: 'customer', className: 'fw-semibold', render: text}, {data: 'total_tagihan', className: 'text-end text-nowrap', render: rupiah},
            {data: 'jatuh_tempo', className: 'text-center text-nowrap', render: date},
            {data: 'sisa_hari', className: 'text-center', render: value => pill(number(value) + ' hari', value < 0 ? 'danger' : 'warn')}],
        attendance: [no, {data: 'nama', className: 'fw-semibold', render: text}, {data: 'jabatan', render: text},
            {data: 'berturut', className: 'text-center', render: value => pill(number(value) + ' hari', value >= 3 ? 'danger' : 'warn')},
            {data: 'total', className: 'text-center', render: value => pill(number(value) + ' hari', 'info')}],
    };

    // Atur tampilan: seret (mouse & sentuh lewat Pointer Events) atau tombol panah (keyboard).
    const list = document.getElementById('reminderSettingsList');
    const renumber = () => list.querySelectorAll('[data-order]').forEach((badge, index) => { badge.textContent = index + 1; });
    const gap = () => parseFloat(getComputedStyle(list).rowGap) || 0;
    // Baris dipindah di DOM saat diseret (pointer capture ikut lepas), jadi gerak & lepas didengar di window.
    list.addEventListener('pointerdown', function (event) {
        const handle = event.target.closest('.reminder-sort-handle');
        if (!handle || (event.pointerType === 'mouse' && event.button !== 0)) return;
        const item = handle.closest('li');
        event.preventDefault();
        item.classList.add('is-dragging');
        list.classList.add('is-active');
        let origin = event.clientY;
        const middle = element => element.getBoundingClientRect().top + element.offsetHeight / 2;
        const move = function (moveEvent) {
            if (moveEvent.pointerId !== event.pointerId) return;
            moveEvent.preventDefault();
            item.style.transform = 'translateY(' + (moveEvent.clientY - origin) + 'px)';
            for (let next = item.nextElementSibling, prev = item.previousElementSibling; ; next = item.nextElementSibling, prev = item.previousElementSibling) {
                if (next && middle(item) > middle(next)) {
                    origin += next.offsetHeight + gap();
                    next.after(item);
                } else if (prev && middle(item) < middle(prev)) {
                    origin -= prev.offsetHeight + gap();
                    prev.before(item);
                } else {
                    break;
                }
                item.style.transform = 'translateY(' + (moveEvent.clientY - origin) + 'px)';
            }
            renumber();
        };
        const end = function (endEvent) {
            if (endEvent.pointerId !== event.pointerId) return;
            item.style.transform = '';
            item.classList.remove('is-dragging');
            list.classList.remove('is-active');
            window.removeEventListener('pointermove', move);
            window.removeEventListener('pointerup', end);
            window.removeEventListener('pointercancel', end);
            renumber();
        };
        window.addEventListener('pointermove', move, {passive: false});
        window.addEventListener('pointerup', end);
        window.addEventListener('pointercancel', end);
    });
    $(list).on('click', '[data-move]', function () {
        const item = $(this).closest('li');
        Number($(this).data('move')) < 0 ? item.prev().before(item) : item.next().after(item);
        renumber();
        $(this).trigger('focus');
    });
    $(list).on('change', 'input[type=checkbox]', function () {
        $(this).closest('li').toggleClass('is-off', !this.checked);
    });
    $('#reminderSettingsForm').on('submit', function () {
        const fields = $('#reminderSettingsFields').empty();
        $('#reminderSettingsList li').each(function () {
            const key = $(this).data('key');
            fields.append($('<input type="hidden" name="order[]">').val(key));
            if ($(this).find('input[type=checkbox]').is(':checked')) fields.append($('<input type="hidden" name="enabled[]">').val(key));
        });
    });

    const labels = {loading: 'Memuat pengingat...', done: 'Seluruh pengingat telah ditampilkan.', empty: 'Tidak ada pengingat. Semua aman.', failed: 'Pengingat gagal dimuat. '};
    @foreach ($tables as $table)
    vendorStatisticsTable('#reminder-table-{{ $table['key'] }}', {url: @json(route('reminders.data', $table['key'])), data: {},
        columns: columns[@json($table['key'])], labels: labels});
    @endforeach
});
</script>
@endpush
