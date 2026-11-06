@extends('layouts.app')
@section('content')
@php($statusOptions = ['unpaid' => 'Belum lunas', 'paid' => 'Lunas', 'all' => 'Semua'])
<div class="container-fluid invoice-vendor-page">
    <header class="invoice-vendor-header">
        <div class="invoice-vendor-identity">
            <span class="invoice-vendor-mark"><img src="{{ asset('images/invoice-hauling.svg') }}" alt="" width="52" height="52"></span>
            <div>
                <p class="invoice-vendor-context">Operasional vendor</p>
                <h1>Invoice Vendor</h1>
                <p class="invoice-vendor-name"><i class="fa fa-building-o" aria-hidden="true"></i>{{ $vendorName }}</p>
            </div>
        </div>
        <div class="invoice-vendor-header-actions">
            <span class="invoice-vendor-readonly"><i class="fa fa-lock" aria-hidden="true"></i>Baca saja</span>
            <a href="{{ route('home') }}" class="btn invoice-vendor-dashboard"><i class="fa fa-arrow-left me-2" aria-hidden="true"></i>Dashboard</a>
        </div>
    </header>

    <section class="invoice-vendor-filters" aria-labelledby="invoice-filter-title">
        <div class="invoice-vendor-filter-heading">
            <h2 id="invoice-filter-title"><i class="fa fa-filter" aria-hidden="true"></i>Filter invoice</h2>
        </div>
        <form action="{{ route('vendor-operational.invoice') }}" method="get" class="invoice-vendor-filter-grid">
            <div class="invoice-filter-search">
                <label for="search" class="form-label">Cari invoice</label>
                <div class="invoice-vendor-search"><i class="fa fa-search" aria-hidden="true"></i><input id="search" name="search" class="form-control" maxlength="100" placeholder="Masukkan periode invoice" value="{{ $filters['search'] ?? '' }}"></div>
            </div>
            <div class="invoice-filter-status">
                <label for="status" class="form-label">Status pembayaran</label>
                <select id="status" name="status" class="form-select">
                    @foreach($statusOptions as $value => $label)<option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="start_date" class="form-label">Tempo Dari</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}">
            </div>
            <div>
                <label for="end_date" class="form-label">Tempo Sampai</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}">
            </div>
            <div class="invoice-vendor-filter-actions">
                <button type="submit" class="btn invoice-vendor-apply">Terapkan</button>
                <a href="{{ route('vendor-operational.invoice') }}" class="btn invoice-vendor-reset"><i class="fa fa-refresh me-1" aria-hidden="true"></i>Reset</a>
            </div>
            @if($errors->any())<p class="invoice-vendor-filter-error text-danger mb-0" role="alert">{{ $errors->first() }}</p>@endif
        </form>
    </section>

    <section class="invoice-vendor-list" aria-labelledby="invoice-list-title">
        <div class="invoice-vendor-list-heading">
            <div><h2 id="invoice-list-title">Daftar invoice</h2><p>Klik invoice untuk melihat rincian.</p></div>
            <span class="invoice-vendor-active-filter"><i class="fa fa-filter" aria-hidden="true"></i>{{ $statusOptions[$filters['status']] }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle table-hover mb-0 invoice-vendor-table" id="note-inbox-table">
                @include('billing.transaksi.invoice.vendor-list-head', ['vendorReadonly' => true])
                <tbody>@include('billing.vendor-readonly.invoice-rows')</tbody>
            </table>
        </div>
        <div id="note-inbox-loading" class="invoice-vendor-loading" role="status" data-next="{{ $next }}">{{ $next ? 'Memuat data berikutnya saat Anda menggulir…' : 'Seluruh data telah ditampilkan.' }}</div>
    </section>
</div>
@endsection
@push('css')
<link href="{{ asset('assets/css/dt.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/vendor-invoices.css') }}?v={{ filemtime(public_path('assets/css/vendor-invoices.css')) }}" rel="stylesheet">
@endpush
@push('js')
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
@once('incremental-data-table')
<script src="{{ asset('assets/js/incremental-data-table.js') }}?v={{ filemtime(public_path('assets/js/incremental-data-table.js')) }}"></script>
@endonce
<script src="{{ asset('assets/js/load-note-inbox.js') }}?v={{ filemtime(public_path('assets/js/load-note-inbox.js')) }}"></script>
<script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
<script>$(function () { $('#status').select2({width: '100%', minimumResultsForSearch: Infinity}); });</script>
@endpush
