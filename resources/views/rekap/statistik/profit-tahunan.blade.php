@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1>Statistik Profit Tahun {{$tahun}}</h1>
        </div>
    </div>
    @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-8">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('statisik.index')}}"><img src="{{asset('images/statistik.svg')}}" alt="dokumen"
                                width="30"> STATISTIK</a></td>
                    <td>
                        <form target="_blank" action="{{route('statistik.profit-tahunan.print')}}" method="get">
                            <input type="hidden" name="tahun" value="{{$tahun}}">
                            <button class="btn" type="submit">
                                <img src="{{asset('images/document.svg')}}" alt="dokumen" width="30"> Print Rekap
                            </button>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="container-fluid mt-5">
        <form action="{{route('statistik.profit-tahunan')}}" method="get">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="tahun" class="form-label">Tahun</label>
                    <select class="form-select" name="tahun" id="tahun">
                        @foreach ($dataTahun as $d)
                        <option value="{{$d->tahun}}" {{$d->tahun == $tahun ? 'selected' : ''}}>{{$d->tahun}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="tahun" class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary form-control" id="btn-cari">Tampilkan</button>
                </div>
            </div>
        </form>
    </div>

    <div style="font-size: 15px">
        @php
        $totalProfitAll = 0;
        @endphp
        <table class="table table-bordered table-hover" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle" style="width: 8%">NOLAM</th>
                    <th class="text-center align-middle" style="width: 10%">Vendor</th>
                    @foreach($nama_bulan as $bulan)
                    <th class="text-center align-middle">{{$bulan}}</th>
                    @endforeach
                    <th class="text-center align-middle">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($statistics as $nomor_lambung => $stat)
                <tr>
                    <td class="text-center align-middle" @if ($stat['vehicle']->status == 'nonaktif')
                        style="background-color: red" @endif>
                        {{$nomor_lambung}}
                    </td>
                    <td class="text-center align-middle" @if ($stat['vehicle']->status == 'nonaktif')
                        style="background-color: red" @endif>
                        {{$stat['vendor']}}
                    </td>
                    @php
                    $totalProfitVehicle = 0;
                    @endphp
                    @foreach($stat['monthly'] as $profit)
                    <td class="text-center align-middle" @if ($stat['vehicle']->status == 'nonaktif')
                        style="background-color: red" @endif>
                        @if ($profit > 0)
                        {{number_format($profit, 0, ',', '.')}}
                        @endif
                        @php
                        $totalProfitVehicle += $profit;
                        $totalProfitAll += $profit;
                        @endphp
                    </td>
                    @endforeach
                    <td class="text-center align-middle">
                        <strong>{{number_format($totalProfitVehicle, 0, ',', '.')}}</strong>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"><strong>Grand Total</strong></td>
                    @for($i = 1; $i <= 12; $i++) <td class="text-center align-middle">
                        @php
                        $totalProfit = 0;
                        foreach ($statistics as $stat) {
                        $totalProfit += $stat['monthly'][$i] ?? 0;
                        }
                        @endphp
                        <strong>{{number_format($totalProfit, 0, ',', '.')}}</strong>
                        </td>
                        @endfor
                        <td class="text-center align-middle">
                            <strong>{{number_format($totalProfitAll, 0, ',', '.')}}</strong>
                        </td>
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
            $('#nominal_transaksi').maskMoney({
                thousands: '.',
                decimal: ',',
                precision: 0
            });
            $('#rekapTable').DataTable({
                "searching": false,
                "paging": false,
                "ordering": false,
                "scrollCollapse": true,
                "scrollY": "550px",
            });
        });
</script>
@endpush
