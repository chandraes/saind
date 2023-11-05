@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
         <h1><u>Template</u></h1>
        </div>
    </div>
    <div class="row justify-content-left mt-5">
        <div class="col-md-4 text-center mb-5">
            <a href="{{route('template-new.kontrak')}}" class="text-decoration-none">
                <img src="{{asset('images/kontrak.svg')}}" alt="" width="100">
                <h2>Template Kontrak</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mb-5">
            <a href="{{route('template-spk.index')}}" class="text-decoration-none">
                <img src="{{asset('images/spk.svg')}}" alt="" width="100">
                <h2>Template SPK</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mb-5">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="100">
                <h2>Dashboard</h2>
            </a>
        </div>
    </div>
</div>
@endsection
