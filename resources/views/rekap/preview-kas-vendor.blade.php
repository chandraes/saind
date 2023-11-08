@extends('layouts.doc-nologo-1')
@section('content')
<div class="container-fluid">
    <center>
        <h2>REKAP KAS VENDOR</h2>
        <h2>{{$vendor->nama}}</h2>
        <h2>{{$stringBulanNow}} {{$tahun}}</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-hover table-bordered table-pdf text-pdf" >
            <thead class="table-success">
            <tr>
                <th class="text-center align-middle table-pdf text-pdf" style="height: 35px">Tanggal</th>
                <th class="text-center align-middle table-pdf text-pdf">Uraian</th>
                <th class="text-center align-middle table-pdf text-pdf">Nomor Lambung</th>
                <th class="text-center align-middle table-pdf text-pdf">Pinjaman/Pelunasan</th>
                <th class="text-center align-middle table-pdf text-pdf">Bayar</th>
                <th class="text-center align-middle table-pdf text-pdf">Sisa</th>
            </tr>
            <tr class="table-warning" style="background-color: yellow">
                <td colspan="3" class="text-center align-middle table-pdf text-pdf" style="height: 20px">Sisa Bulan
                    {{$stringBulan}} {{$tahunSebelumnya}}</td>
                <td class="text-center align-middle table-pdf text-pdf">
                    @if ($dataSebelumnya->sisa > 0)
                    Rp. {{$dataSebelumnya ?
                        number_format($dataSebelumnya->sisa, 0,',','.') : ''}}
                    @endif
                </td>
                <td class="text-center align-middle table-pdf text-pdf">
                    @if ($dataSebelumnya->sisa < 0)
                    Rp. {{$dataSebelumnya ?
                        number_format($dataSebelumnya->sisa, 0,',','.') : ''}}
                    @endif
                </td>
                <td class="table-pdf text-pdf">
                    Rp. {{$dataSebelumnya ? number_format($dataSebelumnya->sisa,
                        0, ',','.') : ''}}
                </td>
            </tr>
            </thead>
            <tbody>

                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->tanggal}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->uraian}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->vehicle_id ? $d->vehicle->nomor_lambung : ''}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{number_format($d->pinjaman, 0, ',', '.')}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{number_format($d->bayar, 0, ',','.')}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{number_format($d->sisa, 0, ',','.')}}</td>
                </tr>
                @endforeach
                <tr>
                    <td class="table-pdf text-pdf" style="height: 20px"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="text-center align-middle table-pdf text-pdf"><strong>Grand Total</strong> </td>
                    <td class="text-center align-middle table-pdf text-pdf">{{number_format($data->sum('pinjaman')+$dataSebelumnya->sisa, 0, ',','.')}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{number_format($data->sum('bayar'), 0, ',','.')}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{number_format($data->sum('pinjaman')+$dataSebelumnya->sisa-$data->sum('bayar'), 0, ',','.')}}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
