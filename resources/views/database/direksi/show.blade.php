@extends('layouts.doc-landscape')
@section('title')
Biodata {{ $data->nama }}
@endsection
@push('header')
<div class="mt-2">
    <center>
        <strong>
            <b>
                <h1>PT SAIND</h1>
                <h2>
                    BIODATA DIREKSI
                </h2>
            </b>
        </strong>
    </center>
</div>
@endpush
@section('content')
<div class="container-fluid">
    <div class="row-pdf text-pdf">
        <div class="column-pdf ">
            <div class="row-2">
                <div class="column-2 text-center align-middle">
                    <img src="{{storage_path('app/'.$data->foto_diri)}}" alt="foto" style="img-thumbnail img-fluid" height="200">
                </div>
                <div class="column-2 text-center align-middle">
                    <img src="{{storage_path('app/'.$data->foto_ktp)}}" alt="foto" style="img-thumbnail img-fluid" height="200">
                </div>
            </div>
        </div>
        <div class="column-pdf">
            <table class="mb-3">
                <tr>
                    <td class="text-pdf" style="width:200px">Nama</td>
                    <td class="text-pdf" style="width: 10px">:</td>
                    <td class="text-pdf">{{ $data->nama }}</td>
                </tr>
                <tr>
                    <td class="text-pdf">Nickname</td>
                    <td class="text-pdf">:</td>
                    <td class="text-pdf">{{ $data->nickname }}</td>
                </tr>
                <tr>
                    <td class="text-pdf">Jabatan</td>
                    <td class="text-pdf">:</td>
                    <td class="text-pdf">{{ $data->jabatan }}</td>
                </tr>
            </table>
            <table class="mb-3">
                <tr>
                    <td class="text-pdf" style="width: 200px">NIK</td>
                    <td class="text-pdf" style="width: 10px">:</td>
                    <td class="text-pdf">{{ $data->nik }}</td>
                </tr>
                <tr>
                    <td class="text-pdf">NPWP</td>
                    <td class="text-pdf">:</td>
                    <td class="text-pdf">{{ $data->npwp }}</td>
                </tr>
            </table>
            <table class="mb-3">
                <tr>
                    <td class="text-pdf" style="width: 200px">Nomor BPJS TK</td>
                    <td class="text-pdf" style="width: 10px">:</td>
                    <td class="text-pdf">{{ $data->bpjs_tk }}</td>
                </tr>
                <tr>
                    <td class="text-pdf">Nomor BPJS Kesehatan</td>
                    <td class="text-pdf">:</td>
                    <td class="text-pdf">{{ $data->bpjs_kesehatan }}</td>
                </tr>
            </table>
            <table class="mb-3">
                <tr>
                    <td class="text-pdf" style="width: 200px">Tempat, Tanggal Lahir</td>
                    <td class="text-pdf" style="width: 10px">:</td>
                    <td class="text-pdf">{{ $data->tempat_lahir }}, {{$data->tanggal_lahir}}</td>
                </tr>
                <tr>
                    <td class="text-pdf">Alamat</td>
                    <td class="text-pdf">:</td>
                    <td class="text-pdf">{{ $data->alamat }}</td>
                </tr>
            </table>
            <table class="mb-3">
                <tr>
                    <td class="text-pdf" style="width: 200px">Nomor HP</td>
                    <td class="text-pdf" style="width: 10px">:</td>
                    <td class="text-pdf">{{ implode('-',str_split($data->no_hp, 4)) }}</td>
                </tr>
                <tr>
                    <td class="text-pdf">Nomor WA</td>
                    <td class="text-pdf">:</td>
                    <td class="text-pdf">{{ implode('-',str_split($data->no_wa, 4)) }}</td>
                </tr>
            </table>
            <table class="mb-3">
                <tr>
                    <td class="text-pdf" style="width: 200px">Nama Bank</td>
                    <td class="text-pdf" style="width: 10px">:</td>
                    <td class="text-pdf">{{ $data->bank }}</td>
                </tr>
                <tr>
                    <td class="text-pdf">Nomor Rekening</td>
                    <td class="text-pdf">:</td>
                    <td class="text-pdf">{{ implode('-',str_split($data->no_rekening, 4)) }}</td>
                </tr>
                <tr>
                    <td class="text-pdf">Nama Rekening</td>
                    <td class="text-pdf">:</td>
                    <td class="text-pdf">{{ $data->nama_rekening }}</td>
                </tr>
            </table>
            <table class="mb-3">
                <tr>
                    <td class="text-pdf" style="width: 200px">Mulai Bekerja</td>
                    <td class="text-pdf" style="width: 10px">:</td>
                    <td class="text-pdf">{{ $data->mulai_bekerja }}</td>
                </tr>
                <tr>
                    <td class="text-pdf">Status</td>
                    <td class="text-pdf">:</td>
                    <td class="text-pdf">{{ strtoupper($data->status) }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>

@endsection
