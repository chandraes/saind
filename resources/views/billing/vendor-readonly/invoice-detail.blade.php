@extends('layouts.app')
@section('content')
@php($statusIcon = ['paid' => 'fa-check-circle', 'unpaid' => 'fa-clock-o', 'cart' => 'fa-shopping-basket'][$invoice['status']])
<div class="container-fluid invoice-vendor-page invoice-detail-page">
    <header class="invoice-vendor-header">
        <div class="invoice-vendor-identity">
            <span class="invoice-vendor-mark"><img src="{{ asset('images/invoice-hauling.svg') }}" alt="" width="52" height="52"></span>
            <div>
                <p class="invoice-vendor-context">Invoice {{ $invoice['category'] }}</p>
                <h1>{{ $invoice['number'] }}</h1>
                <p class="invoice-vendor-name"><i class="fa fa-building-o" aria-hidden="true"></i>{{ $invoice['vendor'] ?? '—' }}</p>
            </div>
        </div>
        <div class="invoice-vendor-header-actions">
            <span class="invoice-detail-status invoice-detail-status-{{ $invoice['status'] }}" data-invoice-status="{{ $invoice['status'] }}">
                <i class="fa {{ $statusIcon }}" aria-hidden="true"></i>{{ $invoice['status_label'] }}
            </span>
            <a href="{{ route('vendor-operational.invoice') }}" class="btn invoice-vendor-dashboard"><i class="fa fa-arrow-left me-2" aria-hidden="true"></i>Daftar invoice</a>
        </div>
    </header>

    <section class="invoice-detail-summary {{ $finance ? 'invoice-detail-summary-finance' : '' }}" aria-label="Ringkasan invoice">
        <div class="invoice-detail-meta">
            <article class="invoice-detail-tile">
                <span class="invoice-detail-tile-icon"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                <div><p>Tanggal invoice</p><strong>{{ $invoice['issued_on'] ?? '—' }}</strong></div>
            </article>
            <article class="invoice-detail-tile {{ $invoice['overdue'] ? 'invoice-detail-tile-alert' : '' }}">
                <span class="invoice-detail-tile-icon"><i class="fa fa-hourglass-half" aria-hidden="true"></i></span>
                <div><p>Jatuh tempo</p><strong>{{ $invoice['due_on'] ?? '—' }}</strong>
                    @if ($invoice['due_note'])<small>{{ $invoice['due_note'] }}</small>@endif</div>
            </article>
            <article class="invoice-detail-tile">
                <span class="invoice-detail-tile-icon"><i class="fa fa-tag" aria-hidden="true"></i></span>
                <div><p>Jenis invoice</p><strong>{{ $invoice['category'] }}</strong></div>
            </article>
        </div>

        @if ($finance)
        <article class="invoice-detail-finance" aria-labelledby="invoice-finance-title">
            <div class="invoice-detail-headline">
                <p id="invoice-finance-title">{{ $finance['headline'][0] }}</p>
                <strong>{{ $finance['headline'][1] }}</strong>
            </div>
            <dl class="invoice-detail-amounts">
                @foreach ($finance['details'] as $label => $value)
                <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                @endforeach
            </dl>
            @if ($finance['progress'])
            <div class="invoice-detail-progress">
                <div class="invoice-detail-progress-track" role="progressbar" aria-label="Pembayaran invoice" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $finance['progress']['percent'] }}">
                    <span style="width: {{ $finance['progress']['percent'] }}%"></span>
                </div>
                <dl class="invoice-detail-amounts invoice-detail-amounts-split">
                    <div><dt>Sudah Dibayar</dt><dd class="invoice-detail-paid">{{ $finance['progress']['paid'] }}</dd></div>
                    <div><dt>Sisa Bayar</dt><dd class="invoice-detail-remaining">{{ $finance['progress']['remaining'] }}</dd></div>
                </dl>
            </div>
            @endif
        </article>
        @endif
    </section>

    @empty($adminTable)
    <section class="invoice-vendor-list" aria-labelledby="invoice-journeys-title">
        <div class="invoice-vendor-list-heading">
            <div><h2 id="invoice-journeys-title">Perjalanan dalam invoice</h2><p>Berat dalam ton. Data dimuat bertahap saat Anda menggulir.</p></div>
            <span class="invoice-vendor-active-filter"><i class="fa fa-lock" aria-hidden="true"></i>Baca saja</span>
        </div>
        <div class="table-responsive">
            <table id="note-inbox-table" class="table table-bordered table-hover align-middle mb-0 invoice-vendor-table">
                <caption class="visually-hidden">{{ $title }}</caption>
                <thead class="table-success"><tr>
                    <th scope="col" style="width: 45px">No</th>
                    @foreach ($columns as $column)<th scope="col">{{ $column }}</th>@endforeach
                </tr></thead>
                <tbody>@include('billing.vendor-readonly.rows')</tbody>
            </table>
        </div>
        @if (!$rows)<p class="text-center text-muted my-4">Belum ada perjalanan pada invoice ini.</p>@endif
        <div id="note-inbox-loading" class="invoice-vendor-loading" role="status" data-next="{{ $next }}">{{ $next ? 'Memuat data berikutnya saat Anda menggulir…' : 'Seluruh data telah ditampilkan.' }}</div>
    </section>
    @endempty
</div>
@isset($adminTable)
{{-- Di luar batas lebar halaman: tabel banyak kolom memakai container-fluid penuh seperti halaman admin. --}}
<section class="invoice-detail-admin-table" aria-label="Perjalanan dalam invoice">
    @include($adminTable['view'], $adminTable['data'])
</section>
@endisset
@endsection
@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/dt.min.css') }}">
<link href="{{ asset('assets/css/vendor-invoices.css') }}?v={{ filemtime(public_path('assets/css/vendor-invoices.css')) }}" rel="stylesheet">
@endpush
@push('js')
@isset($adminTable)
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
@if ($adminTable['view'] === 'billing.transaksi.invoice.bayar.table')
<script>
    // Sama seperti halaman admin Invoice Bayar.
    $(function () {
        $('#notaTable').DataTable({paging: false, ordering: false, searching: false, info: false, scrollCollapse: true, scrollX: true, scrollY: '550px',
            fixedColumns: {leftColumns: 3, rightColumns: 1}});
    });
</script>
@endif
@else
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
@once('incremental-data-table')
<script src="{{ asset('assets/js/incremental-data-table.js') }}?v={{ filemtime(public_path('assets/js/incremental-data-table.js')) }}"></script>
@endonce
<script src="{{ asset('assets/js/load-note-inbox.js') }}?v={{ filemtime(public_path('assets/js/load-note-inbox.js')) }}"></script>
@endisset
@endpush
