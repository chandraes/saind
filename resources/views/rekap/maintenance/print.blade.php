@extends('layouts.doc-nologo-1')
@section('content')
<div class="container-fluid">
    <center>
        <h2>MAINTENANCE {{$vehicle->nomor_lambung}}</h2>
        <h2>{{$tahun}}</h2>
    </center>
</div>
<div class="container-fluid">
    <div style="width: 100%;">
        <div style="width: 50%; float: left;">

            <table class="text-pdf">
                <tr>
                    <td>
                        <h5>Nama Driver</h5>
                    </td>
                    <td style="padding-left:10px;padding-right:10px">
                        <h5>:</h5>
                    </td>
                    <td>
                        <h5>{{$vehicle->driver}}</h5>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5>Tgl Masuk Driver</h5>
                    </td>
                    <td style="padding-left:10px;padding-right:10px">
                        <h5>:</h5>
                    </td>
                    <td>
                        <h5>{{ \Carbon\Carbon::parse($vehicle->tanggal_masuk_driver)->format('d-m-Y') }}</h5>
                    </td>
                </tr>

            </table>
        </div>
        <div style="width: 50%; float: right;">
            <table class="text-pdf">
                <tr>
                    <td>
                        <h5>Pengurus</h5>
                    </td>
                    <td style="padding-left:10px;padding-right:10px">
                        <h5>:</h5>
                    </td>
                    <td>
                        <h5>{{$vehicle->pengurus}}</h5>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5>Tgl Masuk Pengurus</h5>
                    </td>
                    <td style="padding-left:10px;padding-right:10px">
                        <h5>:</h5>
                    </td>
                    <td>
                        <h5>{{ \Carbon\Carbon::parse($vehicle->tanggal_masuk_pengurus)->format('d-m-Y') }}</h5>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div style="clear: both;"></div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row mt-3">
        <table class="table table-hover table-bordered text-pdf table-pdf" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle text-pdf table-pdf">Periode</th>
                    <th class="text-center align-middle text-pdf table-pdf">Odo<br>meter</th>
                    <th class="text-center align-middle text-pdf table-pdf">Filter<br>Strainer</th>
                    <th class="text-center align-middle text-pdf table-pdf">Filter<br>Udara</th>
                    <th class="text-center align-middle text-pdf table-pdf">Stock<br>Baut</th>
                    @foreach ($equipment as $eq)
                    <th class="text-center align-middle text-pdf table-pdf">{!! implode('<br>', explode(' ', $eq->nama))
                        !!}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($weekly as $week => $equipmentCounts)
                <tr>
                    <td class="text-center align-middle text-pdf table-pdf">{{ $week }}</td>
                    <td class="text-center align-middle text-pdf table-pdf">
                        @if ($equipmentCounts['odometer'] == 0)
                        -
                        @else
                        {{ number_format($equipmentCounts['odometer'], 0, ',','.') }}
                        @endif
                    </td>
                    <td class="text-center align-middle text-pdf table-pdf">
                        @if ($equipmentCounts['filter_strainer']  == 0)
                        -
                        @elseif ($equipmentCounts['filter_strainer']  == 1)
                        V
                        @else
                        -
                        @endif
                    </td>
                    <td class="text-center align-middle text-pdf table-pdf">
                        @if ($equipmentCounts['filter_udara']  == 0)
                        -
                        @elseif ($equipmentCounts['filter_udara']  == 1)
                        V
                        @else
                        -
                        @endif
                    </td>
                    <td class="text-center align-middle text-pdf table-pdf">{{ $equipmentCounts['baut'] }}</td>
                    @foreach ($equipment as $eq)
                    <td class="text-center align-middle text-pdf table-pdf">
                        @if ($equipmentCounts[$eq->nama] == 0)
                        -
                        @else
                        {{ $equipmentCounts[$eq->nama] }}
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
