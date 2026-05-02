@extends('layouts.app')

@section('content')
<div class="container pb-5">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <h6 class="text-uppercase text-secondary fw-bold mb-1">Statistik Profit Achievement</h6>
            <h2 class="fw-bold text-dark">{{$nama_bulan}} {{$tahun}}</h2>
            <div class="d-flex justify-content-center">
                <hr class="border-success border-2 opacity-100" style="width: 60px;">
            </div>
        </div>
    </div>

    <!-- Navigation & Filter Section -->
    <div class="row mb-4 align-items-center">
        <!-- Breadcrumb -->
        <div class="col-md-5">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{route('statisik.index')}}">Statistik</a></li>
                    <li class="breadcrumb-item active">Achievement</li>
                </ol>
            </nav>
        </div>

        <!-- Filter Form -->
        <div class="col-md-7">
            <form action="{{route('statistik.achievement')}}" method="get" class="d-flex justify-content-md-end gap-2">
                <select class="form-select w-auto" name="bulan" id="bulan">
                    <!-- Perbaikan Bug Dropdown Bulan -->
                    @foreach ($arrayBulan as $key => $bul)
                    <option value="{{ $key }}" {{ $key == $bulan ? 'selected' : '' }}>{{ $bul }}</option>
                    @endforeach
                </select>
                <select class="form-select w-auto" name="tahun" id="tahun">
                    @foreach ($dataTahun as $d)
                    <option value="{{$d->tahun}}" {{$d->tahun == $tahun ? 'selected' : ''}}>{{$d->tahun}}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary px-4 shadow-sm" id="btn-cari">
                    <i class="fa fa-search me-1"></i> Tampilkan
                </button>
            </form>
        </div>
    </div>

    @include('swal')

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="text-muted small fw-bold text-uppercase mb-2">Total Masuk (98%)</div>
                    <h3 class="text-success fw-bold mb-0">Rp {{number_format($totalTagihan, 0, ',', '.')}}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="text-muted small fw-bold text-uppercase mb-2">Total Keluar (Vendor)</div>
                    <h3 class="text-danger fw-bold mb-0">Rp {{number_format($totalBayar, 0, ',', '.')}}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-primary text-white">
                <div class="card-body p-4">
                    <div class="text-white-50 small fw-bold text-uppercase mb-2">Net Profit (Selisih)</div>
                    <h3 class="fw-bold mb-0">Rp {{number_format($selisih, 0, ',', '.')}}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="rekapTable">
                <thead class="table-success">
                    <tr>
                        <th class="text-center py-3 border-0">TANGGAL</th>
                        <th class="py-3 border-0">URAIAN</th>
                        <th class="text-end py-3 border-0">MASUK (DR)</th>
                        <th class="text-end py-3 border-0">KELUAR (CR)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tagihan as $t)
                    <tr>
                        <!-- Gunakan updated_at atau tanggal aktual di database -->
                        <td class="text-center small text-muted">{{$t->updated_at->format('Y-m-d')}}</td>
                        <td>
                            <div class="fw-bold text-uppercase" style="font-size: 0.85rem;">Tagihan Customer</div>
                            <div class="small text-muted">{{$t->customer?->nama}}</div>
                        </td>
                        <td class="text-end fw-bold text-success">
                            <!-- Menampilkan nominal 98% agar sinkron dengan kalkulasi footer -->
                            Rp {{number_format($t->nominal * 0.98, 0, ',', '.')}}
                        </td>
                        <td class="text-end text-muted">-</td>
                    </tr>
                    @endforeach

                    @foreach ($vendor as $v)
                    <tr>
                        <td class="text-center small text-muted">{{$v->updated_at->format('Y-m-d')}}</td>
                        <td>
                            <div class="fw-bold text-uppercase" style="font-size: 0.85rem;">Pembayaran Vendor</div>
                            <div class="small text-muted">{{$v->vendor?->nama}}</div>
                        </td>
                        <td class="text-end text-muted">-</td>
                        <td class="text-end fw-bold text-danger">
                            Rp {{number_format($v->nominal, 0, ',', '.')}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <!-- Tfoot untuk perhitungan selisih awam -->
                <tfoot class="bg-light fw-bold">
                    <tr>
                        <td colspan="2" class="text-end py-3">TOTAL MASUK (DITERIMA 98%)</td>
                        <td class="text-end text-success py-3">Rp {{number_format($totalTagihan, 0, ',', '.')}}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-end py-3">TOTAL KELUAR (VENDOR)</td>
                        <td></td>
                        <td class="text-end text-danger py-3">Rp {{number_format($totalBayar, 0, ',', '.')}}</td>
                    </tr>
                    <tr class="table-secondary">
                        <td colspan="2" class="text-end py-3 text-uppercase">Selisih</td>
                        <td colspan="2" class="text-end py-3 fs-5 {{ $selisih >= 0 ? 'text-primary' : 'text-danger' }}">
                            Rp {{number_format($selisih, 0, ',', '.')}}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<style>
    body { background-color: #f8fafc; }
    .card { border-radius: 1rem; }
    .table thead th {
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
    .table tfoot td { border-top: 2px solid #dee2e6; }
    .breadcrumb-item + .breadcrumb-item::before { content: "›"; }
</style>
@endpush

@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    $(document).ready(function(){
        $('#rekapTable').DataTable({
            "searching": true,
            "paging": false,
            "info": false,
            "scrollCollapse": true,
            "scrollY": "550px",
            "ordering": false, // Pertahankan urutan input asli agar tfoot tidak pindah
            "language": {
                "search": "Cari Transaksi:"
            }
        });
    });
</script>
@endpush
