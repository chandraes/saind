@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-13 text-center">
            <h1><u>INVOICE BAYAR</u></h1>
            <h1>{{strtoupper($invoice->periode_invoice)}}</h1>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-13 text-center">
            <h1><u>{{$vendor->nama}}</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('billing.index')}}"><img src="{{asset('images/billing.svg')}}"
                                alt="dokumen" width="30"> Billing</a></td>
                      <td><a href="{{url()->previous()}}"><img src="{{asset('images/back.svg')}}"
                                    alt="dokumen" width="30"> Kembali</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>
@include('billing.nota-bayar.table-keranjang')

@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/plugins/date-picker/date-picker.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>

@endpush
