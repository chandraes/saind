@extends('layouts.doc-nologo-2')
@section('content')
<div class="container-fluid">
    <center>
        <h2>STATISTIK PROFIT</h2>
        <h2>{{$nama_bulan}} {{$tahun}}</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row mt-3">
        <table class="table table-hover table-bordered table-pdf text-pdf">
            <thead class="table-pdf text-pdf table-success">
                <tr>
                    <td class="text-center align-middle table-pdf text-pdf" style="font-size: 14px; font-weight: bold">Tanggal</td>
                    @foreach ($vehicle as $v)
                    <td class="text-center align-middle table-pdf text-pdf" style="font-size: 14px; font-weight: bold" @if ($v->status == 'nonaktif')
                        style="background-color: red; font-size: 14px; font-weight: bold"
                    @endif>{{$v->nomor_lambung}}</td>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @for ($i = 1; $i <= $date; $i++)
                <tr>
                    <td class="text-center align-middle table-pdf text-pdf" style="width: 8%">{{$i}}</td>
                        @foreach ($vehicle as $v)
                        <td class="text-center align-middle table-pdf text-pdf" @if ($v->status == 'nonaktif')
                            style="background-color: red"
                        @endif>
                            @php
                                $profit = $data->where('nomor_lambung', $v->nomor_lambung)->where('tanggal', date('Y-m-d', strtotime($i.'-'.$bulan_angka.'-'.$tahun)))->sum('profit') ?? 0;
                            @endphp
                            {{number_format($profit, 0, ',', '.')}}
                        </td>
                        @endforeach
                </tr>
                @endfor
                <tr>
                    <td class="text-center align-middle table-pdf text-pdf">
                        <strong>Total</strong>
                    </td>
                    @foreach ($vehicle as $v)
                    @php
                    $total = $data->where('nomor_lambung', $v->nomor_lambung)->sum('profit') ;
                    @endphp
                    <td class="text-center align-middle table-pdf text-pdf" @if ($v->status == 'nonaktif')
                        style="background-color: red"
                    @endif>
                        <strong>{{number_format($total, 0, ',', '.')}}</strong>
                    </td>
                    @endforeach
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td class="text-center align-middle table-pdf text-pdf" style="height: 20px"><strong>Grand Total</strong></td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        <strong>Rp. {{$data ? number_format($data->sum('profit'), 0, ',', '.') : 0}}</strong>
                    </td>
                    <td colspan="{{$vehicle->count()-1}}"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
