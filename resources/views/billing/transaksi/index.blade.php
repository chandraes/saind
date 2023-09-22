@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
         <h1><u>FORM TRANSAKSI</u></h1>
        </div>
    </div>
    <div class="row justify-content-left mt-5">
        @if (auth()->user()->role === 'admin')

        @endif
        {{-- BACK BUTTON --}}
        <div class="col-md-4 text-center mt-5">
            <a href="{{url()->previous()}}" class="text-decoration-none">
                <img src="{{asset('images/muat.svg')}}" alt="" width="100">
                <h2>Nota Muat <span class="text-danger">{{$data->where('status', 1)->count() > 0 ? "(".$data->where('status', 1)->count().")" : '' }}</span></h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="{{url()->previous()}}" class="text-decoration-none">
                <img src="{{asset('images/bongkar.svg')}}" alt="" width="100">
                <h2>Nota Bongkar <span class="text-danger">{{$data->where('status', 2)->count() > 0 ? "(".$data->where('status', 2)->count().")" : '' }}</span></h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="{{url()->previous()}}" class="text-decoration-none">
                <img src="{{asset('images/back.svg')}}" alt="" width="100">
                <h2>KEMBALI</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="100">
                <h2>Dashboard</h2>
            </a>
        </div>
    </div>
</div>
@endsection
