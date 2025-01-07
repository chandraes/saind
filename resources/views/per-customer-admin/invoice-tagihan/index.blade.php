@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>INVOICE TAGIHAN</u></h1>
        </div>
    </div>
    @include('swal')
    {{-- error validation show in swal --}}
    @if ($errors->any())
    <script>
        Swal.fire({
            title: 'Error!',
            text: '{{$errors->first()}}',
            icon: 'error',
            confirmButtonText: 'Ok'
        })
    </script>
    @endif
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container mt-5 table-responsive ">
    <table class="table table-bordered table-hover" id="data-table">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">Tanggal</th>
                <th class="text-center align-middle">Tambang</th>
                <th class="text-center align-middle">Invoice</th>
                <th class="text-center align-middle">DPP</th>
                <th class="text-center align-middle">Penyesuaian</th>
                <th class="text-center align-middle">Penalti</th>
                <th class="text-center align-middle">Ppn</th>
                <th class="text-center align-middle">Pph</th>
                <th class="text-center align-middle">Total Tagihan</th>
                <th class="text-center align-middle">Balance</th>
                <th class="text-center align-middle">Sisa Tagihan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-center align-middle">{{$d->id_tanggal}}</td>
                <td class="text-center align-middle">{{$d->customer->singkatan}}</td>
                <td class="text-center align-middle">
                    <a href="{{route('per-customer-admin.invoice-tagihan.detail', $d)}}"> {{$d->periode}}</a>
                </td>
                <td class="text-end align-middle">
                    {{$d->nf_total_awal}}
                </td>
                <td class="text-end align-middle">
                    {{$d->nf_penyesuaian}}
                </td>
                <td class="text-end align-middle">
                    {{$d->nf_penalty}}
                </td>
                <td class="text-end align-middle @if ($d->ppn_dipungut == 0) table-danger @endif">
                    {{$d->nf_ppn}}
                </td>
                <td class="text-end align-middle">
                    {{$d->nf_pph}}
                </td>
                <td class="text-end align-middle">
                    {{number_format($d->total_tagihan, 0, ',', '.')}}
                </td>
                <td class="text-end align-middle">
                    {{number_format($d->total_bayar, 0, ',', '.')}}
                </td>
                <td class="text-end align-middle">
                    {{number_format($d->sisa_tagihan, 0, ',', '.')}}
                </td>
            </tr>
            {{-- <button class="btn btn-primary">Test</button> --}}

            @endforeach
        </tbody>
    </table>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    // hide alert after 5 seconds


    $(document).ready(function() {
        $('#data-table').DataTable();

    } );


</script>
@endpush
