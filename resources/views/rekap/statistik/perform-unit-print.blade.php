@extends('layouts.doc-nologo-3')
@section('content')
<div class="container-fluid">
    <center>
        <h2>STATISTIK PERFORM UNIT</h2>
        <h2>{{$nama_bulan}} {{$tahun}}</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row mt-3">
        <table class="table table-hover table-bordered table-pdf text-pdf">
            <thead class="table-pdf text-pdf table-success">
                <tr>
                    <th rowspan="2" class="text-pdf table-pdf text-center align-middle">Tanggal</th>
                    @foreach ($vehicle as $v)
                    <th colspan="2" class="text-pdf table-pdf text-center align-middle" @if ($v->status == 'nonaktif')
                        style="background-color: red" @endif>{{$v->nomor_lambung}} <br>
                        {{$v->vendor->nama}} @if ($v->gps == 1) <strong>(GPS)</strong> @endif @if($v->vendor->support_operational == 1)
                        <strong>(SO)</strong>
                        @endif
                    </th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($vehicle as $v)
                    <th class="text-pdf table-pdf text-center align-middle" @if ($v->status == 'nonaktif')
                        style="background-color: red"
                        @endif>
                        <strong>Rute</strong>
                    </th>
                    <th class="text-pdf table-pdf text-center align-middle" @if ($v->status == 'nonaktif')
                        style="background-color: red"
                        @endif>
                        <strong>Ton</strong>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @for ($i = 1; $i <= $date; $i++) <tr>
                    <td class="text-pdf table-pdf text-center align-middle" style="width: 3%">{{$i}}</td>
                    @foreach ($statistics as $statistic)
                    @foreach ($statistic['data'] as $data)
                    @if ($data['day'] == $i)
                    <td class="text-pdf table-pdf text-center align-middle" @if ($statistic['vehicle']->status == 'nonaktif')
                        style="background-color: red" @endif>
                        {{$data['rute']}}
                    </td>
                    <td class="text-pdf table-pdf text-center align-middle" @if ($statistic['vehicle']->status == 'nonaktif')
                        style="background-color: red" @endif>
                        {{$data['tonase']}}
                    </td>
                    @endif
                    @endforeach
                    @endforeach
                    </tr>
                    @endfor
            </tbody>
            <tfoot>
                <tr>
                    <td class="text-pdf table-pdf text-center align-middle">
                        <strong>Rute Panjang</strong>
                    </td>
                    @foreach ($statistics as $statistic)
                        <td colspan="2" class="text-pdf table-pdf text-center align-middle" @if ($statistic['vehicle']->status == 'nonaktif') style="background-color: red" @endif>
                            <strong>{{ number_format($statistic['long_route_count'], 0, ',', '.') }}</strong>
                        </td>
                    @endforeach
                </tr>
                <tr>
                    <td class="text-pdf table-pdf text-center align-middle">
                        <strong>Rute Pendek</strong>
                    </td>
                    @foreach ($statistics as $statistic)
                        <td colspan="2" class="text-pdf table-pdf text-center align-middle" @if ($statistic['vehicle']->status == 'nonaktif') style="background-color: red" @endif>
                            <strong>{{ number_format($statistic['short_route_count'], 0, ',', '.') }}</strong>
                        </td>
                    @endforeach
                </tr>
                <tr>
                    <td class="text-pdf table-pdf text-center align-middle">
                        <strong>Rute Pendek</strong>
                    </td>
                    @foreach ($statistics as $statistic)
                        <td colspan="2" class="text-pdf table-pdf text-center align-middle" @if ($statistic['vehicle']->status == 'nonaktif') style="background-color: red" @endif>
                            <strong>{{ number_format($statistic['short_route_count']+$statistic['long_route_count'], 0, ',', '.') }}</strong>
                        </td>
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
