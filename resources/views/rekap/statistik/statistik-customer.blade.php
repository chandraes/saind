@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1>Statistik Customer</h1>
        </div>
    </div>
    @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-8">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('rekap.index')}}"><img src="{{asset('images/rekap.svg')}}" alt="dokumen"
                                width="30"> REKAP</a></td>
                    <td><a href="{{route('statisik.index')}}"><img src="{{asset('images/statistik.svg')}}" alt="dokumen"
                                width="30"> STATISTIK</a></td>
                </tr>
            </table>
        </div>
    </div>
    <div >
        @php
        $totalProfitAll = 0;
        @endphp
        <table class="table table-bordered table-hover" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">Customer</th>
                    <th class="text-center align-middle">Total Tagihan Sebelum PPn & Pph</th>
                    <th class="text-center align-middle">Total Invoice</th>
                    <th class="text-center align-middle">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $grandTotal = 0;
                @endphp
                @foreach($statistics as $statistic)
                    @if ($statistic['total'] > 0)
                    <tr>
                        <td class="text-center align-middle">{{ $statistic['customer']->singkatan }}</td>
                        <td class="align-middle" align="right">{{ number_format($statistic['total_nominal_tagihan'], 0, ',', '.') }}</td>
                        <td class="align-middle"  align="right">{{ number_format($statistic['total_sisa_tagihan'], 0, ',', '.') }}</td>
                        <td class="align-middle"  align="right">{{ number_format($statistic['total'], 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @php
                        $grandTotal += $statistic['total'];
                    @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-center align-middle">Grand Total</th>
                    <th class="text-end align-middle">{{ number_format($grandTotal, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
{{-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script> --}}
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    $(document).ready(function(){

            $('#rekapTable').DataTable({
                "searching": false,
                "paging": false,
                "scrollCollapse": true,
                "scrollY": "550px",
            });
        });
</script>
@endpush
