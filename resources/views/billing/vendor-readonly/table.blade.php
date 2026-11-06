<div class="card shadow-sm">
    <div class="table-responsive">
        <table id="note-inbox-table" class="table table-bordered table-hover align-middle mb-0">
            <caption class="px-3">{{ $title }}. Data dimuat bertahap saat Anda menggulir. Berat dalam ton.</caption>
            <thead class="table-success"><tr>
                <th scope="col" style="width: 45px">No</th>
                @foreach ($columns as $column)<th scope="col">{{ $column }}</th>@endforeach
                @if ($showAction)<th scope="col">Aksi</th>@endif
            </tr></thead>
            <tbody>@include('billing.vendor-readonly.rows')</tbody>
        </table>
    </div>
    @if (!$rows)<p class="text-center text-muted my-4">Belum ada data sesuai filter.</p>@endif
    <div id="note-inbox-loading" class="card-footer bg-white text-center" role="status" data-next="{{ $next }}">{{ $next ? 'Memuat data berikutnya saat Anda menggulir…' : 'Seluruh data telah ditampilkan.' }}</div>
</div>
