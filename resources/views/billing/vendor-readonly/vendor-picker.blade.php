@extends('layouts.app')
@section('content')
{{-- Operasional: seperti admin, pilih vendor dulu sebelum melihat Nota Bayar. --}}
<div class="container py-5">
    <div class="row justify-content-center"><div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow rounded-4 overflow-hidden">
            <div class="card-header bg-primary text-white py-3"><h1 class="h5 mb-0"><i class="fa fa-truck me-2" aria-hidden="true"></i>Pilih Vendor</h1></div>
            <form action="{{ route('vendor-operational.nota-bayar') }}" method="get">
                <div class="card-body">
                    <p class="text-muted small">Nota Bayar ditampilkan per vendor. Pilih vendor yang ingin dilihat.</p>
                    <label for="vendor-picker" class="form-label fw-semibold">Nama Vendor <span class="text-danger">*</span></label>
                    <select id="vendor-picker" name="vendor_id" class="form-select readonly-select" required>
                        <option value="" selected disabled>-- Pilih Vendor --</option>
                        @foreach ($vendors as $option)
                        <option value="{{ $option->id }}">{{ $option->nama }}@if ($option->nickname) ({{ $option->nickname }})@endif</option>
                        @endforeach
                    </select>
                    @if ($errors->any())<p class="text-danger small mt-2 mb-0">{{ $errors->first() }}</p>@endif
                </div>
                <div class="card-footer bg-light d-flex justify-content-between">
                    <a href="{{ route('home') }}" class="btn btn-secondary">Dashboard</a>
                    <button type="submit" class="btn btn-primary px-4">Lanjutkan <i class="fa fa-arrow-right ms-1" aria-hidden="true"></i></button>
                </div>
            </form>
        </div>
    </div></div>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.min.css') }}">
@endpush
@push('js')
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script>$(function () { $('#vendor-picker').select2({width: '100%', placeholder: '-- Pilih Vendor --'}); });</script>
@endpush
