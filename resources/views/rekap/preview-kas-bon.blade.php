@extends('layouts.doc-nologo-1')
@section('content')
<div class="container-fluid">
    <center>
        <h2>REKAP KASBON</h2>
        <h2>{{$stringBulanNow}} {{$tahun}}</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-hover table-bordered" ">
            <thead class=" table-success">
            <tr>
                <th class="text-center align-middle table-pdf text-pdf">Tanggal</th>
                <th class="text-center align-middle table-pdf text-pdf">Nama Karyawan</th>
                <th class="text-center align-middle table-pdf text-pdf">Nominal</th>
                <th class="text-center align-middle table-pdf text-pdf">Keterangan</th>
            </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->tanggal}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->karyawan->nama}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->nominal}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        @if ($d->lunas == 1)
                        Lunas
                        @else
                        Belum Lunas
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
