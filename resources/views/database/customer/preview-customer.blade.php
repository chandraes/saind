@extends('layouts.doc-nologo-1')
@section('content')
<div class="container-fluid">
    <center>
        <h2>DAFTAR CUSTOMER</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-hover table-bordered" ">
            <thead class=" table-success">
            <tr>
                <th class="text-center align-middle table-pdf text-pdf" style="height: 35px">No</th>
                <th class="text-center align-middle table-pdf text-pdf" style="height: 35px">NAMA CUSTOMER</th>
                <th class="text-center align-middle table-pdf text-pdf">CONTACT PERSON</th>
                <th class="text-center align-middle table-pdf text-pdf">HARGA TAGIHAN</th>
                <th class="text-center align-middle table-pdf text-pdf">RUTE</th>
                <th class="text-center align-middle table-pdf text-pdf">PPN & PPh</th>
                <th class="text-center align-middle table-pdf text-pdf">BAYAR DARI</th>
                <th class="text-center align-middle table-pdf text-pdf">STATUS</th>
            </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle table-pdf text-pdf" style="height: 35px">{{$loop->iteration}}</td>
                    <td class="align-middle table-pdf text-pdf"><strong>{{$d->nama}} ({{$d->singkatan}})</strong></td>
                    <td class="align-middle table-pdf text-pdf">{{$d->contact_person}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        @foreach ($d->customer_tagihan as $t)
                        Rp. {{number_format($t->harga_tagihan, 0, ',', '.')}} <br>
                        @endforeach
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        @foreach ($d->rute as $r)
                        {{$r->nama}} <br>
                        @endforeach
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        @if ($d->ppn == 1)
                        <span>V</span>
                        @endif
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        @if ($d->tagihan_dari == 1)
                        Tonase Muat
                        @elseif($d->tagihan_dari == 2)
                        Tonase Bongkar
                        @endif
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        @if ($d->status == 1)
                        <span>Aktif</span>
                        @else
                        <span>Non-Aktif</span>
                        @endif
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
