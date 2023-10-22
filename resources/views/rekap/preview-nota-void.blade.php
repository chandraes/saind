@extends('layouts.doc-nologo-1')
@section('content')
<div class="container-fluid">
    <center>
        <h2>REKAP NOTA VOID</h2>
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
                <th class="text-center align-middle table-pdf text-pdf">Uang Jalan</th>

                <th class="text-center align-middle table-pdf text-pdf">Tambang</th>
                <th class="text-center align-middle table-pdf text-pdf">Rute</th>
                <th class="text-center align-middle table-pdf text-pdf">Alasan</th>
            </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->kas_uang_jalan->tanggal}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->kas_uang_jalan->vendor->nama}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->kas_uang_jalan->vehicle->nomor_lambung}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{"UJ".sprintf("%02d",$d->kas_uang_jalan->nomor_uang_jalan)}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->kas_uang_jalan->customer->singkatan}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->kas_uang_jalan->rute->nama}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->alasan}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
