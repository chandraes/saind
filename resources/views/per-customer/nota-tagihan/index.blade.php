@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Nota Tagihan</u></h1>
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
    {{-- if errors has any --}}
    @if ($errors->any())
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Whoops!</strong> Ada kesalahan saat input data, yaitu:
                <ul>
                    @foreach ($errors->all() as $error)
                    <li><strong>{{$error}}</strong></li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @endif
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td>
                        <form target="_blank" action="{{route('per-customer.nota-tagihan.print')}}" method="get"
                            id="form-print">
                            <input type="hidden" name="rute_id" value="{{$rute_id}}">
                            <input type="hidden" name="filter_date" value="{{$filter_date}}">
                            <input type="hidden" name="tanggal_filter" value="{{$tanggal_filter}}">
                            <a href="#" onclick="document.getElementById('form-print').submit();">
                                <img src="{{asset('images/document.svg')}}" alt="dokumen" width="30"> Export
                            </a>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
@include('per-customer.nota-tagihan.filter')
<div class="container-fluid mt-3 table-responsive ">
    <table class="table table-bordered table-hover" id="notaTable">
        <thead class="table-success">
            <tr>
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
                <td class=""
                    colspan="{{5 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 1 : 0) +
                                                                ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0)}}">

                </td>
                <td class="text-center align-middle"><strong>Total</strong></td>
                <td class="text-end align-middle">{{number_format($total_tagihan, 0, ',', '.')}}
                </td>


            </tr>
            <tr>
                <td class="text-center align-middle"
                    colspan="{{5 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 1 : 0) +
                                                                ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0)}}"></td>
                <td class="text-center align-middle"><strong>PPN</strong></td>
                <td class="text-end align-middle">

                    {{number_format($ppn, 0, ',', '.')}}

                </td>


            </tr>
            <tr>
                <td class="align-middle"
                    colspan="{{5 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 1 : 0) +
                                                                ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0)}}">
                </td>
                <td class="text-center align-middle"><strong>PPh</strong></td>
                <td class="text-end align-middle">

                    {{number_format($pph, 0, ',', '.')}}

                </td>


            </tr>
            <tr>
                <td class="align-middle"
                    colspan="{{5 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 1 : 0) +
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
<link rel="stylesheet" href="{{asset('assets/js/flatpickr/flatpickr.min.css')}}">
@endpush
@push('js')
<script src="{{asset('assets/js/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/js/dt-font.js')}}"></script>
<script src="{{asset('assets/js/dt-pdf.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
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
        document.getElementById('filter_date').onchange = function() {
                document.getElementById('tanggal_filter').required = this.value !== '';
            };
        document.getElementById('tanggal_filter').oninput = function() {
            document.getElementById('filter_date').required = this.value !== '';
        };

        flatpickr("#tanggal_filter", {
            mode: "range",
            dateFormat: "d-m-Y",
        });


    });

</script>
@endpush
