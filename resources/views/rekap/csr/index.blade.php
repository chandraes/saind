@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>NOTA LUNAS CSR</u></h1>
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
                    <td><a href="{{route('rekap.index')}}"><img src="{{asset('images/rekap.svg')}}"
                                alt="dokumen" width="30"> Rekap</a></td>
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
                    <th class="text-center align-middle">Customer</th>
                    <th class="text-center align-middle">Invoice</th>
                    <th class="text-center align-middle">Total CSR</th>
                </tr>
            </thead>
            <tbody>
                @php
                $total = 0;
                @endphp
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$d->tanggal}}</td>
                    <td class="text-center align-middle">{{$d->customer->singkatan}}</td>
                    <td class="text-center align-middle">
                        <a href="{{route('rekap.csr.detail', ['invoiceCsr' => $d])}}">{{$d->periode}}</a>
                    </td>
                    <td class="text-center align-middle">
                        {{number_format($d->total_csr, 0, ',', '.')}}
                    </td>
                </tr>
                @php
                $total += $d->total_csr;
                @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td></td>
                    <td class="text-right"><strong>Total:</strong></td>
                    <td class="text-center">{{number_format($total, 0, ',', '.')}}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endsection
    @push('css')
    <link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
    @endpush
    @push('js')
    <script src="{{asset('assets/plugins/date-picker/date-picker.js')}}"></script>
    <script src="{{asset('assets/js/dt5.min.js')}}"></script>
    <script>
        // hide alert after 5 seconds


        $(document).ready(function() {
            $('#data-table').DataTable({
                "paging": false,
                "scrollCollapse": true,
                "scrollY": "550px",
            });
        } );

        function toggleInputTambah() {
            var value = document.getElementById('vendor_id').value;
            if (value == '') {
                document.getElementById('row-input').hidden = true;
            } else {
                document.getElementById('row-input').hidden = false;
            }
        }
    </script>
    @endpush
