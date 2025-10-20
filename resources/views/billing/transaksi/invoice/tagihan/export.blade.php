@extends('layouts.doc-nologo-2')
@section('content')
<div class="container-fluid">
    <center>
        <h2>INVOICE TAGIHAN</h2>
        <h2>{{strtoupper($periode)}}</h2>
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
                    <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="table-pdf text-pdf text-center align-middle">No</th>
                    <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="table-pdf text-pdf text-center align-middle">Nomor Lambung</th>
                    <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="table-pdf text-pdf text-center align-middle">Vendor</th>
                    <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="table-pdf text-pdf text-center align-middle">Rute</th>
                    <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="table-pdf text-pdf text-center align-middle">Jarak (Km)</th>
                    <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="table-pdf text-pdf text-center align-middle">Harga</th>
                    @if ($customer->tanggal_muat == 1)
                    <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="table-pdf text-pdf text-center align-middle">Tanggal Muat</th>
                    @endif
                    @if ($customer->nota_muat == 1)
                    <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="table-pdf text-pdf text-center align-middle">Nota Muat</th>
                    @endif
                    @if ($customer->tonase == 1)
                    <th @if($customer->gt_muat == 1) colspan="3" @elseif($customer->gt_bongkar == 1) rowspan="2" @endif class="table-pdf text-pdf text-center align-middle">Tonase Muat</th>
                    @endif
                    @if ($customer->tanggal_bongkar == 1)
                    <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="table-pdf text-pdf text-center align-middle">Tanggal Bongkar</th>
                    @endif
                    <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="table-pdf text-pdf text-center align-middle">Nota Bongkar</th>
                    <th @if($customer->gt_bongkar == 1) colspan="3" @elseif($customer->gt_muat == 1) rowspan="2" @endif class="table-pdf text-pdf text-center align-middle">Tonase Bongkar</th>
                    @if ($customer->selisih == 1)
                    <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="table-pdf text-pdf text-center align-middle">Selisih (Ton)</th>
                    <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="table-pdf text-pdf text-center align-middle">Selisih (%)</th>
                    @endif
                    <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="table-pdf text-pdf text-center align-middle">Tagihan</th>
                </tr>
                @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1)
                <tr>
                    @if ($customer->gt_muat == 1)
                    <th class="table-pdf text-pdf text-center align-middle">Gross</th>
                    <th class="table-pdf text-pdf text-center align-middle">Tarra</th>
                    <th class="table-pdf text-pdf text-center align-middle">Netto</th>
                    @endif
                    @if ($customer->gt_bongkar == 1)
                    <th class="table-pdf text-pdf text-center align-middle">Gross</th>
                    <th class="table-pdf text-pdf text-center align-middle">Tarra</th>
                    <th class="table-pdf text-pdf text-center align-middle">Netto</th>
                    @endif
                </tr>
                @endif
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>

                    <td class="table-pdf text-pdf text-center align-middle">{{$loop->iteration}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->kas_uang_jalan->vehicle->nomor_lambung}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->kas_uang_jalan->vendor->nickname}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->kas_uang_jalan->rute->nama}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->kas_uang_jalan->rute->jarak}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">
                        {{number_format($d->kas_uang_jalan->customer->customer_tagihan->where('customer_id', $d->kas_uang_jalan->customer_id)
                                                                        ->where('rute_id', $d->kas_uang_jalan->rute_id)
                                                                        ->first()->harga_tagihan, 0, ',', '.')}}
                    </td>
                    @if ($customer->tanggal_muat == 1)
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->id_tanggal_muat}}</td>
                    @endif
                    @if ($customer->nota_muat == 1)
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->nota_muat}}</td>
                    @endif
                    @if ($customer->tonase == 1)
                    @if ($customer->gt_muat == 1)
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->gross_muat}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->tarra_muat}}</td>
                    @endif
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->tonase}}</td>
                    @endif
                    @if ($customer->tanggal_bongkar == 1)
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->id_tanggal_bongkar}}</td>
                    @endif
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->nota_bongkar}}</td>
                    @if ($customer->gt_bongkar == 1)
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->gross_bongkar}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->tarra_bongkar}}</td>
                    @endif
                    <td class="table-pdf text-pdf text-center align-middle">{{$d->timbangan_bongkar}}</td>
                    @if ($customer->selisih == 1)
                    <td class="table-pdf text-pdf text-center align-middle">{{number_format($d->tonase - $d->timbangan_bongkar, 2, ',','.')}}</td>
                    <td class="table-pdf text-pdf text-center align-middle">{{number_format(($d->tonase - $d->timbangan_bongkar)*0.1, 2, ',','.')}}</td>
                    @endif
                    <td class="table-pdf text-pdf text-center align-middle">
                        @if ($d->kas_uang_jalan->customer->tagihan_dari == 1)
                        {{number_format(($d->nominal_tagihan), 0, ',', '.')}}
                        @elseif ($d->kas_uang_jalan->customer->tagihan_dari == 2)
                        {{number_format(($d->nominal_tagihan), 0, ',', '.')}}
                        @endif
                    </td>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td class="text-center align-middle"
                        colspan="{{7 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 1 : 0) +
                                                                    ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0)}}">
                    </td>
                    <td class="table-pdf text-pdf text-center align-middle"><strong>Total</strong></td>
                    <td align="right" class="table-pdf text-pdf align-middle">{{number_format($total_tagihan, 0, ',',
                        '.')}}
                    </td>
                </tr>
                <tr>
                    <td class="text-center align-middle"
                        colspan="{{7 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 1 : 0) +
                                                                    ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0)}}">
                    </td>
                    <td class="table-pdf text-pdf text-center align-middle"><strong>PPN</strong></td>
                    <td align="right" class="table-pdf text-pdf align-middle" @if ($invoice->ppn_dipungut == 0)
                        style="background-color: red; color: white;"
                        @endif>

                        {{number_format($invoice->ppn, 0, ',', '.')}}

                    </td>
                </tr>
                <tr>
                    <td class="align-middle"
                        colspan="{{7 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 1 : 0) +
                                                                    ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0)}}">
                    </td>
                    <td class="table-pdf text-pdf text-center align-middle"><strong>PPh</strong></td>
                    <td align="right" class="table-pdf text-pdf align-middle">

                        {{number_format($invoice->pph, 0, ',', '.')}}

                    </td>
                </tr>
                <tr>
                    <td class="align-middle"
                        colspan="{{7 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 1 : 0) +
                                                                    ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0)}}">
                    </td>
                    <td class="table-pdf text-pdf text-center align-middle"><strong>Tagihan</strong></td>
                    <td align="right" class="table-pdf text-pdf align-middle"> <strong>
                            {{number_format($invoice->total_tagihan + $invoice->pinalty_akhir, 0, ',', '.')}}</strong>
                    </td>
                </tr>
                <tr>
                    <td class="align-middle"
                        colspan="{{7 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 1 : 0) +
                                                                    ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0)}}">
                    </td>
                    <td class="table-pdf text-pdf text-center align-middle"><strong>Penalty</strong></td>
                    <td align="right" class="table-pdf text-pdf align-middle"> <strong>
                            {{number_format($invoice->pinalty_akhir, 0, ',', '.')}}</strong>
                    </td>
                </tr>
                  <tr>
                    <td class="align-middle"
                        colspan="{{7 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 1 : 0) +
                                                                    ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0)}}">
                    </td>
                    <td class="table-pdf text-pdf text-center align-middle"><strong>Grand Total Tagihan</strong></td>
                    <td align="right" class="table-pdf text-pdf align-middle"> <strong>
                            {{number_format($invoice->total_tagihan, 0, ',', '.')}}</strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
