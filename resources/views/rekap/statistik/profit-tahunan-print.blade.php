@extends('layouts.doc-nologo-2')
@section('content')
<div class="container-fluid">
    <center>
        <h2>STATISTIK PROFIT {{$tahun}}</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row mt-3">
        @php
        $totalProfitAll = 0;
        @endphp
        <table class="table table-bordered table-hover table-pdf text-pdf" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th class="table-pdf text-pdf text-center align-middle">NOLAM</th>
                    <th class="table-pdf text-pdf text-center align-middle" style="width: 10%">VENDOR</th>
                    @foreach($nama_bulan as $bulan)
                    <th class="table-pdf text-pdf text-center align-middle">{{$bulan}}</th>
                    @endforeach
                    <th class="table-pdf text-pdf text-center align-middle">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($statistics as $nomor_lambung => $stat)
                <tr>
                    <td class="table-pdf text-pdf text-center align-middle" @if ($stat['vehicle']->status == 'nonaktif')
                        style="background-color: red" @endif>
                        {{$nomor_lambung}}
                    </td>
                    <td class="table-pdf text-pdf text-center align-middle" @if ($stat['vehicle']->status == 'nonaktif')
                        style="background-color: red" @endif>
                        {{$stat['vendor']}}
                    </td>
                    @php
                    $totalProfitVehicle = 0;
                    @endphp
                    @foreach($stat['monthly'] as $profit)
                    <td class="table-pdf text-pdf text-center align-middle" @if ($stat['vehicle']->status == 'nonaktif')
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
                    <td class="table-pdf text-pdf text-center align-middle">
                        <strong>{{number_format($totalProfitVehicle, 0, ',', '.')}}</strong>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-center align-middle"><strong>Grand Total</strong></td>
                    @for($i = 1; $i <= 12; $i++) <td class="table-pdf text-pdf text-center align-middle">
                        @php
                        $totalProfit = 0;
                        foreach ($statistics as $stat) {
                        $totalProfit += $stat['monthly'][$i] ?? 0;
                        }
                        @endphp
                        <strong>{{number_format($totalProfit, 0, ',', '.')}}</strong>
                        </td>
                        @endfor
                        <td class="table-pdf text-pdf text-center align-middle">
                            <strong>{{number_format($totalProfitAll, 0, ',', '.')}}</strong>
                        </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
