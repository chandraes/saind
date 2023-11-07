@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Statistik Perform Vendor</u></h1>
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
                        <form target="_blank" action="{{route('statistik.perform-vendor.print')}}" method="get">
                            <input type="hidden" name="bulan" value="{{$bulan_angka}}">
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
            <form action="{{route('statistik.perform-vendor')}}" method="get">
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
    </div>
    <div style="font-size: 11px" class="table-responsive">
        <table class="table table-bordered table-hover table-responsive" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle" style="width: 8%">Tanggal</th>
                    @foreach ($statistics as $s => $key)
                    <th class="text-center align-middle">
                        {{$s}}
                    </th>
                    @endforeach
                </tr>

            </thead>
            <tbody>
                @for ($i = 1; $i <= $date; $i++) <tr>
                    <td class="text-center align-middle">{{$i}}</td>
                    @foreach ($statistics as $s => $statistic)
                    <td class="text-center align-middle">
                        @if ($statistic['sisa'][$i] != '-')
                        {{number_format($statistic['sisa'][$i], 0, ',', '.')}}
                        @else
                        -
                        @endif
                    </td>
                    @endforeach
                    </tr>
                    @endfor
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-center align-middle">Sisa Terakhir Bulan {{$nama_bulan}} {{$tahun}}</th>
                    @foreach ($statistics as $s => $statistic)
                    <th class="text-center align-middle">
                        @for ($j = $date; $j >= 1; $j--)
                        @if ($statistic['sisa'][$j] != '-')
                        {{number_format($statistic['sisa'][$j], 0, ',', '.')}}
                        @break
                        @endif
                        @endfor
                    </th>
                    @endforeach
                </tr>
                <tr>
                    <th class="text-center align-middle">
                        Total Kas Vendor
                    </th>
                    <th colspan="{{count($statistics)}}">
                        Rp. {{number_format($grand_total, 0, ',', '.')}}
                    </th>
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

</script>
@endpush
