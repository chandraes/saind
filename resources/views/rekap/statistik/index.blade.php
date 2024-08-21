@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>STATISTIK</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="row justify-content-left mt-5">
        @if (auth()->user()->role == 'admin' || auth()->user()->role == 'su')
        <h1>PROFIT</h1>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('statistik.profit-harian')}}" class="text-decoration-none">
                <img src="{{asset('images/profit.svg')}}" alt="" width="80">
                <h5 class="mt-3">GRAND TOTAL<br>HARIAN<br>(KOTOR)</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('statisik.profit-bulanan')}}" class="text-decoration-none">
                <img src="{{asset('images/profit.svg')}}" alt="" width="80">
                <h5 class="mt-3">TOTAL<br>HARIAN<br>(KOTOR)</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('statistik.profit-tahunan')}}" class="text-decoration-none">
                <img src="{{asset('images/profit-tahunan.svg')}}" alt="" width="80">
                <h5 class="mt-3">TOTAL<br>BULANAN<br>(KOTOR)</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('statistik.profit-tahunan-bersih')}}" class="text-decoration-none">
                <img src="{{asset('images/profit-tahunan.svg')}}" alt="" width="80">
                <h5 class="mt-3">GRAND TOTAL<br>BULANAN<br>(BERSIH)</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('statistik.profit-tahunan-bersih')}}" class="text-decoration-none">
                <img src="{{asset('images/profit-tahunan.svg')}}" alt="" width="80">
                <h5 class="mt-3">GRAND TOTAL<br>TAHUNAN<br>(BERSIH)</h5>
            </a>
        </div>
        @endif
    </div>
    <hr>
    <div class="row justify-content-left mt-5">
        <h1>PERFORM UNIT</h1>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('statistik.perform-unit')}}" class="text-decoration-none">
                <img src="{{asset('images/perform-unit.svg')}}" alt="" width="80">
                <h5 class="mt-3">BULANAN</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('statistik.perform-unit-tahunan')}}" class="text-decoration-none">
                <img src="{{asset('images/perform-unit-tahunan.svg')}}" alt="" width="80">
                <h5 class="mt-3">TAHUNAN</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#upahGendongId">
                <img src="{{asset('images/statistik-ug.svg')}}" alt="" width="80">
                <h5 class="mt-3">UPAH GENDONG</h5>
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
                        <form action="{{route('statistik.upah-gendong')}}" method="get">
                            <div class="modal-body">
                                <div class="col-md-12 mb-3">
                                    <select class="form-select" name="vehicle_id" id="vehicle_id">
                                        @foreach ($data as $d)
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
        {{-- <div class="col-md-2 text-center mt-5">
            <a href="{{route('statistik.perform-unit-tahunan')}}" class="text-decoration-none">
                <img src="{{asset('images/aktivasi-maintenance.svg')}}" alt="" width="80">
                <h5 class="mt-3">MAINTENANCE</h5>
            </a>
        </div> --}}
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#ban_luar">
                <img src="{{asset('images/db-ban.svg')}}" alt="" width="80">
                <h5 class="mt-3">BAN LUAR</h5>
            </a>
            <div class="modal fade" id="ban_luar" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="ban-luarTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="ban-luarTitle">
                                Pilih NOLAM
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{route('statistik.ban-luar')}}" method="get">
                            <div class="modal-body">
                                <div class="col-md-12 mb-3">
                                    <select class="form-select" name="vehicle_id" id="vehicle_ban">
                                        @foreach ($vehicle as $d)
                                        <option value="{{$d->id}}">{{$d->nomor_lambung}}</option>
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
    <hr>
    <div class="row justify-content-left mt-5">
        <h1>OTHERS</h1>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('statistik.perform-vendor')}}" class="text-decoration-none">
                <img src="{{asset('images/statistik-vendor.svg')}}" alt="" width="80">
                <h5 class="mt-3">STATISTIK VENDOR</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('statistik.customer')}}" class="text-decoration-none">
                <img src="{{asset('images/statistik-customer.svg')}}" alt="" width="80">
                <h5 class="mt-3">STATISTIK CUSTOMER</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('rekap.index')}}" class="text-decoration-none">
                <img src="{{asset('images/back.svg')}}" alt="" width="80">
                <h5 class="mt-3">KEMBALI</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="80">
                <h5 class="mt-3">Dashboard</h5>
            </a>
        </div>
    </div>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script>
    $(document).ready(function () {
        $('#vehicle_ban').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih NOLAM',
            dropdownParent: $('#ban_luar')
        });
    });
</script>
@endpush
