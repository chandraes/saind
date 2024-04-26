@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>BAN LUAR </u></h1>
            <h1>{{$posisi->nama}}</h1>
            {{-- <h1>{{$nama_bulan}} {{$tahun}}</h1> --}}
        </div>
    </div>
    @include('swal')
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('rekap.index')}}"><img src="{{asset('images/rekap.svg')}}" alt="dokumen"
                                width="30"> REKAP</a></td>
                    <td><a href="{{route('statisik.index')}}"><img src="{{asset('images/statistik.svg')}}" alt="dokumen"
                                width="30"> STATISTIK</a></td>
                    <td>
                        <form action="{{route('statistik.ban-luar')}}" method="get">
                            <input type="hidden" name="vehicle_id" value="{{$vehicle->id}}">

                                <button class="btn" type="submit"> <img src="{{asset('images/back.svg')}}" alt="dokumen"
                                    width="30"> KEMBALI</button>
                        </form>
                    </td>
                </tr>
            </table>
        </div>

    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">MEREK</th>
                    <th class="text-center align-middle">NO. SERI BAN</th>
                    <th class="text-center align-middle">KONDISI BAN</th>
                    <th class="text-center align-middle">TGL GANTI BAN</th>
                    <th class="text-center align-middle">ACTION</th>
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
<link rel="stylesheet" href="{{asset('assets/js/flatpickr/flatpickr.min.css')}}">
@endpush
@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="{{asset('assets/js/moment.min.js')}}"></script>
<script>
    $(document).ready(function(){

        $('#rekapTable').DataTable({
            'processing': true,
            'serverSide': true,
            "searching": true,
            "ordering": false,
            'ajax': {
                'url': "{{route('statistik.ban-luar.histori-data')}}",
                'data': {
                    'vehicle': '{{$vehicle->id}}',
                    'posisi': '{{$posisi->id}}' // Added missing closing quote
                },
                'type': 'GET',
            },
            'columns':[
                {data: 'merk', name: 'merk', class:"text-center align-middle"},
                {data: 'no_seri', name: 'no_seri', class:"text-center align-middle"},
                {
                    data: 'kondisi',
                    name: 'kondisi',
                    class:"text-center align-middle",
                    "render": function (data, type, row, meta) {
                        return data + '%';
                    }
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    class:"text-center align-middle",
                    "render": function (data, type, row, meta) {
                        return moment(data).format('DD-MM-YYYY');
                    }
                },
                {
                    data: null,
                    name: 'ACT',
                    class:"text-center align-middle",
                    "render": function (data, type, row, meta) {
                        var url = "/statistik/ban-luar/histori-destroy/" + row.id;
                        return '<a href="' + url + '" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to delete this item?\')">Delete</a>';
                    }
                },
            ]
        });
    });
</script>
@endpush
