@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Statistik Perform Unit</u></h1>
            <h1>{{$nama_bulan}} {{$tahun}}</h1>
        </div>
    </div>
    @include('swal')
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('rekap.index')}}"><img src="{{asset('images/rekap.svg')}}" alt="dokumen"
                                width="30"> REKAP</a></td>
                    <td><a href="{{route('statisik.index')}}"><img src="{{asset('images/statistik.svg')}}" alt="dokumen"
                                    width="30"> STATISTIK</a></td>
                    <td>
                        <form target="_blank" action="{{route('statistik.perform-unit.print')}}" method="get">
                            <input type="hidden" name="offset" value="{{$offset}}">
                            <input type="hidden" name="bulan" value="{{$bulan_angka}}">
                            @if ($vendor != 0)
                            <input type="hidden" name="vendor" value="{{$vendor}}">
                            @endif
                            <input type="hidden" name="tahun" value="{{$tahun}}">
                            <button class="btn" type="submit">
                                <img src="{{asset('images/document.svg')}}" alt="dokumen" width="30"> Print Perform Unit
                            </button>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <form action="{{route('statistik.perform-unit')}}" method="get">
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
                            <option value="{{$d->tahun}}" {{$d->tahun == $tahun ? 'selected' : ''}}>{{$d->tahun}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 my-3">
                        <button type="submit" class="btn btn-primary form-control" id="btn-cari">Tampilkan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <form action="{{route('statistik.perform-unit')}}" method="get">
                <label for="" class="form-label">Filter Vendor</label>
                <div class="input-group">
                    <input type="hidden" name="offset" value="{{$offset}}">
                    <input type="hidden" name="bulan" value="{{$bulan_angka}}">
                    <input type="hidden" name="tahun" value="{{$tahun}}">
                    <select class="form-select" name="vendor" id="vendor" required>
                        <option value="">Pilih Vendor</option>
                        @foreach ($vendors as $v)
                        <option value="{{$v->id}}" {{$v->id == $vendor ? 'selected' : ''}}>{{$v->nama}}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-success" type="submit" id="">Tampilkan</button>
                    <a href="{{route('statistik.perform-unit')}}" class="btn btn-outline-warning" type="button" id="">Reset Filter</a>
                </div>
            </form>

        </div>
        <div class="col-6 my-3">
            <div class="btn-group float-end" role="group" aria-label="Button group name">
                @if ($offset > 0)
                <form action="{{route('statistik.perform-unit')}}" method="get">
                    <input type="hidden" name="offset" value="{{$offset-10}}">
                    <input type="hidden" name="bulan" value="{{$bulan_angka}}">
                    @if ($vendor != 0)
                    <input type="hidden" name="vendor" value="{{$vendor}}">
                    @endif
                    <input type="hidden" name="tahun" value="{{$tahun}}">
                    <button type="submit" class="btn btn-primary m-3"><i class="fa fa-arrow-left"></i>
                        Sebelumnya</button>
                </form>
                @endif
                @if ($vehicle->count() > 0)
                <form action="{{route('statistik.perform-unit')}}" method="get">
                    <input type="hidden" name="offset" value="{{$offset+10}}">
                    <input type="hidden" name="bulan" value="{{$bulan_angka}}">
                    @if ($vendor != 0)
                    <input type="hidden" name="vendor" value="{{$vendor}}">
                    @endif
                    <input type="hidden" name="tahun" value="{{$tahun}}">
                    <button type="submit" class="btn btn-success m-3">Selanjutnya
                        <i class="fa fa-arrow-right"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <div style="font-size: 11px" class="table-responsive">
        <table class="table table-bordered table-hover table-responsive" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th rowspan="2" class="text-center align-middle">Tanggal</th>
                    @foreach ($vehicle as $v)
                    <th colspan="2" class="text-center align-middle" @if ($v->status == 'nonaktif')
                        style="background-color: red" @endif>{{$v->nomor_lambung}} <br>
                        {{$v->vendor->nama}} <br> @if ($v->gps == 1) <strong>(GPS)</strong> @endif
                        @if($v->vendor->support_operational == 1)
                        <strong>(SO)</strong>
                        @endif
                    </th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($vehicle as $v)
                    <th class="text-center align-middle" @if ($v->status == 'nonaktif')
                        style="background-color: red"
                        @endif>
                        <strong>Rute</strong>
                    </th>
                    <th class="text-center align-middle" @if ($v->status == 'nonaktif')
                        style="background-color: red"
                        @endif>
                        <strong>Ton</strong>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @for ($i = 1; $i <= $date; $i++) <tr>
                    <td class="text-center align-middle" style="width: 3%">{{$i}}</td>
                    @foreach ($statistics as $statistic)
                    @foreach ($statistic['data'] as $data)
                    @if ($data['day'] == $i)
                    <td class="text-center align-middle" @if ($statistic['vehicle']->status == 'nonaktif')
                        style="background-color: red" @endif>
                        {{$data['rute']}}
                    </td>
                    <td class="text-center align-middle" @if ($statistic['vehicle']->status == 'nonaktif')
                        style="background-color: red" @endif>
                        {{$data['tonase']}}
                    </td>
                    @endif
                    @endforeach
                    @endforeach
                    </tr>
                    @endfor
            </tbody>
            <tfoot>
                <tr>
                    <td class="text-center align-middle">
                        <strong>Rute Panjang</strong>
                    </td>
                    @foreach ($statistics as $statistic)
                        <td colspan="2" class="text-center align-middle" @if ($statistic['vehicle']->status == 'nonaktif') style="background-color: red" @endif>
                            <strong>{{ number_format($statistic['long_route_count'], 0, ',', '.') }}</strong>
                        </td>
                    @endforeach
                </tr>
                <tr>
                    <td class="text-center align-middle">
                        <strong>Rute Pendek</strong>
                    </td>
                    @foreach ($statistics as $statistic)
                        <td colspan="2" class="text-center align-middle" @if ($statistic['vehicle']->status == 'nonaktif') style="background-color: red" @endif>
                            <strong>{{ number_format($statistic['short_route_count'], 0, ',', '.') }}</strong>
                        </td>
                    @endforeach
                </tr>
                <tr>
                    <td class="text-center align-middle">
                        <strong>Total</strong>
                    </td>
                    @foreach ($statistics as $statistic)
                        <td colspan="2" class="text-center align-middle" @if ($statistic['vehicle']->status == 'nonaktif') style="background-color: red" @endif>
                            <strong>{{ number_format($statistic['short_route_count']+$statistic['long_route_count'], 0, ',', '.') }}</strong>
                        </td>
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
{{-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script> --}}
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    $(document).ready(function(){
            $('#nominal_transaksi').maskMoney({
                thousands: '.',
                decimal: ',',
                precision: 0
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
        // masukForm on submit, sweetalert confirm
        $('#lanjutForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah data sudah benar?',
                text: "Pastikan data sudah benar sebelum disimpan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
                }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            })
        });
</script>
@endpush
