@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center"><div class="col-md-12 text-center"><h1><u>{{ $title }}</u></h1><h2><u>{{ $vendorName }}</u></h2></div></div>
    <div class="d-flex flex-wrap gap-4 align-items-center mt-3 mb-4">
        <a href="{{ route('home') }}" class="d-inline-flex align-items-center gap-2"><img src="{{ asset('images/dashboard.svg') }}" alt="" width="30">Dashboard</a>
        <a href="{{ route('vendor-operational.nota-bayar', $vendorQuery) }}" class="d-inline-flex align-items-center gap-2"><img src="{{ asset('images/back.svg') }}" alt="" width="30">Kembali</a>
        @if ($vendorQuery)<a href="{{ route('vendor-operational.nota-bayar') }}" class="d-inline-flex align-items-center gap-2"><i class="fa fa-exchange" aria-hidden="true"></i>Ganti vendor</a>@endif
    </div>
    <form method="GET" class="row g-3 align-items-end mb-3">
        <input type="hidden" name="kind" value="{{ $filters['kind'] }}">
        @foreach ($vendorQuery as $name => $value)<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endforeach
        <div class="col-md-4"><label class="form-label" for="search">Cari nota, unit, atau rute</label><input id="search" name="search" class="form-control" value="{{ $filters['search'] }}" maxlength="100"></div>
        <div class="col-md-3 d-flex gap-2"><button type="submit" class="btn btn-primary">Cari</button><a class="btn btn-outline-secondary" href="{{ route('vendor-operational.nota-bayar', ['kind' => $filters['kind']] + $vendorQuery) }}">Reset</a></div>
    </form>
    @if ($errors->any())<p class="text-danger">{{ $errors->first() }}</p>@endif
</div>
<div class="container-fluid mt-3">
    @include('billing.vendor-readonly.table', ['showAction' => false])
</div>
@include('billing.vendor-readonly.modal')
@endsection
@include('billing.vendor-readonly.scripts')
