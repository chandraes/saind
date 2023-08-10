@extends('layouts.doc-layout')
@section('title', 'Biodata Vendor')
@section('content')
<hr style="border-bottom: 1.4px solid;">
<div class="container-fluid justify-content-center">
    <center>
        <h2>Biodata Vendor {{$data->perusahaan}}</h1< /center>
</div>
<div class="container-fluid">
    <div class="row col-12 mt-3">
        <table class="table">
            <tr>
                <td class="text-pdf">1</td>
                <td class="text-pdf" style="width: 130px">Nama</td>
                <td class="text-pdf" style="width: 10px">:</td>
                <td class="text-pdf">{{$data->nama}}</td>
            </tr>
            <tr>
                <td class="text-pdf">2</td>
                <td class="text-pdf">Jabatan</td>
                <td class="text-pdf">:</td>
                <td class="text-pdf">{{$data->jabatan}}</td>
            </tr>
            <tr>
                <td class="text-pdf">3</td>
                <td class="text-pdf">Nama Perusahaan</td>
                <td class="text-pdf">:</td>
                <td class="text-pdf">{{$data->perusahaan}}</td>
            </tr>
            <tr>
                <td class="text-pdf">4</td>
                <td class="text-pdf">NPWP</td>
                <td class="text-pdf">:</td>
                <td class="text-pdf">{{$data->npwp}}</td>
            </tr>
            <tr>
                <td class="text-pdf">5</td>
                <td class="text-pdf">Alamat</td>
                <td class="text-pdf">:</td>
                <td class="text-pdf">{{$data->alamat}}</td>
            </tr>
            <tr>
                <td class="text-pdf">6</td>
                <td class="text-pdf">No HP</td>
                <td class="text-pdf">:</td>
                <td class="text-pdf">{{$data->no_hp}}</td>
            </tr>
            <tr>
                <td class="text-pdf">7</td>
                <td class="text-pdf">Email</td>
                <td class="text-pdf">:</td>
                <td class="text-pdf">{{$data->email}}</td>
            </tr>
            <tr>
                <td class="text-pdf">8</td>
                <td class="text-pdf">Nama Bank</td>
                <td class="text-pdf">:</td>
                <td class="text-pdf">{{$data->bank}}</td>
            </tr>
            <tr>
                <td class="text-pdf">9</td>
                <td class="text-pdf">No Rekening</td>
                <td class="text-pdf">:</td>
                <td class="text-pdf">{{$data->no_rekening}}</td>
            </tr>
            <tr>
                <td class="text-pdf">10</td>
                <td class="text-pdf">Nama Rekening</td>
                <td class="text-pdf">:</td>
                <td class="text-pdf">{{$data->nama_rekening}}</td>
            </tr>
            <tr class="">
                <td class="text-pdf">11</td>
                <td class="text-pdf">Sistem Pembayaran</td>
                <td class="text-pdf">:</td>
                <td class="text-pdf"></td>
            </tr>
        </table>
    </div>
    <div class="row">
        <table class="table table-pdf">
            <thead>
                <tr class="text-center align-middle text-pdf">
                    <th class="table-pdf">No</th>
                    <th class="table-pdf">Uraian</th>
                    @foreach ($customer as $c)
                        <th class="table-pdf">{{$c->singkatan}}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr class="table-pdf text-pdf">
                    <td class="table-pdf text-center">1</td>
                    <td class="table-pdf"><strong>OPNAME</strong></td>
                    @foreach ($customer as $c)
                    <td class="table-pdf"></td>
                    @endforeach
                </tr>
                <tr class="table-pdf text-pdf">
                    <td class="table-pdf text-center"></td>
                    <td class="table-pdf">* Harga :</td>

                    @foreach ($customer as $c)
                    @if ($data->vendor_bayar->where('pembayaran', 'opname')->where('customer_id', $c->id)->first())
                    <td class="table-pdf">Rp. {{number_format($data->vendor_bayar->where('pembayaran', 'opname')->where('customer_id', $c->id)->first()->harga_kesepakatan, 0, ',', '.')}}</td>
                    @else
                    <td class="table-pdf"></td>
                    @endif
                    @endforeach

                </tr>
                <tr class="table-pdf text-pdf">
                    <td class="table-pdf text-center">2</td>
                    <td class="table-pdf"><strong>TITIPAN</strong></td>
                    @foreach ($customer as $c)
                    <td class="table-pdf"></td>
                    @endforeach
                </tr>
                <tr class="table-pdf text-pdf">
                    <td class="table-pdf text-center"></td>
                    <td class="table-pdf">* Harga :</td>
                    @if ($data->vendor_bayar->where('pembayaran', 'titipan'))
                    @foreach ($data->vendor_bayar->where('pembayaran', 'titipan') as $c)
                        <td class="table-pdf">Rp. {{number_format($c->harga_kesepakatan, 0, ',', '.')}}</td>
                    @endforeach
                    @else
                    <td class="table-pdf"></td>
                    @endif
                </tr>
            </tbody>
        </table>
    </div>
    <div class="row">
        <span class="text-pdf"><strong>12. Mekanisme Uang Jalan</strong></span>
        <table class="table table-pdf text-pdf">
            <thead>
                <tr class="text-center align-middle text-pdf">
                    <th class="table-pdf">No</th>
                    <th class="table-pdf">Uraian</th>
                    <th class="table-pdf">Rute</th>
                    <th class="table-pdf">Jarak (Km)</th>
                    <th class="table-pdf">
                        @foreach ($customer as $c)
                        {{$c->singkatan}} @if (!$loop->last) / @endif
                        @endforeach
                    </th>
                    <th class="table-pdf">Harga Kesepakatan (
                    @foreach ($customer as $c)
                     {{$c->singkatan}} @if (!$loop->last) & @endif
                    @endforeach
                    )
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data->vendor_uang_jalan as $c)
                <tr class="table-pdf text-pdf">
                    <td class="table-pdf text-center">
                        @if ($loop->first)
                        1
                        @endif
                    </td>
                    <td class="table-pdf">@if ($loop->first)
                        Nilai uang jalan :
                        @endif</td>
                    <td class="table-pdf">* {{$c->rute->nama}}</td>
                    <td class="table-pdf text-center">{{$c->rute->jarak}}</td>
                    <td class="table-pdf">Rp. {{number_format($c->rute->uang_jalan, 0, ',', '.')}}</td>
                    <td class="table-pdf">Rp. {{number_format($c->hk_uang_jalan, 0, ',', '.')}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <span class="text-pdf"><strong>* Untuk sistem titipan tidak perlu mengisi poin 12 (mekanisme uang jalan)</strong></span><br>
    <span class="text-pdf"><strong>* Sponsor :</strong></span>
    <br><br><br>
    <div class="row text-pdf">
        <div class="row-pdf">
            <div class="column-pdf">
                <br>
                <span class="text-pdf">Dbuat Oleh,</span><br><br><br><br><br><br>
                <span class="text-pdf"><strong>{{$data->user->name}}</strong></span>
            </div>
            <div class="column-pdf">
                <span class="text-pdf"><strong>Muara Enim, {{$data->tanggal}}</strong></span><br>
                <span class="text-pdf">Disetujui Oleh,</span><br><br><br><br><br><br>
                <span class="text-pdf"><strong>Medy Andika</strong></span><br>
                <span class="text-pdf">Direktur Utama</span>
            </div>
        </div>
    </div>
</div>
@endsection
