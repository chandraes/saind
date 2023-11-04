@extends('layouts.doc-nologo-3')
@section('content')
<div class="container-fluid">
    <center>
        <h2>STATISTIK PERFORM UNIT {{$tahun}}</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row mt-3">
        <table class="table table-bordered table-hover table-responsive" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th rowspan="2" class="text-center align-middle text-pdf table-pdf">NOLAM</th>
                    @for ($month = 1; $month <= 12; $month++)
                        <th colspan="2" class="text-center align-middle text-pdf table-pdf">{{ date('F', mktime(0, 0, 0, $month, 10)) }}</th>
                    @endfor
                    <th colspan="2" class="text-center align-middle text-pdf table-pdf">Total</th>
                </tr>
                <tr>
                    @for ($month = 1; $month <= 12; $month++)
                        <th class="text-center align-middle text-pdf table-pdf">
                            <strong>Rute Panjang</strong>
                        </th>
                        <th class="text-center align-middle text-pdf table-pdf">
                            <strong>Rute Pendek</strong>
                        </th>
                    @endfor
                    <th class="text-center align-middle text-pdf table-pdf">Rute Panjang</th>
                    <th class="text-center align-middle text-pdf table-pdf">Rute Pendek</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($statistics as $statistic)
                    <tr>
                        <td class="text-center align-middle text-pdf table-pdf">{{ $statistic['vehicle']->nomor_lambung }}</td>
                        @php
                            $totalLongRoute = 0;
                            $totalShortRoute = 0;
                        @endphp
                        @foreach ($statistic['monthly'] as $month)
                            @php
                                $totalLongRoute += $month['long_route_count'];
                                $totalShortRoute += $month['short_route_count'];
                            @endphp
                            <td class="text-center align-middle text-pdf table-pdf @if ($statistic['vehicle']->status == 'nonaktif') table-danger @endif" >
                                @if ($month['long_route_count'] == 0)
                                    @php
                                        $month['long_route_count'] = '';
                                    @endphp
                                @endif
                                {{ $month['long_route_count'] }}
                            </td>
                            <td class="text-center align-middle text-pdf table-pdf" @if ($statistic['vehicle']->status == 'nonaktif') style="background-color: red" @endif>
                                @if ($month['short_route_count'] == 0)
                                    @php
                                        $month['short_route_count'] = '';
                                    @endphp
                                @endif
                                {{ $month['short_route_count'] }}
                            </td>
                        @endforeach
                        <td class="text-center align-middle text-pdf table-pdf">{{ $totalLongRoute }}</td>
                        <td class="text-center align-middle text-pdf table-pdf">{{ $totalShortRoute }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
