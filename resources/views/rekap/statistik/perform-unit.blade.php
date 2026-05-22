@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">Statistik Perform Unit</h1>
            <p class="text-muted mb-0">Periode: <span class="fw-semibold">{{
                    \Carbon\Carbon::parse($start_date)->translatedFormat('d M Y') }}</span> s/d <span
                    class="fw-semibold">{{ \Carbon\Carbon::parse($end_date)->translatedFormat('d M Y') }}</span></p>
        </div>

        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="{{route('home')}}" class="btn btn-outline-secondary">
                <i class="fa fa-tachometer-alt me-1"></i> Dashboard
            </a>
            @if (!in_array(auth()->user()->role, ['vendor', 'operasional', 'asisten-user', 'vendor-operational']))
            <a href="{{route('statisik.index')}}" class="btn btn-outline-secondary">
                <i class="fa fa-chart-bar me-1"></i> STATISTIK
            </a>
            @endif


            <form id="printForm" target="_blank" action="{{route('statistik.perform-unit.print')}}" method="get"
                class="d-inline">
                <input type="hidden" name="start_date" value="{{$start_date}}">
                <input type="hidden" name="end_date" value="{{$end_date}}">
                <input type="hidden" name="vendor" id="printVendorId" value="{{$vendor}}">
                <button class="btn btn-danger" type="submit">
                    <i class="fa fa-print me-1"></i> Print Perform Unit
                </button>
            </form>
        </div>
    </div>

    @include('swal')

    <div class="row row-cols-1 row-cols-md-3 row-cols-xl-5 g-3 mb-4">
        <div class="col">
            <div class="card border-0 shadow-sm border-start border-danger border-4 h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1"
                                style="font-size: 11px;">Total Kendaraan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 fw-bold">{{
                                number_format($jumlah_vehicle, 0, ',', '.') }} Unit</div>
                        </div>
                        <div class="col-auto text-muted"><i class="fa fa-truck fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm border-start border-primary border-4 h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1"
                                style="font-size: 11px;">GT Rute Panjang</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 fw-bold">{{
                                number_format($grand_total_long_route, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto text-muted"><i class="fa fa-route fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm border-start border-warning border-4 h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1"
                                style="font-size: 11px;">GT Rute Pendek</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 fw-bold">{{
                                number_format($grand_total_short_route, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto text-muted"><i class="fa fa-map-marked-alt fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm border-start border-success border-4 h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1"
                                style="font-size: 11px;">Grand Total Tonase</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 fw-bold">{{
                                number_format($grand_total_tonase, 2, ',', '.') }} T</div>
                        </div>
                        <div class="col-auto text-muted"><i class="fa fa-weight-hanging fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm border-start border-info border-4 h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1"
                                style="font-size: 11px;">Persentase Utilisasi</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 fw-bold">{{
                                number_format($persentase_utilisasi, 2, ',', '.') }}%</div>
                        </div>
                        <div class="col-auto text-muted"><i class="fa fa-percentage fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{route('statistik.perform-unit')}}" method="get" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label fw-bold small text-muted">Tanggal Mulai</label>
                        <input type="date" class="form-control" name="start_date" id="start_date"
                            value="{{$start_date}}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label fw-bold small text-muted">Tanggal Selesai <span
                                class="text-danger small">(Max 1 Bulan)</span></label>
                        <input type="date" class="form-control" name="end_date" id="end_date" value="{{$end_date}}">
                    </div>
                    @if (!in_array(auth()->user()->role, ['vendor', 'vendor-operational']))
                    <div class="col-md-3">
                        <label for="vendor" class="form-label fw-bold small text-muted">Filter Vendor <span
                                class="text-danger small">*Wajib untuk Print</span></label>
                        <select class="form-select select2-vendor" name="vendor" id="vendor" style="width: 100%;">
                            <option value="">Semua Vendor</option>
                            @foreach ($vendors as $v)
                            <option value="{{$v->id}}" {{$v->id == $vendor ? 'selected' : ''}}>{{$v->nama}}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-md-3">
                        <label class="form-label d-none d-md-block">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa fa-filter me-1"></i> Tampilkan
                            </button>
                            <a href="{{route('statistik.perform-unit')}}" class="btn btn-outline-warning text-nowrap">
                                <i class="fa fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div style="font-size: 11px" class="table-responsive">
                <table class="table table-bordered table-hover mb-0 align-middle" id="rekapTable">
                    <thead class="table-dark">
                        <tr>
                            <th rowspan="2" class="text-center align-middle" style="min-width: 90px;">Tanggal</th>
                            @foreach ($vehicle as $v)
                            <th colspan="2"
                                class="text-center align-middle {{ $v->status == 'nonaktif' ? 'bg-danger text-white' : '' }}">
                                <span class="fw-bold">{{$v->nomor_lambung}}</span> ({{$v->nopol}}) <br>
                                <small>{{strtoupper($v->vendor->nickname)}}
                                    ({{strtoupper($v->vendor->pembayaran)}})</small>
                                @if ($v->gps == 1) <span class="badge bg-primary px-1 py-0">GPS</span> @endif
                                @if($v->vendor->support_operational == 1) <span
                                    class="badge bg-info text-dark px-1 py-0">SO</span> @endif <br>
                                <small class="text-muted text-white-50">Idx {{$v->no_index}} ({{$v->tahun}})</small>
                            </th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($vehicle as $v)
                            <th
                                class="text-center align-middle {{ $v->status == 'nonaktif' ? 'bg-danger text-white-50' : '' }}">
                                Rute</th>
                            <th
                                class="text-center align-middle {{ $v->status == 'nonaktif' ? 'bg-danger text-white-50' : '' }}">
                                Ton</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dates_array as $idx => $dateStr)
                        <tr>
                            <td class="text-center align-middle fw-semibold bg-light">
                                {{ \Carbon\Carbon::parse($dateStr)->translatedFormat('d-M-y') }}
                            </td>
                            @foreach ($statistics as $statistic)
                            @php
                            $dayData = $statistic['data'][$idx];
                            $isNonaktif = $statistic['vehicle']->status == 'nonaktif';
                            @endphp
                            <td
                                class="text-center align-middle text-nowrap {{ $isNonaktif ? 'table-danger text-danger' : '' }}">
                                {!! str_replace(',', '<br>', $dayData['rute']) !!}
                            </td>
                            <td
                                class="text-center align-middle text-nowrap {{ $isNonaktif ? 'table-danger text-danger' : '' }}">
                                {!! str_replace(',', '<br>', $dayData['tonase']) !!}
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light border-top border-secondary">
                        <tr>
                            <td class="text-center align-middle fw-bold">Rute Panjang</td>
                            @foreach ($statistics as $statistic)
                            <td colspan="2"
                                class="text-center align-middle fw-bold text-primary {{ $statistic['vehicle']->status == 'nonaktif' ? 'table-danger' : '' }}">
                                {{ number_format($statistic['long_route_count'], 0, ',', '.') }}
                            </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="text-center align-middle fw-bold">Rute Pendek</td>
                            @foreach ($statistics as $statistic)
                            <td colspan="2"
                                class="text-center align-middle fw-bold text-warning {{ $statistic['vehicle']->status == 'nonaktif' ? 'table-danger' : '' }}">
                                {{ number_format($statistic['short_route_count'], 0, ',', '.') }}
                            </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="text-center align-middle fw-bold">Total Rute</td>
                            @foreach ($statistics as $statistic)
                            <td colspan="2" class="text-center align-middle fw-bold bg-dark text-white">
                                {{ number_format($statistic['short_route_count'] + $statistic['long_route_count'], 0,
                                ',', '.') }}
                            </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="text-center align-middle fw-bold">Total Tonase</td>
                            @foreach ($statistics as $statistic)
                            <td colspan="2"
                                class="text-center align-middle fw-bold text-success {{ $statistic['vehicle']->status == 'nonaktif' ? 'table-danger' : '' }}">
                                {{ number_format($statistic['total_tonase'], 2, ',', '.') }}
                            </td>
                            @endforeach
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    #rekapTable thead th {
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 0.375rem !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 12px !important;
        color: #212529 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
