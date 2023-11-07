@extends('layouts.doc-nologo-2')
@section('content')
<div class="container-fluid">
    <center>
        <h2>STATISTIK PERFORM VENDOR</h2>
        <h2>{{$nama_bulan}} {{$tahun}}</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row mt-3">
        <table class="table table-bordered table-hover table-responsive" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle table-pdf text-pdf" style="width: 8%">Tanggal</th>
                    @foreach ($statistics as $s => $key)
                    <th class="text-center align-middle table-pdf text-pdf">
                        {{$s}}
                    </th>
                    @endforeach
                </tr>

            </thead>
            <tbody>
                @for ($i = 1; $i <= $date; $i++) <tr>
                    <td class="text-center align-middle table-pdf text-pdf">{{$i}}</td>
                    @foreach ($statistics as $s => $statistic)
                    <td class="text-center align-middle table-pdf text-pdf">
                        @if ($statistic['sisa'][$i] != '-')
                        {{number_format($statistic['sisa'][$i], 0, ',', '.')}}
                        @else
                        -
                        @endif
                    </td>
                    @endforeach
                    </tr>
                    @endfor
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-center align-middle table-pdf text-pdf">Sisa Terakhir Bulan {{$nama_bulan}} {{$tahun}}</th>
                    @foreach ($statistics as $s => $statistic)
                    <th class="text-center align-middle table-pdf text-pdf">
                        @for ($j = $date; $j >= 1; $j--)
                        @if ($statistic['sisa'][$j] != '-')
                        {{number_format($statistic['sisa'][$j], 0, ',', '.')}}
                        @break
                        @endif
                        @endfor
                    </th>
                    @endforeach
                </tr>
                <tr>
                    <th class="text-center align-middle text-pdf table-pdf">
                        Total Kasbon
                    </th>
                    <th colspan="{{count($statistics)}}" class="text-pdf table-pdf">
                        Rp. {{number_format($grand_total, 0, ',', '.')}}
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
