@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Void Nota Nomor Lambung {{$data->kas_uang_jalan->vehicle->nomor_lambung}}</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('billing.transaksi.index')}}"><img src="{{asset('images/transaction.svg')}}"
                                alt="dokumen" width="30"> Form Transaksi</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container mt-5">

</div>
@endsection
@push('css')
<link href="https://cdn.datatables.net/v/bs5/dt-1.13.5/datatables.min.css" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/plugins/date-picker/date-picker.js')}}"></script>
<script src="https://cdn.datatables.net/v/bs5/dt-1.13.5/datatables.min.js"></script>
<script>
    // hide alert after 5 seconds


    $(document).ready(function() {
        $('#data-table').DataTable();

    } );


</script>
@endpush