</style>
@endpush

@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function(){

        let userRole = "{{auth()->user()->role}}";

        $('.select2-vendor').select2({
            placeholder: "Pilih Vendor",
            allowClear: true
        });

        $('#rekapTable').DataTable({
            "searching": false, "responsive": false, "paging": false, "ordering": false,
            "scrollCollapse": true, "scrollY": "500px", "scrollX": true,
        });

        function handleDateLimits() {
            let startVal = $('#start_date').val();
            if (startVal) {
                let startDate = new Date(startVal);
                let maxDate = new Date(startDate);
                maxDate.setDate(startDate.getDate() + 31);

                let yyyyMax = maxDate.getFullYear();
                let mmMax = String(maxDate.getMonth() + 1).padStart(2, '0');
                let ddMax = String(maxDate.getDate()).padStart(2, '0');

                $('#end_date').attr('min', startVal);
                $('#end_date').attr('max', `${yyyyMax}-${mmMax}-${ddMax}`);
            }
        }

        $('#start_date').on('change', function() {
            handleDateLimits();
            let startVal = $(this).val();
            let endVal = $('#end_date').val();
            if (endVal < startVal || (new Date(endVal) - new Date(startVal)) > (31 * 24 * 60 * 60 * 1000)) {
                $('#end_date').val(startVal);
            }
        });

        handleDateLimits();

        // VALIDASI JAVASCRIPT: Cegah Print Jika Vendor Kosong
        $('#printForm').on('submit', function(e) {
            // Ambil value vendor dari select2 filter utama
            let currentVendor = $('#vendor').val();
            let currentStartDate = $('#start_date').val();
            let currentEndDate = $('#end_date').val();

            //if user is not vendor or vendor-operational, then vendor filter must be filled before print

            if (userRole !== 'vendor' && userRole !== 'vendor-operational' && !currentVendor) {

                e.preventDefault(); // Batalkan submit form
                Swal.fire({
                    icon: 'warning',
                    title: 'Akses Ditolak',
                    text: 'Anda wajib memilih salah satu Vendor pada filter sebelum dapat mencetak dokumen PDF!',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }

            // Jika lolos validasi, sinkronkan nilai form print dengan filter terbaru sebelum meluncur
            $('#printVendorId').val(currentVendor);
            $(this).find('input[name="start_date"]').val(currentStartDate);
            $(this).find('input[name="end_date"]').val(currentEndDate);
        });
    });
</script>
@endpush
