@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Statistik Profit</u></h1>
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
                        <form target="_blank" action="{{route('statistik.profit-bulanan.print')}}" method="get">
                            <input type="hidden" name="offset" value="{{$offset}}">
                            <input type="hidden" name="bulan" value="{{$bulan_angka}}">
                            @if ($vendor != 0)
                            <input type="hidden" name="vendor" value="{{$vendor}}">
                            @endif
                            <input type="hidden" name="tahun" value="{{$tahun}}">
                            <button class="btn" type="submit">
                                <img src="{{asset('images/document.svg')}}" alt="dokumen" width="30"> Print Rekap
                            </button>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <form action="{{route('statisik.profit-bulanan')}}" method="get">
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
            <form action="{{route('statisik.profit-bulanan')}}" method="get">
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
                    <a href="{{route('statisik.profit-bulanan')}}" class="btn btn-outline-warning" type="button" id="">Reset Filter</a>
                </div>
            </form>

        </div>
        <div class="col-6 my-3">
            <div class="btn-group float-end" role="group" aria-label="Button group name">
                @if ($offset > 0)
                <form action="{{route('statisik.profit-bulanan')}}" method="get">
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
                <form action="{{route('statisik.profit-bulanan')}}" method="get">
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

    <div style="font-size: 15px">
        <table class="table table-bordered table-hover" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">Tanggal</th>
                    @foreach ($vehicle as $v)
                    <th class="text-center align-middle" @if ($v->status == 'nonaktif')
                        style="background-color: red"
                        @endif>{{$v->nomor_lambung}} <br>
                        {{$v->vendor->nickname}} @if ($v->gps == 1) <strong>(GPS)</strong> @endif @if($v->vendor->support_operational == 1) <strong>(SO)</strong> @endif
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @for ($i = 1; $i <= $date; $i++) <tr>
                    <td class="text-center align-middle" style="width: 8%">{{$i}}</td>
                    @foreach ($vehicle as $v)
                    <td class="text-center align-middle" @if ($v->status == 'nonaktif')
                        style="background-color: red"
                        @endif>
                        @php
                        $profit = $data->where('nomor_lambung', $v->nomor_lambung)->where('tanggal', date('Y-m-d',
                        strtotime($i.'-'.$bulan_angka.'-'.$tahun)))->sum('profit') ?? 0;
                        @endphp
                        {{number_format($profit, 0, ',', '.')}}
                    </td>
                    @endforeach
                    </tr>
                    @endfor

            </tbody>
            <tfoot>
                <tr>
                    <td class="text-center align-middle">
                        <strong>Total</strong>
                    </td>
                    @foreach ($vehicle as $v)
                    @php
                    $total = $data->where('nomor_lambung', $v->nomor_lambung)->sum('profit') ;
                    @endphp
                    <td class="text-center align-middle" @if ($v->status == 'nonaktif')
                        style="background-color: red"
                        @endif>
                        <strong>{{number_format($total, 0, ',', '.')}}</strong>
                    </td>
                    @endforeach
                </tr>
                <tr>
                    <td><strong>Grand Total</strong></td>
                    <td colspan="{{$vehicle->count()}}">
                        <strong>Rp. {{$data ? number_format($data->sum('profit'), 0, ',', '.') : 0}}</strong>
                    </td>
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
                    $('#spinner').show();
                    this.submit();
                }
            })
        });
</script>
@endpush
