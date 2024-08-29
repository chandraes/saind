@extends('layouts.doc-nologo-2')
@section('content')
<div class="container-fluid">
    <center>
        <h2>STATISTIK PROFIT HARIAN (KOTOR)</h2>
        <h2>{{$nama_bulan}} {{$tahun}}</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row mt-3">
        <table class="table table-bordered table-hover text-pdf table-pdf" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle text-pdf table-pdf">Tanggal</th>
                    <th class="text-center align-middle text-pdf table-pdf">Profit Kotor</th>
                </tr>
            </thead>
            <tbody>
                @foreach(range(1, $date) as $i)
                    @php
                        $tanggal = sprintf('%04d-%02d-%02d', $tahun, $bulan, $i);
                        $profit = $profitHarian[$tanggal];
                    @endphp
                    <tr>
                        <td class="text-center align-middle text-pdf table-pdf" style="width: 8%">{{ $i }}</td>
                        <td class="text-center align-middle text-pdf table-pdf">{{ number_format($profit, 0, ',','.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>Grand Total</strong></td>
                    <td class="text-center align-middle text-pdf table-pdf"><strong>{{ number_format($grandTotal, 0, ',','.') }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
