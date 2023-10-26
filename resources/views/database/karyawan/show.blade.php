@extends('layouts.doc-landscape')
@section('title')
Biodata {{ $karyawan->nama }}
@endsection
@push('header')
<div class="mt-2">
    <center>
        <strong>
            <b>
                <h1>PT SAIND</h1>
                <h2>
                    BIODATA STAFF
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
                    <img src="{{storage_path('app/'.$karyawan->foto_diri)}}" alt="foto" style="img-thumbnail img-fluid" height="200">
                </div>
                <div class="column-2 text-center align-middle">
                    <img src="{{storage_path('app/'.$karyawan->foto_ktp)}}" alt="foto" style="img-thumbnail img-fluid" height="200">
                </div>
            </div>
        </div>
        <div class="column-pdf">
            <table class="mb-3">
                <tr>
                    <td class="text-pdf" style="width:200px">Nama</td>
                    <td class="text-pdf" style="width: 10px">:</td>
                    <td class="text-pdf">{{ $karyawan->nama }}</td>
                </tr>
                <tr>
                    <td class="text-pdf">Nickname</td>
                    <td class="text-pdf">:</td>
                    <td class="text-pdf">{{ $karyawan->nickname }}</td>
                </tr>
                <tr>
                    <td class="text-pdf">Jabatan</td>
                    <td class="text-pdf">:</td>
                    <td class="text-pdf">{{ $karyawan->jabatan->nama }}</td>
                </tr>
            </table>
            <table class="mb-3">
                <tr>
                    <td class="text-pdf" style="width: 200px">NIK</td>
                    <td class="text-pdf" style="width: 10px">:</td>
                    <td class="text-pdf">{{ $karyawan->nik }}</td>
                </tr>
                <tr>
                    <td class="text-pdf">NPWP</td>
                    <td class="text-pdf">:</td>
                    <td class="text-pdf">{{ $karyawan->npwp }}</td>
                </tr>
            </table>
            <table class="mb-3">
                <tr>
                    <td class="text-pdf" style="width: 200px">Nomor BPJS TK</td>
                    <td class="text-pdf" style="width: 10px">:</td>
                    <td class="text-pdf">{{ $karyawan->bpjs_tk }}</td>
                </tr>
                <tr>
                    <td class="text-pdf">Nomor BPJS Kesehatan</td>
                    <td class="text-pdf">:</td>
                    <td class="text-pdf">{{ $karyawan->bpjs_kesehatan }}</td>
                </tr>
            </table>
            <table class="mb-3">
                <tr>
                    <td class="text-pdf" style="width: 200px">Tempat, Tanggal Lahir</td>
                    <td class="text-pdf" style="width: 10px">:</td>
                    <td class="text-pdf">{{ $karyawan->tempat_lahir }}, {{$karyawan->tanggal_lahir}}</td>
                </tr>
                <tr>
                    <td class="text-pdf">Alamat</td>
                    <td class="text-pdf">:</td>
                    <td class="text-pdf">{{ $karyawan->alamat }}</td>
                </tr>
            </table>
            <table class="mb-3">
                <tr>
                    <td class="text-pdf" style="width: 200px">Nomor HP</td>
                    <td class="text-pdf" style="width: 10px">:</td>
                    <td class="text-pdf">{{ implode('-',str_split($karyawan->no_hp, 4)) }}</td>
                </tr>
                <tr>
                    <td class="text-pdf">Nomor WA</td>
                    <td class="text-pdf">:</td>
                    <td class="text-pdf">{{ implode('-',str_split($karyawan->no_wa, 4)) }}</td>
                </tr>
            </table>
            <table class="mb-3">
                <tr>
                    <td class="text-pdf" style="width: 200px">Nama Bank</td>
                    <td class="text-pdf" style="width: 10px">:</td>
                    <td class="text-pdf">{{ $karyawan->bank }}</td>
                </tr>
                <tr>
                    <td class="text-pdf">Nomor Rekening</td>
                    <td class="text-pdf">:</td>
                    <td class="text-pdf">{{ implode('-',str_split($karyawan->no_rekening, 4)) }}</td>
                </tr>
                <tr>
                    <td class="text-pdf">Nama Rekening</td>
                    <td class="text-pdf">:</td>
                    <td class="text-pdf">{{ $karyawan->nama_rekening }}</td>
                </tr>
            </table>
            <table class="mb-3">
                <tr>
                    <td class="text-pdf" style="width: 200px">Mulai Bekerja</td>
                    <td class="text-pdf" style="width: 10px">:</td>
                    <td class="text-pdf">{{ $karyawan->mulai_bekerja }}</td>
                </tr>
                <tr>
                    <td class="text-pdf">Status</td>
                    <td class="text-pdf">:</td>
                    <td class="text-pdf">{{ strtoupper($karyawan->status) }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>

@endsection
