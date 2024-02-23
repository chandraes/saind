@extends('layouts.doc-nologo-1')
@section('content')
<div class="container-fluid">
    <center>
        <h2>NOTA TAGIHAN</h2>
        <h2><u>{{$customer->nama}} ({{$customer->singkatan}})</u></h2>
    </center>
    @php
        $total_tagihan = $data ? $data->sum('nominal_tagihan') : 0;
        $ppn = $customer->ppn == 1 && $data ? $data->sum('nominal_tagihan') * 0.11 : 0;
        $pph = $customer->pph == 1 && $data ? $data->sum('nominal_tagihan') * 0.02 : 0;
        $profit = $data->sum('profit');
        $profit_persen = count($data) > 0 ? ($data->sum('profit') / $data->sum('nominal_bayar')) * 100 : 0;
    @endphp
</div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row mt-3">
        <table class="table table-hover table-bordered text-pdf">
            <thead class="table-pdf text-pdf table-success">
                <tr class="table-pdf text-pdf">
                    <th rowspan="2" class="table-pdf text-pdf text-center align-middle">No</th>
                    <th rowspan="2" class="table-pdf text-pdf text-center align-middle">PERIODE PEKERJAAN</th>
                    <th rowspan="2" class="table-pdf text-pdf text-center align-middle">NO. SJB <br> (SURAT JALAN BATUBARA)</th>
                    <th rowspan="2" class="table-pdf text-pdf text-center align-middle">NO. LAMBUNG</th>
                    <th rowspan="2" class="table-pdf text-pdf text-center align-middle">RUTE</th>
                    <th rowspan="2" class="table-pdf text-pdf text-center align-middle">SJB PENERIMAAN</th>
                    <th colspan="3" class="table-pdf text-pdf text-center align-middle">TONASE</th>
                </tr>
                <tr>
                    <th class="table-pdf text-pdf text-center align-middle">GROSS</th>
                    <th class="table-pdf text-pdf text-center align-middle">TARRA</th>
                    <th class="table-pdf text-pdf text-center align-middle">NETTO</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="table-pdf text-pdf text-center align-middle">{{$loop->iteration}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$customer->tagihan_dari == 1 ? $d->id_tanggal_muat : $d->id_tanggal_bongkar}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->nota_muat}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">SAIND{{$d->kas_uang_jalan->vehicle->nomor_lambung}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->kas_uang_jalan->rute->nama}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->nota_bongkar}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$customer->tagihan_dari == 1 ? $d->gross_muat : $d->gross_bongkar}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$customer->tagihan_dari == 1 ? $d->tarra_muat : $d->tarra_bongkar}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$customer->tagihan_dari == 1 ? $d->tonase : $d->timbangan_bongkar}}</td>
                    @if ($customer->selisih == 1)
                    <td class="table-pdf text-pdf text-center align-middle">{{number_format($d->tonase - $d->timbangan_bongkar, 2, ',','.')}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{number_format(($d->tonase - $d->timbangan_bongkar)*0.1, 2, ',','.')}}</td>
                    @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="table-pdf text-pdf text-center align-middle">**</th>
                    <th class="table-pdf text-pdf text-center align-middle">**</th>
                    <th class="table-pdf text-pdf text-center align-middle">TOTAL</th>
                    <th class="table-pdf text-pdf text-center align-middle">**</th>
                    <th class="table-pdf text-pdf text-center align-middle"></th>
                    <th class="table-pdf text-pdf text-center align-middle"></th>
                    <th class="table-pdf text-pdf text-center align-middle"></th>
                    <th class="table-pdf text-pdf text-center align-middle"></th>
                    <th class="table-pdf text-pdf text-center align-middle">{{$customer->tagihan_dari == 1 ? $data->sum('tonase') : $data->sum('timbangan_bongkar')}}</th>
                    @if ($customer->selisih == 1)
                    <td class="table-pdf text-pdf text-center align-middle">**}</td>
                    <td class="table-pdf text-pdf text-center align-middle">**</td>
                    @endif
                </tr>
                {{-- <tr>
                    <td class="text-center align-middle"
                        colspan="{{6 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 3 : 0) +
                                                                    ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0)}}"></td>
                    <td class="table-pdf text-pdf text-center align-middle"><strong>PPN</strong></td>
                    <td align="right" class="table-pdf text-pdf align-middle">

                        {{number_format($ppn, 0, ',', '.')}}

                    </td>
                </tr>
                <tr>
                    <td class="align-middle"
                        colspan="{{6 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 3 : 0) +
                                                                    ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0)}}">
                    </td>
                    <td class="table-pdf text-pdf text-center align-middle"><strong>PPh</strong></td>
                    <td align="right" class="table-pdf text-pdf align-middle">

                        {{number_format($pph, 0, ',', '.')}}

                    </td>
                </tr>
                <tr>
                    <td class="align-middle"
                        colspan="{{6 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 3 : 0) +
                                                                    ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0)}}">
                    </td>
                    <td class="table-pdf text-pdf text-center align-middle"><strong>Tagihan</strong></td>
                    <td align="right" class="table-pdf text-pdf align-middle"> <strong>
                        {{number_format($total_tagihan-$pph+$ppn, 0, ',', '.')}}</strong>
                    </td>
                </tr> --}}
            </tfoot>
        </table>
    </div>
</div>
@endsection
