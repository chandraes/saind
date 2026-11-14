@extends('layouts.app')
@section('content')
@php
    $periodQuery = ['bulan' => $period['month'], 'tahun' => $period['year']];
    $filtered = $search !== '' || $deductionsOnly;
    $zone = 'Asia/Jakarta';
    $stamp = fn (?string $value) => $value ? \Carbon\Carbon::parse($value)->timezone($zone)->format('d/m/Y H:i').' WIB' : null;
    $money = fn (int $value) => number_format($value, 0, ',', '.');
@endphp
<div class="container-fluid hrd-deductions">
    <div class="row justify-content-center"><div class="col-md-12 text-center"><h1><u>POTONGAN ABSENSI</u></h1><h2><u>{{ $period['label'] }}</u></h2></div></div>
    <div class="d-flex flex-wrap gap-4 align-items-center mt-3 mb-4">
        <a href="{{ route('home') }}" class="d-inline-flex align-items-center gap-2"><img src="{{ asset('images/dashboard.svg') }}" alt="" width="30">Dashboard</a>
    </div>

    @php
        // Periode dipisah Bulan & Tahun; bulan yang tersedia mengikuti tahun terpilih.
        $byYear = collect($periods)->groupBy('year')->map(fn ($items) => $items->map(fn ($p) => [
            'month' => $p['month'],
            'label' => \Carbon\Carbon::create($p['year'], $p['month'], 1)->locale('id')->translatedFormat('F').($p['final'] ? '' : ' (berjalan)'),
        ])->values());
    @endphp
    {{-- Bulan/Tahun dan "hanya berpotongan" langsung diterapkan; Cari lewat tombol/Enter. --}}
    <form method="GET" id="hrd-filter" class="card shadow-sm border-0 mb-3 hrd-filter" role="search" aria-label="Cari dan filter potongan absensi">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <fieldset class="col-md-6 col-xl-4">
                    <legend class="form-label small fw-semibold text-muted text-uppercase mb-1 float-none fs-6"><i class="fa fa-calendar me-1" aria-hidden="true"></i>Periode gaji</legend>
                    <div class="d-flex gap-2">
                        <select id="bulan" name="bulan" class="form-select" aria-label="Bulan">
                            @foreach ($byYear[$period['year']] as $option)
                                <option value="{{ $option['month'] }}" @selected($option['month'] === $period['month'])>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                        <select id="tahun" name="tahun" class="form-select hrd-year" aria-label="Tahun">
                            @foreach ($byYear->keys() as $year)
                                <option value="{{ $year }}" @selected($year === $period['year'])>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </fieldset>
                <div class="col-md-6 col-xl-5">
                    <label class="form-label small fw-semibold text-muted text-uppercase mb-1" for="search"><i class="fa fa-search me-1" aria-hidden="true"></i>Cari pegawai</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa fa-search text-muted" aria-hidden="true"></i></span>
                        <input id="search" name="search" type="search" class="form-control border-start-0" value="{{ $search }}" maxlength="100" placeholder="Nama atau jabatan">
                        <button type="submit" class="btn btn-success px-4">Cari</button>
                    </div>
                </div>
                <div class="col-sm col-xl-auto">
                    <div class="form-check form-switch hrd-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="potongan" name="potongan" value="1" @checked($deductionsOnly)>
                        <label class="form-check-label" for="potongan">Hanya yang ada potongan</label>
                    </div>
                </div>
                <div class="col-sm-auto ms-xl-auto">
                    <a class="btn btn-outline-secondary w-100 {{ $filtered ? '' : 'disabled' }}" @if (!$filtered) aria-disabled="true" tabindex="-1" @endif href="{{ route('hrd.attendance-deductions', $periodQuery) }}"><i class="fa fa-undo me-1" aria-hidden="true"></i>Reset</a>
                </div>
            </div>
        </div>
    </form>
    @if ($errors->any())<p class="text-danger">{{ $errors->first() }}</p>@endif

    <div class="alert {{ $status['final'] ? 'alert-success' : 'alert-warning' }} d-flex flex-wrap gap-2 align-items-center small" role="status">
        <span class="badge {{ $status['final'] ? 'bg-success' : 'bg-warning text-dark' }}">{{ $status['final'] ? 'Final' : 'Sementara' }}</span>
        @if ($status['final'])
            <span>Gaji periode ini sudah disahkan. Potongan sesuai data yang tersimpan saat pengesahan.</span>
        @elseif (!$status['enabled'])
            <span>Potongan absensi otomatis sedang nonaktif, sehingga belum ada potongan pada periode ini.</span>
        @else
            <span>Pantauan periode absensi {{ \Carbon\Carbon::parse($status['from'])->format('d/m/Y') }}–{{ \Carbon\Carbon::parse($status['to'])->format('d/m/Y') }}; nilai dapat berubah sampai gaji disahkan.
                {{ $stamp($status['fetched_at']) ? 'Data ditarik '.$stamp($status['fetched_at']).'.' : 'Data absensi belum ditarik.' }}
                @if ($stamp($status['next_sync_at'])) Pembaruan berikutnya {{ $stamp($status['next_sync_at']) }}. @endif</span>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle" id="hrd-deductions-table">
            <thead class="table-success text-center align-middle">
                <tr><th style="width:4rem">No</th><th>Nama</th><th>Jabatan</th><th style="width:14rem">Potongan Absensi</th></tr>
            </thead>
            <tbody>
                @include('hrd.attendance-deduction-rows')
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <th colspan="3" class="text-end">Total seluruh hasil filter · {{ $deductedCount }} pegawai berpotongan</th>
                    <th class="text-end text-danger">{{ $money($total) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
    {{-- Infinite scroll: baris berikutnya dimuat saat sentinel terlihat. --}}
    <div id="hrd-more" class="text-center text-muted small py-3" @unless ($rows->hasMorePages()) hidden @endunless data-next="{{ $rows->nextPageUrl() }}" role="status">
        <span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Memuat pegawai berikutnya…
    </div>
</div>
@endsection
@push('css')
<link href="{{ asset('assets/css/payroll-attendance-detail.css') }}" rel="stylesheet">
<style>
.hrd-filter .hrd-year{flex:0 0 7.5rem}
/* Switch setinggi kontrol lain agar sebaris dengan Bulan/Tahun dan Cari. */
.hrd-filter .hrd-switch{min-height:calc(1.5em + .75rem + 2px);display:flex;align-items:center;gap:.5rem;padding-left:2.5em}
.hrd-filter .input-group-text+.form-control:focus{border-left:1px solid #86b7fe}
</style>
@endpush
@push('js')
<script src="{{ asset('assets/js/payroll-attendance-detail.js') }}?v={{ filemtime(public_path('assets/js/payroll-attendance-detail.js')) }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('hrd-filter');
    const months = @json($byYear);
    const month = document.getElementById('bulan');
    const year = document.getElementById('tahun');
    // Ganti tahun: isi ulang bulan yang tersedia (terbaru lebih dulu) lalu terapkan.
    year.addEventListener('change', () => {
        month.replaceChildren(...months[year.value].map(item => new Option(item.label, item.month)));
        form.requestSubmit();
    });
    month.addEventListener('change', () => form.requestSubmit());
    document.getElementById('potongan').addEventListener('change', () => form.requestSubmit());
    const more = document.getElementById('hrd-more');
    const body = document.querySelector('#hrd-deductions-table tbody');
    let loading = false;
    const loadMore = async () => {
        if (loading || !more.dataset.next) return;
        loading = true;
        try {
            const response = await fetch(more.dataset.next, {headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}, credentials: 'same-origin'});
            if (!response.ok) throw new Error();
            const page = await response.json();
            body.insertAdjacentHTML('beforeend', page.html);
            more.dataset.next = page.next || '';
            more.hidden = !page.next;
        } catch {
            more.textContent = 'Gagal memuat data berikutnya. Gulir lagi atau muat ulang halaman.';
            setTimeout(() => { loading = false; }, 3000);
            return;
        }
        loading = false;
        if (more.dataset.next && more.getBoundingClientRect().top < window.innerHeight) loadMore();
    };
    new IntersectionObserver(entries => entries.some(entry => entry.isIntersecting) && loadMore(), {rootMargin: '200px'}).observe(more);
    document.getElementById('hrd-deductions-table').addEventListener('click', async event => {
        const button = event.target.closest('[data-deduction-detail]');
        if (!button || button.dataset.busy) return;
        button.dataset.busy = '1';
        try {
            const response = await fetch(button.dataset.deductionDetail, {headers: {'Accept': 'application/json'}, credentials: 'same-origin'});
            if (!response.ok) throw new Error(response.status === 404 ? 'Rincian potongan tidak tersedia.' : 'Rincian belum dapat dimuat. Muat ulang halaman lalu coba lagi.');
            const {data} = await response.json();
            window.showPayrollAttendanceDetail(data.name, data.period.label, data.detail, data.pdf_url);
        } catch (error) {
            Swal.fire({icon: 'error', title: 'Tidak berhasil', text: error.message});
        } finally { delete button.dataset.busy; }
    });
});
</script>
@endpush
