@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Tonase Tambang </u></h1>
            <h1>{{$customer->nama}}</h1>
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
                    <td><a href="{{route('statisik.index')}}"><img src="{{asset('images/statistik.svg')}}" alt="dokumen"
                                width="30"> STATISTIK</a></td>
                    {{-- <td>
                        <form target="_blank" action="{{route('statistik.profit-harian.pdf')}}" method="get">
                            <input type="hidden" name="offset" value="{{$offset}}">
                            <input type="hidden" name="bulan" value="{{$bulan_angka}}">
                            <input type="hidden" name="tahun" value="{{$tahun}}">
                            <button class="btn" type="submit">
                                <img src="{{asset('images/document.svg')}}" alt="dokumen" width="30"> Print Rekap
                            </button>
                        </form>
                    </td> --}}
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <form action="{{route('statistik.tonase-tambang', $customer)}}" method="get">
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

    <div>
        <table class="table table-bordered table-hover" id="rekapTable">
            @php
                $totalMuat = 0;
                $totalBongkar = 0;
                $monthlyTotalMuat = [];
                $monthlyTotalBongkar = [];
            @endphp
            <thead class="table-success">
                <tr>
                    <th rowspan="2" class="text-center align-middle">Tanggal</th>
                    @foreach ($dbRute as $rute)
                        <th colspan="3" class="text-center align-middle">{{ $rute->nama }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($dbRute as $rute)
                        <th class="text-center align-middle">Ritase</th>
                        <th class="text-center align-middle">Tonase Muat</th>
                        <th class="text-center align-middle">Tonase Bongkar</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @for ($i = 1; $i <= $date; $i++)
                    <tr>
                        <td class='text-center align-middle'>{{ sprintf('%02d', $i) . '-' . $bulan . '-' . $tahun }}</td>
                        @foreach ($dbRute as $rute)
                            @php
                                $dayData = $statistics[$i][$rute->id] ?? ['data' => ['ritase' => 0, 'tonase_muat' => 0, 'tonase_bongkar' => 0]];
                                $monthlyTotalRitase[$rute->id] = ($monthlyTotalRitase[$rute->id] ?? 0) + $dayData['data']['ritase'];
                                $monthlyTotalMuat[$rute->id] = ($monthlyTotalMuat[$rute->id] ?? 0) + $dayData['data']['tonase_muat'];
                                $monthlyTotalBongkar[$rute->id] = ($monthlyTotalBongkar[$rute->id] ?? 0) + $dayData['data']['tonase_bongkar'];
                            @endphp
                            <td class='text-center align-middle'>{{ ($dayData['data']['ritase']) }}</td>
                            <td class='text-center align-middle'>{{ ($dayData['data']['tonase_muat']) }}</td>
                            <td class='text-center align-middle'>{{ $dayData['data']['tonase_bongkar'] }}</td>
                        @endforeach
                    </tr>
                @endfor
            </tbody>
            <tfoot>
                <tr>
                    @php
                        $totalBongkar = array_sum($monthlyTotalBongkar);
                    @endphp
                    <th class="text-center align-middle" rowspan="2">Total</th>
                    @foreach ($dbRute as $rute)
                        <th class="text-center align-middle">{{ number_format($monthlyTotalRitase[$rute->id], 2, ',','.') ?? 0 }}</th>
                        <th class="text-center align-middle">{{ number_format($monthlyTotalMuat[$rute->id], 2, ',','.') ?? 0 }}</th>
                        <th class="text-center align-middle">{{ number_format($monthlyTotalBongkar[$rute->id], 2, ',','.') ?? 0 }}</th>
                    @endforeach
                </tr>
                <tr>
                    <th class="text-center align-middle" colspan="{{count($dbRute)*3-1}}">Grand Total</th>
                    <th class="text-center align-middle">{{ number_format($totalBongkar, 2, ',','.')}}</th>
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

            $('#rekapTable').DataTable({
                "searching": false,
                "paging": false,
                'info': false,
                "ordering": false,
                "scrollCollapse": true,
                "scrollY": "400px",
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
