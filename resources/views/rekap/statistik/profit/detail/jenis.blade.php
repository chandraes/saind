@extends('layouts.app')

@section('content')
<div class="container pb-5">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <h6 class="text-uppercase text-secondary fw-bold mb-1">Statistik Profit Bulanan (Bersih)</h6>
            <h1 class="display-5 fw-bold text-dark">{{$stringJenis}}</h1>
            <p class="lead text-muted">{{$nama_bulan}} {{$tahun}}</p>
            <div class="d-flex justify-content-center">
                <hr class="border-primary border-2 opacity-100" style="width: 60px;">
            </div>
        </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{route('statisik.index')}}">Statistik</a></li>
                    <li class="breadcrumb-item active">Detail Profit</li>
                </ol>
            </nav>
            <a href="{{route('statistik.profit-tahunan-bersih')}}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <img src="{{asset('images/back.svg')}}" width="18" class="me-1"> Kembali
            </a>
        </div>
    </div>

    @include('swal')

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="text-muted small fw-bold text-uppercase mb-2">Total Masuk (98%)</div>
                    <h3 class="text-success fw-bold mb-0">Rp {{$totalTagihan}}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="text-muted small fw-bold text-uppercase mb-2">Total Keluar (Vendor)</div>
                    <h3 class="text-danger fw-bold mb-0">Rp {{$totalBayar}}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-primary text-white">
                <div class="card-body p-4">
                    <div class="text-white-50 small fw-bold text-uppercase mb-2">Net Profit (Selisih)</div>
                    <h3 class="fw-bold mb-0">Rp {{$selisih}}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="mb-0 fw-bold"><i class="fa fa-list me-2"></i>Rincian Transaksi</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="rekapTable">
                <thead class="bg-light text-secondary table-success">
                    <tr>
                        <th class="text-center py-3 border-0">TANGGAL</th>
                        <th class="py-3 border-0">URAIAN</th>
                        <th class="text-end py-3 border-0">MASUK</th>
                        <th class="text-end py-3 border-0">KELUAR</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tagihan as $t)
                    <tr>
                        <td class="text-center small text-muted">{{$t->tanggal}}</td>
                        <td>
                            <div class="fw-bold">Tagihan Customer</div>
                            <div class="small text-muted">{{$t->customer?->nama}}</div>
                        </td>
                        <td class="text-end fw-bold text-success">Rp. {{$t->nf_dpp}}</td>
                        <td class="text-end text-muted">-</td>
                    </tr>
                    @endforeach
                    @foreach ($vendor as $v)
                    <tr>
                        <td class="text-center small text-muted">{{$v->tanggal_lunas}}</td>
                        <td>
                            <div class="fw-bold">Pembayaran Vendor</div>
                            <div class="small text-muted">{{$v->vendor?->nama}}</div>
                        </td>
                        <td class="text-end text-muted">-</td>
                        <td class="text-end fw-bold text-danger">Rp {{$v->nf_nominal}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<style>
    body { background-color: #f8f9fa; }
    .breadcrumb-item + .breadcrumb-item::before { content: "›"; font-size: 1.2rem; vertical-align: middle; }
    .table thead th { font-size: 0.75rem; letter-spacing: 1px; }
    #rekapTable_wrapper .dataTables_scrollBody { border: none !important; }
</style>
@endpush

@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    $(document).ready(function(){
        $('#rekapTable').DataTable({
            "searching": false, // Diaktifkan agar lebih profesional jika data banyak
            "paging": false,
            "scrollCollapse": true,
            "scrollY": "500px",
            "info": false,
            "language": {
                "search": "Cari Transaksi:"
            }
        });
    });
</script>
@endpush
