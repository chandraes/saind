@extends('layouts.doc-nologo-3')
@section('content')
<div class="container-fluid">
    <center>
        <h2>Grand Total Tahunan (Bersih)</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row mt-3">
        <table class="table table-bordered table-hover table-responsive" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle text-pdf table-pdf">Tahun</th>
                    @foreach ($nama_bulan as $item => $value)
                    <th class="text-center align-middle text-pdf table-pdf">{{$value}}</th>
                    @endforeach
                    <th class="text-center align-middle text-pdf table-pdf">Grand Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statistics as $s => $su)
                <tr>
                    <td class="text-center align-middle text-pdf table-pdf">{{$s}}</td>
                    @foreach ($su['data'] as $item => $value)
                    <td class="text-end align-middle text-pdf table-pdf">{{number_format($value['bersih'], 0, ',', '.')}}</td>
                    @endforeach
                    <th class="text-end align-middle text-pdf table-pdf">{{number_format($su['total'], 0, ',', '.')}}</th>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
