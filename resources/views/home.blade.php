@extends('layouts.app')
@section('content')
<div class="container">
    @include('swal')
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>DASHBOARD</u></h1>
        </div>
    </div>
    <div class="row justify-content-left mt-5">
        @if (auth()->user()->role === 'admin' || auth()->user()->role === 'su')
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('database')}}" class="text-decoration-none">
                <img src="{{asset('images/database.svg')}}" alt="" width="80">
                <h4 class="mt-3">Database</h4>
            </a>
        </div>
        @endif
        @if (auth()->user()->role === 'su' || auth()->user()->role === 'admin' || auth()->user()->role === 'user')
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('billing.index')}}" class="text-decoration-none">
                <img src="{{asset('images/billing.svg')}}" alt="" width="80">
                <h4 class="mt-3">Billing</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('rekap.index')}}" class="text-decoration-none">
                <img src="{{asset('images/rekap.svg')}}" alt="" width="80">
                <h4 class="mt-3">Rekap</h4>
            </a>
        </div>
        @endif
        @if (auth()->user()->role === 'admin' || auth()->user()->role === 'su')
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('dokumen')}}" class="text-decoration-none">
                <img src="{{asset('images/document.svg')}}" alt="" width="80">
                <h4 class="mt-3">Dokumen</h4>
            </a>
        </div>
        @if (auth()->user()->role === 'su')
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('bypass.index')}}" class="text-decoration-none">
                <img src="{{asset('images/admin.svg')}}" alt="" width="80">
                <h4 class="mt-3">Bypass</h4>
            </a>
        </div>
        @endif
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('pengaturan')}}" class="text-decoration-none">
                <img src="{{asset('images/pengaturan.svg')}}" alt="" width="80">
                <h4 class="mt-3">Pengaturan</h4>
            </a>
        </div>
        @endif

    </div>
    @if (auth()->user()->role === 'vendor')
    <div class="row justify-content-left mt-5">
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('kas-per-vendor.index', auth()->user()->vendor_id)}}" class="text-decoration-none">
                <img src="{{asset('images/kas-vendor.svg')}}" alt="" width="80">
                <h4 class="mt-3">Kas Vendor</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('perform-unit-pervendor.index')}}" class="text-decoration-none">
                <img src="{{asset('images/perform-unit.svg')}}" alt="" width="80">
                <h4 class="mt-3">Perform Unit</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('statistik-pervendor.index')}}" class="text-decoration-none">
                <img src="{{asset('images/statistik-vendor.svg')}}" alt="" width="80">
                <h4 class="mt-3">Statistik Vendor</h4>
            </a>
        </div>
    </div>
    <div class="row justify-content-left mt-5">
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('database.upah-gendong')}}" class="text-decoration-none">
                <img src="{{asset('images/upah-gendong.svg')}}" alt="" width="80">
                <h4 class="mt-3">DATABASE<br>UPAH GENDONG</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#upahGendongId">
                <img src="{{asset('images/statistik-ug.svg')}}" alt="" width="80">
                <h4 class="mt-3">STATISTIK<br>UPAH GENDONG</h4>
            </a>
            <div class="modal fade" id="upahGendongId" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="ugTitleId" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="ugTitleId">
                                Pilih NOLAM
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{route('per-vendor.upah-gendong')}}" method="get">
                        <div class="modal-body">
                            <div class="col-md-12 mb-3">
                                <select
                                    class="form-select"
                                    name="vehicle_id"
                                    id="vehicle_id"
                                >
                                @foreach ($ug as $d)
                                    <option value="{{$d->vehicle_id}}">{{$d->vehicle->nomor_lambung}}</option>
                                @endforeach
                                </select>
                            </div>


                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Tutup
                            </button>
                            <button type="submit" class="btn btn-primary">Lanjutkan</button>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-left mt-5">
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('database.upah-gendong')}}" class="text-decoration-none">
                <img src="{{asset('images/upah-gendong.svg')}}" alt="" width="80">
                <h4 class="mt-3">DATABASE<br>BAN LUAR</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#upahGendongId">
                <img src="{{asset('images/statistik-ug.svg')}}" alt="" width="80">
                <h4 class="mt-3">STATISTIK<br>BAN LUAR</h4>
            </a>
            <div class="modal fade" id="upahGendongId" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="ugTitleId" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="ugTitleId">
                                Pilih NOLAM
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{route('per-vendor.upah-gendong')}}" method="get">
                        <div class="modal-body">
                            <div class="col-md-12 mb-3">
                                <select
                                    class="form-select"
                                    name="vehicle_id"
                                    id="vehicle_id"
                                >
                                @foreach ($ug as $d)
                                    <option value="{{$d->vehicle_id}}">{{$d->vehicle->nomor_lambung}}</option>
                                @endforeach
                                </select>
                            </div>


                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Tutup
                            </button>
                            <button type="submit" class="btn btn-primary">Lanjutkan</button>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @if (auth()->user()->role === 'customer')
    @include('per-customer.index')
    @endif
    @if (auth()->user()->role === 'operasional')
    @include('operasional.index')
    @endif
</div>
@endsection
@if (auth()->user()->role === 'operasional')
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script>
    $(document).ready(function() {
            $('#vendor_id').select2({
                placeholder: 'Pilih Vendor',
                width: '100%',
                dropdownParent: $('#vendorModal')
            });
        });
</script>
@endpush

@endif
