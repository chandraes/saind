@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
         <h1><u>DOKUMEN</u></h1>
        </div>
    </div>
    <div class="row justify-content-left mt-5">
        <h2 class="my-2">UMUM</h2>

        {{-- <div class="col-md-3 text-center mb-5">
            <a href="{{route('template')}}" class="text-decoration-none">
                <img src="{{asset('images/sph.svg')}}" alt="" width="100">
                <h2>Template</h2>
            </a>
        </div>

        <div class="col-md-3 text-center mb-5">
            <a href="{{route('kontrak.index')}}" class="text-decoration-none">
                <img src="{{asset('images/kontrak.svg')}}" alt="" width="100">
                <h2>Kontrak</h2>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('spk.index')}}" class="text-decoration-none">
                <img src="{{asset('images/spk.svg')}}" alt="" width="100">
                <h2>SPK</h2>
            </a>
        </div> --}}
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('dokumen.kontrak-tambang')}}" class="text-decoration-none">
                <img src="{{asset('images/kontrak-tambang.svg')}}" alt="" width="70">
                <h4 class="mt-3">KONTRAK TAMBANG</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('dokumen.kontrak-vendor')}}" class="text-decoration-none">
                <img src="{{asset('images/kontrak-vendor.svg')}}" alt="" width="70">
                <h4 class="mt-3">KONTRAK VENDOR</h4>
            </a>
        </div>
        {{-- SPH ISI SAMA DENGAN COMPANY PROFILE BATASI 5MB --}}
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('dokumen.sph')}}" class="text-decoration-none">
                <img src="{{asset('images/sph.svg')}}" alt="" width="70">
                <h4 class="mt-3">SPH</h4>
            </a>
        </div>
        {{-- KOLOM PERTAMA BULAN JANUARI  --}}
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('dokumen.mutasi-rekening')}}" class="text-decoration-none">
                <img src="{{asset('images/mutasi-rekening.svg')}}" alt="" width="70">
                <h4 class="mt-3">MUTASI REKENING</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="70">
                <h4 class="mt-3">DASHBOARD</h4>
            </a>
        </div>
    </div>

</div>
@endsection
