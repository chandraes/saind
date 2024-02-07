@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>REKAP NOTA LUNAS</u></h1>
        </div>
    </div>
    @include('swal')
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

<div class="container table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-hover table-bordered" id="rekapTable">
            <thead class=" table-success">
            <tr>
                <th class="text-center align-middle">Tanggal</th>
                <th class="text-center align-middle">Periode</th>
                <th class="text-center align-middle">Nominal</th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    // hide alert after 5 seconds
    setTimeout(function() {
        $('#alert').fadeOut('slow');
    }, 5000);

    $(document).ready(function() {
        $('#rekapTable').DataTable({
            'scrollY': "550px",
            'scrollCollapse': true,
            'paging': false,
            'processing': true,
            'serverSide': true,
            ajax: {
                url: '{{route('per-customer.nota-lunas.data')}}',
                type: 'GET',
            },
            columns: [
                {data: 'id_tanggal', name: 'id_tanggal', class: 'text-center', searchable: true, orderData: [0]},
                {
                    data: 'periode',
                    name: 'periode',
                    class: 'text-center',
                    searchable: true,
                    orderData: [1],
                    render: function(data, type, row) {
                        return '<a href="/per-customer/nota-lunas/' + row.id + '/detail">' + data + '</a>';
                    }
                },
                {
                    data: 'total_tagihan',
                    name: 'total_tagihan',
                    class: 'text-end',
                    searchable: true,
                    orderData: [2],
                    render: function(data, type, row) {
                        return Number(data).toLocaleString('id-ID'); // format as number in Indonesian locale
                    }
                },
            ]
        });

    } );

</script>
@endpush
