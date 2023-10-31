@extends('layouts.doc-nologo-1')
@section('content')
<div class="container-fluid">
    <center>
        <h2>DAFTAR VEHICLES</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-hover table-bordered" ">
            <thead class=" table-success">
            <tr>
                <th class="text-center align-middle table-pdf text-pdf" style="height: 35px">NO</th>
                <th class="text-center align-middle table-pdf text-pdf" style="height: 35px">NOLAM</th>
                <th class="text-center align-middle table-pdf text-pdf">VENDOR</th>
                <th class="text-center align-middle table-pdf text-pdf">PERUSAHAAN</th>
                <th class="text-center align-middle table-pdf text-pdf">NOPOL</th>
                <th class="text-center align-middle table-pdf text-pdf">NAMA STNK</th>
                <th class="text-center align-middle table-pdf text-pdf">NO RANGKA</th>
                <th class="text-center align-middle table-pdf text-pdf">NO MESIN</th>
                <th class="text-center align-middle table-pdf text-pdf">TIPE</th>
                <th class="text-center align-middle table-pdf text-pdf">INDEX</th>
                <th class="text-center align-middle table-pdf text-pdf">TAHUN</th>
                <th class="text-center align-middle table-pdf text-pdf">GPS</th>
                <th class="text-center align-middle table-pdf text-pdf">STATUS</th>
            </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle table-pdf text-pdf" style="height: 35px">{{$loop->iteration}}</td>
                    <td class="text-center align-middle table-pdf text-pdf" >
                        <span @if ($d->no_index < 30 || $d->tahun < 2016) style="color:red" @endif>
                            {{$d->nomor_lambung}}
                        </span>
                    </td>
                    <td class="align-middle table-pdf text-pdf">{{$d->vendor->nama}}</td>
                    <td class="align-middle table-pdf text-pdf">{{$d->vendor->perusahaan}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->nopol}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->nama_stnk}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->no_rangka}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->no_mesin}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->tipe}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        <span @if ($d->no_index < 30) style="color: red" @endif>
                            {{$d->no_index}}
                        </span>
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        <span @if ($d->tahun < 2016) style="color: red" @endif>
                            {{$d->tahun}}
                        </span>
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        @if ($d->gps == 1)
                        <span>V</span>
                        @endif
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">{{strtoupper($d->status)}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
