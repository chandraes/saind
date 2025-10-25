@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Statistik Perform Unit Tahun {{$tahun}}</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-8">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>

                    {{-- <td><a href="{{route('statisik.index')}}"><img src="{{asset('images/statistik.svg')}}" alt="dokumen"
                                width="30"> STATISTIK</a></td>
                    <td>
                        <form target="_blank" action="{{route('statistik.perform-unit-tahunan.print')}}" method="get">
                            <input type="hidden" name="tahun" value="{{$tahun}}">
                            <button class="btn" type="submit">
                                <img src="{{asset('images/document.svg')}}" alt="dokumen" width="30"> Print Perform Unit
                            </button>
                        </form>
                    </td> --}}
                </tr>
            </table>
        </div>
    </div>
    <div class="container-fluid mt-5">
        <form action="{{url()->current()}}" method="get">
            <div class="row">
                {{-- <div class="col-md-3 mb-3">
                    <label for="bulan" class="form-label">Bulan</label>
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
                </div> --}}
                <div class="col-md-3 mb-3">
                    <label for="tahun" class="form-label">Tahun</label>
                    <select class="form-select" name="tahun" id="tahun">
                        @foreach ($dataTahun as $d)
                        <option value="{{$d->tahun}}" {{$d->tahun == $tahun ? 'selected' : ''}}>{{$d->tahun}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="tahun" class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary form-control" id="btn-cari">Tampilkan</button>
                </div>
            </div>
        </form>
    </div>
    <div style="font-size: 11px" class="table-responsive">
        <table class="table table-bordered table-hover table-responsive" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th rowspan="2" class="text-center align-middle">NOLAM</th>
                    @for ($month = 1; $month <= 12; $month++) <th colspan="2" class="text-center align-middle">{{
                        date('F', mktime(0, 0, 0, $month, 10)) }}</th>
                        @endfor
                        <th colspan="2" class="text-center align-middle">Total</th>
                </tr>
                <tr>
                    @for ($month = 1; $month <= 12; $month++) <th class="text-center align-middle">
                        <strong>Rute Panjang</strong>
                        </th>
                        <th class="text-center align-middle">
                            <strong>Rute Pendek</strong>
                        </th>
                        @endfor
                        <th class="text-center align-middle">Rute Panjang</th>
                        <th class="text-center align-middle">Rute Pendek</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($statistics as $statistic)
                <tr>
                    <td class="text-center align-middle">{{ $statistic['vehicle']->nomor_lambung }}</td>
                    @php
                    $totalLongRoute = 0;
                    $totalShortRoute = 0;
                    @endphp
                    @foreach ($statistic['monthly'] as $month)
                    @php
                    $totalLongRoute += $month['long_route_count'];
                    $totalShortRoute += $month['short_route_count'];
                    @endphp
                    <td
                        class="text-center align-middle @if ($statistic['vehicle']->status == 'nonaktif') table-danger @endif">
                        @if ($month['long_route_count'] == 0)
                        @php
                        $month['long_route_count'] = '';
                        @endphp
                        @endif
                        {{ $month['long_route_count'] }}
                    </td>
                    <td class="text-center align-middle" @if ($statistic['vehicle']->status == 'nonaktif')
                        style="background-color: red" @endif>
                        @if ($month['short_route_count'] == 0)
                        @php
                        $month['short_route_count'] = '';
                        @endphp
                        @endif
                        {{ $month['short_route_count'] }}
                    </td>
                    @endforeach
                    <td class="text-center align-middle">{{ $totalLongRoute }}</td>
                    <td class="text-center align-middle">{{ $totalShortRoute }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')

<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    $(document).ready(function(){
            $('#nominal_transaksi').maskMoney({
                thousands: '.',
                decimal: ',',
                precision: 0
            });
            $('#rekapTable').DataTable({
                // "searching": false,
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
                    $('#spinner').show();
                    this.submit();
                }
            })
        });
</script>
@endpush
