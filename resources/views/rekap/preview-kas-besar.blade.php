@extends('layouts.doc-nologo-1')
@section('content')
<div class="container-fluid">
    <center>
        <h2>REKAP KAS BESAR</h2>
        <h2>{{$stringBulanNow}} {{$tahun}}</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row mt-3">
        <table class="table table-hover table-bordered table-pdf text-pdf">
            <thead class="table-pdf text-pdf table-success">
                <tr class="table-pdf text-pdf">
                    <th class="table-pdf text-pdf text-center align-middle" style="height: 35px">Tanggal</th>
                    <th class="table-pdf text-pdf text-center align-middle">Uraian</th>
                    <th class="table-pdf text-pdf text-center align-middle">Deposit</th>
                    <th class="table-pdf text-pdf text-center align-middle">Kas Kecil</th>
                    <th class="table-pdf text-pdf text-center align-middle">Kas Uang Jalan</th>
                    <th class="table-pdf text-pdf text-center align-middle">Tagihan</th>
                    <th class="table-pdf text-pdf text-center align-middle">Masuk</th>
                    <th class="table-pdf text-pdf text-center align-middle">Keluar</th>
                    <th class="table-pdf text-pdf text-center align-middle">Saldo</th>
                    <th class="table-pdf text-pdf text-center align-middle">Transfer Ke Rekening</th>
                    <th class="table-pdf text-pdf text-center align-middle">Bank</th>
                    <th class="table-pdf text-pdf text-center align-middle">Modal Investor</th>
                </tr>
            </thead>
            <tbody>
                <tr class="table-warning" style="background-color: yellow">
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf text-center align-middle"><strong>Saldo Bulan
                        {{$stringBulan}} {{$tahunSebelumnya}} </strong> </td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf text-center align-middle">Rp. {{$dataSebelumnya ? number_format($dataSebelumnya->saldo,
                        0, ',','.') : ''}}</td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf text-center align-middle">Rp. {{$dataSebelumnya ?
                        number_format($dataSebelumnya->modal_investor_terakhir, 0,',','.') : ''}}</td>
                </tr>
                @foreach ($data as $d)
                <tr>
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->tanggal}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->uraian}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->nomor_kode_deposit ?
                        $d->kode_deposit.sprintf("%02d",$d->nomor_kode_deposit) : ''}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->nomor_kode_kas_kecil ?
                        $d->kode_kas_kecil.sprintf("%02d",$d->nomor_kode_kas_kecil) : ''}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->nomor_kode_kas_uang_jalan ?
                        $d->kode_kas_uang_jalan.sprintf("%02d",$d->nomor_kode_kas_uang_jalan) : ''}}</td>
                    <td class="table-pdf text-pdf text-center align-middle"></td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->jenis_transaksi->id === 1 ?
                        number_format($d->nominal_transaksi, 0, ',', '.') : ''}}
                    </td>
                    <td class="table-pdf text-pdf text-center align-middle" style="">{{$d->jenis_transaksi->id === 2 ?
                        number_format($d->nominal_transaksi, 0, ',', '.') : ''}}
                    </td>
                    <td class="table-pdf text-pdf text-center align-middle">{{number_format($d->saldo, 0, ',', '.')}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->transfer_ke}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->bank}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{number_format($d->modal_investor, 0, ',', '.')}}</td>
                </tr>
                @endforeach
                <tr>
                    <td class="table-pdf text-pdf" style="height: 15px"></td>
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
                    <td colspan="4" class="table-pdf text-pdf text-center align-middle"><strong>GRAND TOTAL</strong></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf text-center align-middle"><strong>{{number_format($data->where('jenis_transaksi_id',
                            1)->sum('nominal_transaksi'), 0, ',', '.')}}</strong></td>
                    <td class="table-pdf text-pdf text-center align-middle text-danger">
                        <strong>{{number_format($data->where('jenis_transaksi_id',
                            2)->sum('nominal_transaksi'), 0, ',', '.')}}</strong></td>
                    {{-- latest saldo --}}
                    <td class="table-pdf text-pdf text-center align-middle">
                        <strong>
                            {{$data->last() ? number_format($data->last()->saldo, 0, ',', '.') : ''}}
                        </strong>
                    </td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf text-center align-middle">
                        <strong>
                            {{$data->last() ? number_format($data->last()->modal_investor_terakhir, 0, ',', '.') : ''}}
                        </strong>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
