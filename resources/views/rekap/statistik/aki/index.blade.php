@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header Page -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Statistik Aki</h3>
            <p class="text-muted mb-0">Pantau kondisi, tanggal pemasangan, dan umur aki pada setiap posisi kendaraan.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                <img src="{{ asset('images/dashboard.svg') }}" width="18" alt="Dashboard"> Dashboard
            </a>
            @if (!in_array(auth()->user()->role, ['asisten-user', 'operasional', 'vendor']))
            <a href="{{ route('rekap.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                <img src="{{ asset('images/rekap.svg') }}" width="18" alt="Rekap"> Rekap
            </a>
            <a href="{{ route('statisik.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                <img src="{{ asset('images/statistik.svg') }}" width="18" alt="Statistik"> Statistik
            </a>
            @endif
        </div>
    </div>

    @include('swal')

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
        <strong class="d-block mb-1">Terjadi kesalahan validasi:</strong>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Card Informasi Kendaraan -->
    <div class="card border-0 shadow-sm mb-4 bg-light">
        <div class="card-body">
            <div class="row align-items-center text-center text-md-start">
                <div class="col-md-4 border-end-md mb-2 mb-md-0">
                    <span class="text-muted fs-7 d-block">Nomor Lambung</span>
                    <span class="fw-bold fs-5 text-primary">{{ $vehicle->nomor_lambung }}</span>
                </div>
                <div class="col-md-4 border-end-md mb-2 mb-md-0">
                    <span class="text-muted fs-7 d-block">Nama Driver</span>
                    <span class="fw-bold fs-6">{{ $vehicle->nama_driver ?? '-' }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted fs-7 d-block">Pengurus</span>
                    <span class="fw-bold fs-6">{{ $vehicle->pengurus ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><span class="text-muted small">Posisi aki</span><div class="fs-3 fw-bold">{{ $aki->count() }}</div></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><span class="text-muted small">Posisi terpasang</span><div class="fs-3 fw-bold text-primary">{{ $aki->filter(fn ($item) => $item->akiLog !== null)->count() }}</div></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><span class="text-muted small">Kondisi ≤ 40%</span><div class="fs-3 fw-bold text-danger">{{ $aki->filter(fn ($item) => $item->akiLog !== null && $item->akiLog['kondisi'] <= 40)->count() }}</div></div></div></div>
    </div>
    <!-- Tabel Data Aki -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white p-3"><h5 class="fw-bold mb-1">Aki saat ini</h5><p class="text-muted small mb-0">Umur dihitung dari tanggal ganti sampai hari ini. Data tanpa riwayat ditampilkan sebagai tanda pisah.</p></div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="rekapTable">
                    <thead class="table-success">
                        <tr>
                            <th class="text-center" style="width: 50px;">NO</th>
                            <th>POSISI AKI</th>
                            <th class="text-center">MEREK</th>
                            <th class="text-center">KONDISI</th>
                            <th class="text-center">TANGGAL GANTI</th>
                            <th class="text-center">UMUR AKI</th>
                            <th class="text-center">RIWAYAT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($aki as $d)
                        <tr>
                            <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                            <td class="fw-semibold">
                                <i class="fa fa-car-battery me-1 text-primary"></i>{{ $d->nama }}
                            </td>
                            <td class="text-center text-uppercase">{{ $d->akiLog['merk'] ?? '-' }}</td>
                            <td class="text-center">
                                @if (isset($d->akiLog['kondisi']))
                                    @php
                                        $kondisi = (int)$d->akiLog['kondisi'];
                                        $badgeColor = $kondisi > 70 ? 'bg-success' : ($kondisi > 40 ? 'bg-warning text-dark' : 'bg-danger');
                                    @endphp
                                    <span class="badge {{ $badgeColor }}">{{ $kondisi }}%</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if (isset($d->akiLog['tanggal_ganti']))
                                <a href="{{ route('statistik.aki.histori', ['vehicle' => $vehicle->id, 'posisi' => $d->id]) }}" class="fw-bold px-2 py-1 text-decoration-none">
                                    {{ $d->akiLog['tanggal_ganti'] }}
                                </a>
                                @else
                                -
                                @endif
                            </td>
                            <td class="text-center text-nowrap fw-semibold">{{ isset($d->akiLog['umur_hari']) ? $d->akiLog['umur_hari'].' hari' : '-' }}</td>
                            <td class="text-center"><a href="{{ route('statistik.aki.histori', ['vehicle' => $vehicle->id, 'posisi' => $d->id]) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-history me-1"></i> Lihat histori</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link href="{{ asset('assets/css/dt.min.css') }}" rel="stylesheet">
@endpush

@push('js')
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
<script>
    $(document).ready(function(){
        $('#rekapTable').DataTable({
            "searching": true,
            "info": false,
            "responsive": true,
            "paging": false,
            "ordering": false,
            "scrollCollapse": true,
            "language": { "search": "Cari posisi / merek:", "zeroRecords": "Data aki tidak ditemukan", "emptyTable": "Belum ada data aki" },
        });
    });
</script>
@endpush
