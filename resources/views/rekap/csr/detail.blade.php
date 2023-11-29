@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-13 text-center">
            <h1><u>NOTA LUNAS CSR</u></h1>
            <h1>{{$periode}}</h1>
            <h1>{{$customer->singkatan}}</h1>
        </div>
    </div>
    @php
        $total_tagihan = $data ? $data->sum('nominal_csr') : 0;
    @endphp
    @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('rekap.index')}}"><img src="{{asset('images/rekap.svg')}}"
                                alt="dokumen" width="30"> Rekap</a></td>
                    <td><a href="{{route('rekap.csr')}}"><img src="{{asset('images/rekap-csr.svg')}}"
                                    alt="dokumen" width="30"> Nota Csr</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container-fluid mt-5 table-responsive ">
    <table class="table table-bordered table-hover" id="notaTable">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">Tanggal UJ</th>
                <th class="text-center align-middle">Kode</th>
                <th class="text-center align-middle">Nomor Lambung</th>
                <th class="text-center align-middle">Vendor</th>
                <th class="text-center align-middle">Rute</th>
                <th class="text-center align-middle">Jarak</th>
                <th class="text-center align-middle">Harga</th>
                <th class="text-center align-middle">Tanggal Muat</th>
                <th class="text-center align-middle">Nota Muat</th>
                <th class="text-center align-middle">Tonase Muat</th>
                <th class="text-center align-middle">Tanggal Bongkar</th>
                <th class="text-center align-middle">Nota Bongkar</th>
                <th class="text-center align-middle">Tonase Bongkar</th>
                <th class="text-center align-middle">Tagihan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->tanggal}}</td>
                <td class="align-middle">

                    <div class="modal fade" id="uj{{$d->id}}" tabindex="-1" data-bs-backdrop="static"
                        data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg"
                            role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTitleId">Nota Tagihan
                                        {{$d->kas_uang_jalan->vehicle->nomor_lambung}}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-4 mb-3">
                                                <label for="tanggal_muat" class="form-label">Kode</label>
                                                <input type="text" class="form-control" name="tanggal_muat"
                                                    id="tanggal_muat" placeholder="" value="UJ{{sprintf(" %02d",
                                                    $d->kas_uang_jalan->nomor_uang_jalan)}}" readonly>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label for="tanggal_muat" class="form-label">Tanggal</label>
                                                <input type="text" class="form-control" name="tanggal_uang_jalan"
                                                    id="tanggal_muat" placeholder=""
                                                    value="{{$d->kas_uang_jalan->tanggal}}" readonly>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label for="no_lambung" class="form-label">Nomor Lambung</label>
                                                <input type="text" class="form-control" name="no_lambung"
                                                    id="no_lambung" placeholder=""
                                                    value="{{$d->kas_uang_jalan->vehicle->nomor_lambung}}" readonly>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label for="vendor" class="form-label">Vendor</label>
                                                <input type="text" class="form-control" name="vendor" id="vendor"
                                                    placeholder="" value="{{$d->kas_uang_jalan->vendor->nickname}}"
                                                    readonly>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label for="tambang" class="form-label">Tambang</label>
                                                <input type="text" class="form-control" name="tambang" id="tambang"
                                                    placeholder="" value="{{$d->kas_uang_jalan->customer->singkatan}}"
                                                    readonly>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label for="rute" class="form-label">Rute</label>
                                                <input type="text" class="form-control" name="rute" id="rute"
                                                    placeholder="" value="{{$d->kas_uang_jalan->rute->nama}}" readonly>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-4 mb-3">
                                                <label for="nota_muat" class="form-label">Nota Muat</label>
                                                <input type="text" class="form-control" name="nota_muat" id="nota_muat"
                                                    placeholder="" value="{{$d->nota_muat}}" readonly>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label for="tonase" class="form-label">Timbangan Muat</label>
                                                <input type="text" class="form-control" name="tonase" id="tonase"
                                                    placeholder="" value="{{$d->tonase}}" readonly>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label for="tonase" class="form-label">Tanggal Muat</label>
                                                <input type="text" class="form-control" name="tonase" id="tonase"
                                                    placeholder="" value="{{$d->id_tanggal_muat}}" readonly>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-4 mb-3">
                                                <label for="nota_bongkar" class="form-label">Nota Bongkar</label>
                                                <input type="text" class="form-control" name="nota_bongkar"
                                                    id="nota_bongkar" placeholder=""
                                                    value="{{$d->nota_bongkar ? $d->nota_bongkar : ''}}"
                                                    {{$d->nota_bongkar ? 'readonly' : ''}} readonly>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label for="timbangan_bongkar" class="form-label">Timbangan
                                                    Bongkar</label>
                                                <input type="text" class="form-control" name="timbangan_bongkar"
                                                    id="timbangan_bongkar" placeholder=""
                                                    value="{{$d->timbangan_bongkar ? $d->timbangan_bongkar : ''}}"
                                                    {{$d->timbangan_bongkar ? 'readonly' : ''}} readonly>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label for="tonase" class="form-label">Tanggal Bongkar</label>
                                                <input type="text" class="form-control" name="tonase" id="tonase"
                                                    placeholder="" value="{{date('d M Y')}}" readonly>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#uj{{$d->id}}"> <strong>UJ{{sprintf("%02d",
                                $d->kas_uang_jalan->nomor_uang_jalan)}}</strong></a>
                    </div>
                </td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->vehicle->nomor_lambung}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->vendor->nickname}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->rute->nama}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->rute->jarak}}</td>
                <td class="text-center align-middle">
                    {{number_format(($d->harga_csr), 0, ',', '.')}}
                </td>
                <td class="text-center align-middle">{{$d->id_tanggal_muat}}</td>
                <td class="text-center align-middle">{{$d->nota_muat}}</td>
                <td class="text-center align-middle">{{$d->tonase}}</td>
                <td class="text-center align-middle">{{$d->id_tanggal_bongkar}}</td>
                <td class="text-center align-middle">{{$d->nota_bongkar}}</td>
                <td class="text-center align-middle">{{$d->timbangan_bongkar}}</td>
                <td class="text-center align-middle">
                    {{number_format(($d->nominal_csr), 0, ',', '.')}}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr align="right">
                <td class="text-center align-middle"
                    colspan="13"></td>
                <td class="text-center align-middle"><strong>Total</strong></td>
                <td align="right" class="align-middle">{{number_format($total_tagihan, 0, ',', '.')}}
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
<script src="{{asset('assets/plugins/date-picker/date-picker.js')}}"></script>
<script src="{{asset('assets/js/dt-font.js')}}"></script>
<script src="{{asset('assets/js/dt-pdf.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    // hide alert after 5 seconds


    $(document).ready(function() {
        var table = $('#notaTable').DataTable({
            "paging": false,
            "ordering": false,
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
