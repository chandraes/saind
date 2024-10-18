@extends('layouts.doc-nologo-2')
@section('content')
<div class="container-fluid">
    <center>
        <h3><u>Tonase Tambang </u></h3>
        <h3>{{$customer->nama}}</h3>
        <h3>{{$nama_bulan}} {{$tahun}}</h3>
    </center>
</div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row mt-3">
        <table class="table table-bordered table-hover table-pdf text-pdf" id="rekapTable">
            @php
                $totalMuat = 0;
                $totalBongkar = 0;
                $monthlyTotalMuat = [];
                $monthlyTotalBongkar = [];
            @endphp
            <thead class="table-success">
                <tr>
                    <th rowspan="2" class="text-center align-middle table-pdf text-pdf">Tanggal</th>
                    @foreach ($dbRute as $rute)
                        <th colspan="3" class="text-center align-middle table-pdf text-pdf">{{ $rute->nama }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($dbRute as $rute)
                        <th class="text-center align-middle text-pdf table-pdf">Ritase</th>
                        <th class="text-center align-middle text-pdf table-pdf">Tonase Muat</th>
                        <th class="text-center align-middle text-pdf table-pdf">Tonase Bongkar</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @for ($i = 1; $i <= $date; $i++)
                    <tr>
                        <td class='text-center align-middle text-pdf table-pdf'>{{ sprintf('%02d', $i) . '-' . $bulan . '-' . $tahun }}</td>
                        @foreach ($dbRute as $rute)
                            @php
                                $dayData = $statistics[$i][$rute->id] ?? ['data' => ['ritase' => 0, 'tonase_muat' => 0, 'tonase_bongkar' => 0]];
                                $monthlyTotalRitase[$rute->id] = ($monthlyTotalRitase[$rute->id] ?? 0) + $dayData['data']['ritase'];
                                $monthlyTotalMuat[$rute->id] = ($monthlyTotalMuat[$rute->id] ?? 0) + $dayData['data']['tonase_muat'];
                                $monthlyTotalBongkar[$rute->id] = ($monthlyTotalBongkar[$rute->id] ?? 0) + $dayData['data']['tonase_bongkar'];
                            @endphp
                            <td class='text-center align-middle text-pdf table-pdf'>{{ ($dayData['data']['ritase']) }}</td>
                            <td class='text-center align-middle text-pdf table-pdf'>{{ ($dayData['data']['tonase_muat']) }}</td>
                            <td class='text-center align-middle text-pdf table-pdf'>{{ $dayData['data']['tonase_bongkar'] }}</td>
                        @endforeach
                    </tr>
                @endfor
            </tbody>
            <tfoot>
                <tr>
                    @php
                        $totalBongkar = array_sum($monthlyTotalBongkar);
                    @endphp
                    <th class="text-center align-middle text-pdf table-pdf" rowspan="2">Total</th>
                    @foreach ($dbRute as $rute)
                        <th class="text-center align-middle text-pdf table-pdf">{{ number_format($monthlyTotalRitase[$rute->id], 0, ',','.') ?? 0 }}</th>
                        <th class="text-center align-middle text-pdf table-pdf">{{ number_format($monthlyTotalMuat[$rute->id], 2, ',','.') ?? 0 }}</th>
                        <th class="text-center align-middle text-pdf table-pdf">{{ number_format($monthlyTotalBongkar[$rute->id], 2, ',','.') ?? 0 }}</th>
                    @endforeach
                </tr>
                <tr>
                    <th class="text-center align-middle text-pdf table-pdf" colspan="{{count($dbRute)*3-1}}">Grand Total</th>
                    <th class="text-center align-middle text-pdf table-pdf">{{ number_format($totalBongkar, 2, ',','.')}}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
