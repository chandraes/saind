@extends('layouts.doc-nologo-1')
@section('content')
<div class="container-fluid">
    <center>
        <h2>Daftar Vendor</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-hover table-bordered" ">
            <thead class=" table-success">
            <tr>
                <th class="text-center align-middle table-pdf text-pdf" style="height: 35px">NAMA</th>
                <th class="text-center align-middle table-pdf text-pdf">PERUSAHAAN</th>
                <th class="text-center align-middle table-pdf text-pdf">NICKNAME</th>
                <th class="text-center align-middle table-pdf text-pdf">PEMBAYARAN</th>
                <th class="text-center align-middle table-pdf text-pdf">SO</th>
                <th class="text-center align-middle table-pdf text-pdf">PPN & PPh</th>
                <th class="text-center align-middle table-pdf text-pdf">PLAFON CASH</th>
                <th class="text-center align-middle table-pdf text-pdf">PLAFON STORING</th>
                <th class="text-center align-middle table-pdf text-pdf">STATUS</th>
                <th class="text-center align-middle table-pdf text-pdf">SPONSOR</th>
            </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="align-middle table-pdf text-pdf">{{$d->nama}}</td>
                    <td class="align-middle table-pdf text-pdf">{{$d->perusahaan}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->nickname}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{strtoupper($d->pembayaran)}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        @if ($d->support_operational == 1)
                        <span>V</span>
                        @endif
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        @if ($d->ppn == 1)
                        <span>V</span>
                        @endif
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        {{number_format($d->plafon_titipan,0,',','.')}}
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        {{number_format($d->plafon_lain,0,',','.')}}
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">{{strtoupper($d->status)}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->sponsor ? $d->sponsor->nama : ''}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
