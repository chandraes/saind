@extends('layouts.doc-nologo-1')
@section('content')
<div class="container-fluid">
    <center>
        <h2>REKAP KAS UANG JALAN</h2>
        <h2>{{$stringBulanNow}} {{$tahun}}</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-hover table-bordered" ">
            <thead class=" table-success">
            <tr>
                <th class="text-center align-middle table-pdf text-pdf" style="height: 35px">Tanggal</th>
                <th class="text-center align-middle table-pdf text-pdf">Nama Vendor</th>
                <th class="text-center align-middle table-pdf text-pdf">Nomor Lambung</th>
                <th class="text-center align-middle table-pdf text-pdf">Kas Uang Jalan</th>
                <th class="text-center align-middle table-pdf text-pdf">Uang Jalan</th>
                <th class="text-center align-middle table-pdf text-pdf">Masuk</th>
                <th class="text-center align-middle table-pdf text-pdf">Keluar</th>
                <th class="text-center align-middle table-pdf text-pdf">Saldo</th>
                <th class="text-center align-middle table-pdf text-pdf">Tambang</th>
                <th class="text-center align-middle table-pdf text-pdf">Rute</th>
                <th class="text-center align-middle table-pdf text-pdf">Cash/Transfer</th>
                <th class="text-center align-middle table-pdf text-pdf">Bank</th>
            </tr>
            </thead>
            <tbody>
                <tr class="table-warning" style="background-color: yellow">
                    <td class="text-center align-middle table-pdf text-pdf" colspan="3" style="height: 20px">Saldo Bulan
                        {{$stringBulan}} {{$tahunSebelumnya}}</td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="text-center align-middle table-pdf text-pdf">Rp. {{$dataSebelumnya ? number_format($dataSebelumnya->saldo,
                        0, ',','.') : ''}}</td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                </tr>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->tanggal}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->vendor ? $d->vendor->nama : ''}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->vehicle ? $d->vehicle->nomor_lambung : ''}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->nomor_kode_kas_uang_jalan ?
                        $d->kode_kas_uang_jalan.sprintf("%02d",$d->nomor_kode_kas_uang_jalan) : ''}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->nomor_uang_jalan ?
                            $d->kode_uang_jalan.sprintf("%02d",$d->nomor_uang_jalan) : ''}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->jenis_transaksi->id === 1 ?
                        number_format($d->nominal_transaksi, 0, ',', '.') : ''}}
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf text-danger">{{$d->jenis_transaksi->id === 2 ?
                        number_format($d->nominal_transaksi, 0, ',', '.') : ''}}
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">{{number_format($d->saldo, 0, ',', '.')}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->customer ? $d->customer->singkatan : ''}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->rute ? $d->rute->nama : ''}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->transfer_ke}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->bank}}</td>
                </tr>
                @endforeach
                <tr>
                    <td class="table-pdf text-pdf" style="height: 20px"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-center align-middle table-pdf text-pdf"><strong>GRAND TOTAL</strong></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="text-center align-middle table-pdf text-pdf"><strong>{{number_format($data->where('jenis_transaksi_id',
                            1)->sum('nominal_transaksi'), 0, ',', '.')}}</strong></td>
                    <td class="text-center align-middle table-pdf text-pdf text-danger"><strong>{{number_format($data->where('jenis_transaksi_id',
                            2)->sum('nominal_transaksi'), 0, ',', '.')}}</strong></td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        <strong>
                            {{$data->last() ? number_format($data->last()->saldo, 0, ',', '.') : ''}}
                        </strong>
                    </td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
