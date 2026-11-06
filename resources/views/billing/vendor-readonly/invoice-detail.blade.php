@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h1 class="h3 mb-1">{{ $title }}</h1><p class="text-muted mb-0">Rincian nota vendor Anda · hanya baca</p></div>
        <button type="button" class="btn btn-outline-secondary" onclick="history.back()"><i class="fa fa-arrow-left me-1" aria-hidden="true"></i>Kembali</button>
    </div>
    <div class="card shadow-sm mb-4"><div class="card-body">@include('billing.vendor-readonly.detail', ['fields' => $invoiceFields])</div></div>
    @include('billing.vendor-readonly.table', ['showAction' => false])
</div>
@endsection
@push('js')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
@once('incremental-data-table')
<script src="{{ asset('assets/js/incremental-data-table.js') }}?v={{ filemtime(public_path('assets/js/incremental-data-table.js')) }}"></script>
@endonce
<script src="{{ asset('assets/js/load-note-inbox.js') }}?v={{ filemtime(public_path('assets/js/load-note-inbox.js')) }}"></script>
@endpush

@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/dt.min.css') }}">
@endpush
