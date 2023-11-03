@extends('layouts.doc-nologo-2')
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
                    <th colspan="3" class="text-pdf table-pdf text-center align-middle" @if ($v->status == 'nonaktif')
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
                        <strong>Bongkar</strong>
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
                    @foreach ($vehicle as $v)
                    <td class="text-pdf table-pdf text-center align-middle" @if ($v->status == 'nonaktif')
                        style="background-color: red"
                        @endif>
                        @php
                        $rute = $data->where('nomor_lambung', $v->nomor_lambung)->where('tanggal', date('Y-m-d',
                        strtotime($i.'-'.$bulan_angka.'-'.$tahun)))->where('void', 0)->first()->kas_uang_jalan->rute->nama ?? '-';
                        @endphp
                        {{$rute}}
                    </td>
                    <td class="text-pdf table-pdf text-center align-middle" @if ($v->status == 'nonaktif')
                        style="background-color: red"
                        @endif>
                        {{-- check if tanggal bongkar is not null --}}
                        @php
                        $tgl_bongkar = $data->where('nomor_lambung', $v->nomor_lambung)->where('tanggal', date('Y-m-d',
                        strtotime($i.'-'.$bulan_angka.'-'.$tahun)))->first()->tanggal_bongkar ?? '-';
                        @endphp
                        {{-- {{$tgl_bongkar}} --}}
                        {{-- only show date day --}}
                        @if ($tgl_bongkar != '-' && $tgl_bongkar != '0000-00-00')
                        {{date('d-m', strtotime($tgl_bongkar))}}
                        @else
                        -
                        @endif

                    </td>
                    <td class="text-pdf table-pdf text-center align-middle" @if ($v->status == 'nonaktif')
                        style="background-color: red"
                        @endif>
                        @php
                        $tonase = $data->where('nomor_lambung', $v->nomor_lambung)->where('tanggal', date('Y-m-d',
                        strtotime($i.'-'.$bulan_angka.'-'.$tahun)))->first()->timbangan_bongkar ?? "-";
                        @endphp
                        {{$tonase}}
                    </td>
                    @endforeach
                    </tr>
                    @endfor
            </tbody>
            <tfoot>
                <tr>
                    <td class="text-pdf table-pdf text-center align-middle">
                        <strong>Rute Panjang</strong>
                    </td>
                    @foreach ($vehicle as $v)
                    @php
                    $total = $data->where('nomor_lambung', $v->nomor_lambung)->where('jarak', '>', 50)->count() ;
                    @endphp
                    <td colspan="3" class="text-pdf table-pdf text-center align-middle" @if ($v->status == 'nonaktif')
                        style="background-color: red"
                        @endif>
                        <strong>{{number_format($total, 0, ',', '.')}}</strong>
                    </td>
                    @endforeach
                </tr>
                <tr>
                    <td class="text-pdf table-pdf text-center align-middle">
                        <strong>Rute Pendek</strong>
                    </td>
                    @foreach ($vehicle as $v)
                    @php
                    $total = $data->where('nomor_lambung', $v->nomor_lambung)->where('jarak', '<=', 50)->count() ;
                    @endphp
                    <td colspan="3" class="text-pdf table-pdf text-center align-middle" @if ($v->status == 'nonaktif')
                        style="background-color: red"
                        @endif>
                        <strong>{{number_format($total, 0, ',', '.')}}</strong>
                    </td>
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
