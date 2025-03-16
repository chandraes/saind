@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>STATISTIK UPAH GENDONG</u></h1>
            {{-- <h1>{{$nama_bulan}} {{$tahun}}</h1> --}}
        </div>
    </div>
    @include('swal')
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <form action="{{route('vendor-operational.per-vendor.upah-gendong')}}" method="get">
                <input type="hidden" name="vehicle_id" value="{{$vehicle}}">
                <div class="row">
                    <div class="col-md-4 my-3">
                        <select class="form-select" name="bulan" id="bulan">
                            <option value="1" {{$bulan=='01' ? 'selected' : '' }}>Januari</option>
                            <option value="2" {{$bulan=='02' ? 'selected' : '' }}>Februari</option>
                            <option value="3" {{$bulan=='03' ? 'selected' : '' }}>Maret</option>
                            <option value="4" {{$bulan=='04' ? 'selected' : '' }}>April</option>
                            <option value="5" {{$bulan=='05' ? 'selected' : '' }}>Mei</option>
                            <option value="6" {{$bulan=='06' ? 'selected' : '' }}>Juni</option>
                            <option value="7" {{$bulan=='07' ? 'selected' : '' }}>Juli</option>
                            <option value="8" {{$bulan=='08' ? 'selected' : '' }}>Agustus</option>
                            <option value="9" {{$bulan=='09' ? 'selected' : '' }}>September</option>
                            <option value="10" {{$bulan=='10' ? 'selected' : '' }}>Oktober</option>
                            <option value="11" {{$bulan=='11' ? 'selected' : '' }}>November</option>
                            <option value="12" {{$bulan=='12' ? 'selected' : '' }}>Desember</option>
                        </select>
                    </div>
                    <div class="col-md-4 my-3">
                        <select class="form-select" name="tahun" id="tahun">
                            @foreach ($dataTahun as $d)
                            <option value="{{$d->tahun}}" {{$d->tahun == $tahun ? 'selected' : ''}}>{{$d->tahun}}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 my-3">
                        <button type="submit" class="btn btn-primary form-control" id="btn-cari">Tampilkan</button>
                    </div>
                </div>
            </form>
        </div>

        <form action="{{route('vendor-operational.per-vendor.upah-gendong')}}" method="get">
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <input type="hidden" name="vehicle_id" value="{{$vehicle}}">
                        <label for="start_date" class="form-label">Range Tanggal</label>
                        <input type="text" class="form-control" name="tanggal_filter" id="tanggal_filter"
                            value="{{$tanggal_filter}}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="" class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary form-control">Filter Range Tanggal</button>
                    </div>
                </div>
            </div>
        </form>

    </div>
    <div class="row mt-2">
        <div class="col-md-6 d-flex justify-content-start">
            <table>
                <tr>
                    <td>
                        <h5>Nomor Lambung</h5>
                    </td>
                    <td style="padding-left:10px;padding-right:10px">
                        <h5>:</h5>
                    </td>
                    <td>
                        <h5>SAI{{$ug->vehicle->nomor_lambung}}</h5>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5>Nama Driver</h5>
                    </td>
                    <td style="padding-left:10px;padding-right:10px">
                        <h5>:</h5>
                    </td>
                    <td>
                        <h5>{{$ug->nama_driver}}</h5>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5>Pengurus</h5>
                    </td>
                    <td style="padding-left:10px;padding-right:10px">
                        <h5>:</h5>
                    </td>
                    <td>
                        <h5>{{$ug->nama_pengurus}}</h5>
                    </td>
                </tr>
            </table>
        </div>
        <div class="col-md-6 d-flex justify-content-end">
            <table>
                <tr>
                    <td>
                        <h5>Nama Rekening</h5>
                    </td>
                    <td style="padding-left:10px;padding-right:10px">
                        <h5>:</h5>
                    </td>
                    <td>
                        <h5>{{$ug->nama_rek}}</h5>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5>Nomor Rekening</h5>
                    </td>
                    <td style="padding-left:10px;padding-right:10px">
                        <h5>:</h5>
                    </td>
                    <td>
                        <h5>{{$ug->no_rek}}</h5>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5>Bank</h5>
                    </td>
                    <td style="padding-left:10px;padding-right:10px">
                        <h5>:</h5>
                    </td>
                    <td>
                        <h5>{{$ug->bank}}</h5>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-responsive" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">Hari</th>
                    <th class="text-center align-middle">Tanggal</th>
                    <th class="text-center align-middle">Rute</th>
                    <th class="text-center align-middle">KM</th>
                    <th class="text-center align-middle">Tonase < 50 KM</th>
                    <th class="text-center align-middle">Tonase > 50 KM</th>
                    <th class="text-center align-middle">Kelebihan Tonase</th>
                    <th class="text-center align-middle">Total Upah Gendong</th>
                </tr>
            </thead>
            <tbody>
                @php
                $total = 0;
                $total_under_50 = 0;
                $total_over_50 = 0;
                $total_kelebihan_tonase = 0;
                @endphp
                @foreach ($data as $d)
                @php
                $kelebihan_tonase = 0;
                $upah_gendong = 0;
                @endphp
                <tr>
                    <td class="text-center">{{$d->kas_uang_jalan->hari}}</td>
                    <td class="text-center">{{$d->kas_uang_jalan->id_tanggal}}</td>
                    <td class="text-center">{{$d->kas_uang_jalan->rute->nama}}</td>
                    <td class="text-center">{{$d->kas_uang_jalan->rute->jarak}}</td>
                    <td class="text-center">
                        @if ($d->kas_uang_jalan->rute->jarak < 50) {{$d->timbangan_bongkar}}
                            @php
                            $total_under_50 += $d->timbangan_bongkar;
                            @endphp
                            @else
                            -
                            @endif
                    </td>
                    <td class="text-center">
                        @if ($d->kas_uang_jalan->rute->jarak > 50)
                        {{$d->timbangan_bongkar}}
                        @php
                        $total_over_50 += $d->timbangan_bongkar;
                        $kelebihan_tonase = $d->timbangan_bongkar - $ug->tonase_min;
                        @endphp

                        @else
                        -
                        @endif
                    </td>
                    <td class="text-center">

                        {{$kelebihan_tonase}}
                        @php $upah_gendong=$kelebihan_tonase * $ug->nominal;
                            $total += $upah_gendong;
                            $total_kelebihan_tonase += $kelebihan_tonase;
                            @endphp


                    </td>
                    <td class="text-end align-middle">

                        {{number_format($upah_gendong, 0, ',', '.')}}

                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-center align-middle" colspan="3">Grand Total</th>
                    <th class="text-center align-middle">{{$data->sum('jarak')}}</th>
                    <th class="text-center align-middle">{{$total_under_50}}</th>
                    <th class="text-center align-middle">{{$total_over_50}}</th>
                    <th class="text-center align-middle">{{$total_kelebihan_tonase}}</th>
                    <th class="text-end align-middle">{{number_format($total, 0, ',','.')}}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="{{asset('assets/js/flatpickr/flatpickr.min.css')}}">
@endpush
@push('js')
{{-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script> --}}
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="{{asset('assets/js/flatpickr/flatpickr.js')}}"></script>
<script>
    $(document).ready(function(){
            $('#nominal_transaksi').maskMoney({
                thousands: '.',
                decimal: ',',
                precision: 0
            });
            flatpickr("#tanggal_filter", {
                mode: "range",
                dateFormat: "d-m-Y",
            });
            $('#rekapTable').DataTable({
                "searching": false,
                "responsive": true,
                "paging": false,
                "ordering": false,
                "scrollCollapse": true,
                "scrollY": "550px",
            });
        });
</script>
@endpush
