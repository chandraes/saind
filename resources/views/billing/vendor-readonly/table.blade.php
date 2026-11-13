<div class="card shadow-sm">
    <div class="table-responsive">
        <table id="note-inbox-table" class="table table-bordered table-hover align-middle mb-0 vendor-readonly-table">
            <caption class="px-3">{{ $title }}. Data dimuat bertahap saat Anda menggulir. Berat dalam ton.</caption>
            <thead class="table-success"><tr>
                <th scope="col" style="width: 45px">No</th>
                @foreach ($columns as $column)<th scope="col">@include('billing.vendor-readonly.sort-link', ['label' => $column, 'key' => array_search($column, \App\Services\VendorOperationalBilling::SORTS, true) ?: null])</th>@endforeach
                @if ($showAction)<th scope="col">Aksi</th>@endif
            </tr></thead>
            <tbody>@include('billing.vendor-readonly.rows')</tbody>
        </table>
    </div>
    @if (!$rows)<p class="text-center text-muted my-4">Belum ada data sesuai filter.</p>@endif
    <div id="note-inbox-loading" class="card-footer bg-white text-center" role="status" data-next="{{ $next }}">{{ $next ? 'Memuat data berikutnya saat Anda menggulir…' : 'Seluruh data telah ditampilkan.' }}</div>
</div>
@once
@push('css')
{{-- Banyak kolom (hingga 19 untuk akun vendor): ringkas agar muat satu layar; angka tidak dipotong. --}}
<style>
.vendor-readonly-table{font-size:.78rem}.vendor-readonly-table th,.vendor-readonly-table td{padding:.45rem .35rem}
.vendor-readonly-table td{white-space:nowrap}.vendor-readonly-table td:nth-child(5),.vendor-readonly-table td:nth-child(6){white-space:normal;min-width:90px}
@media(max-width:1439.98px){.vendor-readonly-table{font-size:.7rem}.vendor-readonly-table th,.vendor-readonly-table td{padding:.35rem .2rem}.vendor-readonly-table td:nth-child(5),.vendor-readonly-table td:nth-child(6){min-width:76px}}
</style>
@endpush
@endonce
