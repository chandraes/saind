@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Invoice Tagihan</u></h1>
            <h1>{{$periode}}</h1>

        </div>
    </div>
    @php
    // $selectedData = [];
    $total_tagihan = $data ? $data->sum('nominal_tagihan') : 0;
    $ppn = $customer->ppn == 1 && $data ? $data->sum('nominal_tagihan') * 0.11 : 0;
    $pph = $customer->pph == 1 && $data ? $data->sum('nominal_tagihan') * 0.02 : 0;

    @endphp
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>{{$customer->nama}} ({{$customer->singkatan}})</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-8">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td>
                        <a href="{{route('per-customer.invoice-tagihan')}}"><img src="{{asset('images/invoice-tagihan.svg')}}"
                                alt="dokumen" width="30">
                            Invoice Tagihan
                        </a>
                    </td>
                    <td>
                        <a href="{{route('per-customer.invoice-tagihan.export', ['invoice' => $invoice_id])}}" target="_blank">
                            <img src="{{asset('images/document.svg')}}" alt="dokumen" width="30"> Export
                        </a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container-fluid mt-5">
    <table class="table table-bordered table-hover" id="notaTable">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">NOLAM</th>
                <th class="text-center align-middle">Rute</th>
                <th class="text-center align-middle">Jarak (Km)</th>
                <th class="text-center align-middle">Harga</th>
                @if ($customer->tanggal_muat == 1)
                <th class="text-center align-middle">Tanggal Muat</th>
                @endif
                @if ($customer->nota_muat == 1)
                <th class="text-center align-middle">Nota Muat</th>
                @endif
                @if ($customer->tonase == 1)
                <th class="text-center align-middle">Tonase Muat</th>
                @endif
                @if ($customer->tanggal_bongkar == 1)
                <th class="text-center align-middle">Tanggal Bongkar</th>
                @endif
                <th class="text-center align-middle">Nota Bongkar</th>
                <th class="text-center align-middle">Tonase Bongkar</th>
                @if ($customer->selisih == 1)
                <th class="text-center align-middle">Selisih (Ton)</th>
                <th class="text-center align-middle">Selisih (%)</th>
                @endif
                <th class="text-center align-middle">Tagihan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->vehicle->nomor_lambung}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->rute->nama}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->rute->jarak}}</td>
                <td class="text-center align-middle">
                    {{number_format($d->harga_customer, 0, ',', '.')}}
                </td>
                @if ($customer->tanggal_muat == 1)
                <td class="text-center align-middle">{{$d->id_tanggal_muat}}</td>
                @endif
                @if ($customer->nota_muat == 1)
                <td class="text-center align-middle">{{$d->nota_muat}}</td>
                @endif
                @if ($customer->tonase == 1)
                <td class="text-center align-middle">{{$d->tonase}}</td>
                @endif
                @if ($customer->tanggal_bongkar == 1)
                <td class="text-center align-middle">{{$d->id_tanggal_bongkar}}</td>
                @endif
                <td class="text-center align-middle">{{$d->nota_bongkar}}</td>
                <td class="text-center align-middle">{{$d->timbangan_bongkar}}</td>
                @if ($customer->selisih == 1)
                <td class="text-center align-middle">{{number_format($d->tonase - $d->timbangan_bongkar, 2, ',','.')}}
                </td>
                <td class="text-center align-middle">{{number_format(($d->tonase - $d->timbangan_bongkar)*0.1, 2,
                    ',','.')}}</td>
                @endif
                <td class="text-end align-middle">
                    @if ($d->kas_uang_jalan->customer->tagihan_dari == 1)
                    {{number_format(($d->nominal_tagihan), 0, ',', '.')}}
                    @elseif ($d->kas_uang_jalan->customer->tagihan_dari == 2)
                    {{number_format(($d->nominal_tagihan), 0, ',', '.')}}
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="text-center align-middle"
                    colspan="{{6 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 1 : 0) +
                                                                ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0)}}"></td>
                <td class="text-center align-middle"><strong>Total</strong></td>
                <td class="text-end align-middle">{{number_format($total_tagihan, 0, ',', '.')}}
                </td>
            </tr>
            <tr>
                <td class="text-center align-middle"
                    colspan="{{6 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 1 : 0) +
                                                                ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0)}}"></td>
                <td class="text-center align-middle"><strong>PPN</strong></td>
                <td class="text-end align-middle">

                    {{number_format($ppn, 0, ',', '.')}}

                </td>
            </tr>
            <tr>
                <td class="align-middle"
                    colspan="{{6 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 1 : 0) +
                                                                ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0)}}">
                </td>
                <td class="text-center align-middle"><strong>PPh</strong></td>
                <td class="text-end align-middle">
                    {{number_format($pph, 0, ',', '.')}}
                </td>
            </tr>
            <tr>
                <td class="align-middle"
                    colspan="{{6 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 1 : 0) +
                                                                ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0)}}">
                </td>
                <td class="text-center align-middle"><strong>Tagihan</strong></td>
                <td class="text-end align-middle"> <strong>
                        {{number_format($total_tagihan-$pph+$ppn, 0, ',', '.')}}</strong>
                </td>
            </tr>
        </tfoot>
    </table>
</div>


@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/js/dt-font.js')}}"></script>
<script src="{{asset('assets/js/dt-pdf.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    // hide alert after 5 seconds


    $(document).ready(function() {
        var table = $('#notaTable').DataTable({
            "paging": false,
            "ordering": true,
            "searching": false,
            "scrollCollapse": true,
            "scrollY": "550px",
            "fixedColumns": {
                "leftColumns": 3,
                "rightColumns": 1
            },
        });

    });

</script>
@endpush
