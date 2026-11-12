@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center"><div class="col-md-12 text-center"><h1><u>Nota Bongkar</u></h1></div></div>
    <div class="d-flex mt-3 mb-4"><a href="{{ route('home') }}" class="d-inline-flex align-items-center gap-2"><img src="{{ asset('images/dashboard.svg') }}" alt="" width="30"> Dashboard</a></div>
    <form method="GET" class="row g-3 align-items-end">
        @include('billing.vendor-readonly.vendor-filter')
        <div class="col-md-4"><label class="form-label" for="search">Cari nota, unit, atau rute</label><input id="search" name="search" class="form-control" value="{{ $filters['search'] }}" maxlength="100"></div>
        <div class="col-md-3 d-flex gap-2"><button type="submit" class="btn btn-primary">Terapkan</button><a href="{{ route('vendor-operational.nota-bongkar') }}" class="btn btn-outline-secondary">Reset</a></div>
    </form>
    @if ($errors->any())<p class="text-danger mt-2 mb-0">{{ $errors->first() }}</p>@endif
</div>
<div class="container-fluid mt-5">
    <div class="table-responsive">
        <table class="table table-bordered table-hover" id="note-inbox-table">
            <thead class="table-success"><tr>@include('billing.transaksi.nota-bongkar.table-head', ['showActions' => false])</tr></thead>
            <tbody>@include('billing.vendor-readonly.unloading-rows')</tbody>
        </table>
    </div>
    @if (!$rows)<p class="text-center text-muted my-4">Belum ada nota bongkar sesuai filter.</p>@endif
    <div id="note-inbox-loading" class="text-center py-3" role="status" data-next="{{ $next }}">{{ $next ? 'Memuat nota berikutnya saat Anda menggulir…' : 'Seluruh nota telah ditampilkan.' }}</div>
</div>
@include('billing.vendor-readonly.modal')
@endsection
@include('billing.vendor-readonly.scripts')
