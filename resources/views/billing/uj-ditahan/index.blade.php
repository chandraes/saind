@extends('layouts.app') <!-- Sesuaikan dengan layout Anda -->

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold text-uppercase" style="letter-spacing: 2px;">
                <i class="fas fa-truck-moving text-primary me-2"></i> Rekapitulasi Uang Jalan Ditahan
            </h2>
            <hr class="w-25 mx-auto text-muted">
        </div>
    </div>

    @include('swal')

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex flex-wrap gap-3 justify-content-center justify-content-md-start">
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-cloud me-2"></i> Dashboard
                    </a>
                    <a href="{{ route('billing.index') }}" class="btn btn-outline-info">
                        <i class="fa fa-database me-2"></i> BILLING
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Total Saldo Bulan Ini (Desain Modern Gradient & Glassmorphism) -->
    <div class="row mb-4">
        <div class="col-xl-5 col-md-7 col-12">
            <div class="card border-0 shadow-lg text-white position-relative overflow-hidden"
                 style="background: linear-gradient(135deg, #dc3545 0%, #851419 100%); border-radius: 18px;">

                <!-- Ornamen Lingkaran Transparan Latar Belakang -->
                <div class="position-absolute" style="top: -20px; right: -20px; width: 130px; height: 130px; background: rgba(255, 255, 255, 0.12); border-radius: 50%; pointer-events: none;"></div>
                <div class="position-absolute" style="bottom: -35px; right: 40px; width: 150px; height: 150px; background: rgba(255, 255, 255, 0.06); border-radius: 50%; pointer-events: none;"></div>

                <div class="card-body p-4 position-relative" style="z-index: 1;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <!-- Badge Periode Terpilih -->
                            <span class="badge bg-white text-danger px-3 py-2 rounded-pill font-weight-bold shadow-sm mb-2 d-inline-block" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                <i class="fas fa-calendar-alt me-1"></i> PERIODE {{ strtoupper(\Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F')) }} {{ $tahun }}
                            </span>

                            <!-- Label Judul Card -->
                            <div class="text-white-50 text-uppercase fw-semibold mb-1" style="font-size: 0.85rem; letter-spacing: 0.8px;">
                                Total Saldo Ditahan
                            </div>

                            <!-- Angka Total Saldo -->
                            <h2 class="fw-bold mb-0 text-white" style="font-size: 2.1rem; letter-spacing: -0.5px;">
                                Rp {{ number_format($totalSaldoBulanIni, 0, ',', '.') }}
                            </h2>
                        </div>

                        <!-- Icon dengan Lingkaran Kaca (Glassmorphism) -->
                        <div class="d-flex align-items-center justify-content-center rounded-circle shadow-sm"
                             style="width: 68px; height: 68px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(6px); border: 1px solid rgba(255, 255, 255, 0.3);">
                            <i class="fas fa-wallet fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Data Detail Uang Jalan Ditahan</h6>
        </div>
        <div class="card-body">
            <!-- Form Filter -->
            <form action="{{ route('billing.uj-ditahan') }}" method="GET" class="mb-4">
                <div class="row align-items-start">
                    <div class="col-md-3 mb-3">
                        <label for="bulan" class="form-label font-weight-bold">Filter Bulan</label>
                        <select name="bulan" id="bulan" class="form-select form-control" onchange="this.form.submit()">
                            @php
                                $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            @endphp

                            @foreach($namaBulan as $index => $nama)
                                @php
                                    $numBulan = $index + 1;
                                    $checkKey = $numBulan . '-' . $tahun;
                                    $hasSaldo = in_array($checkKey, $activeBalances);
                                @endphp
                                <option value="{{ $numBulan }}" {{ $bulan == $numBulan ? 'selected' : '' }}>
                                    {{ $nama }} {!! $hasSaldo ? '&#10071;' : '' !!}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-danger mt-2 d-inline-block fw-bold">&#10071; <i>Ada saldo tertahan</i></small>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="tahun" class="form-label font-weight-bold">Filter Tahun</label>
                        <select name="tahun" id="tahun" class="form-select form-control" onchange="this.form.submit()">
                            @foreach($listTahun as $thn)
                                <option value="{{ $thn }}" {{ $tahun == $thn ? 'selected' : '' }}>{{ $thn }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>

            <hr>

            <!-- Table List UJ Ditahan -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-success">
                        <tr>
                            <th class="text-center">No</th>
                            <th>Nomor Lambung</th>
                            <th>Driver Utama</th>
                            <th class="text-right">Total Masuk</th>
                            <th class="text-right">Total Keluar</th>
                            <th class="text-right">Sisa Saldo</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                        <tr>
                            <td class="text-center align-middle">{{ $loop->iteration }}</td>
                            <td class="align-middle font-weight-bold">{{ $item->vehicle?->nomor_lambung ?? '-' }}</td>
                            <td class="align-middle">{{ $item->vehicle->driver?->nama ?? '-' }}</td>
                            <td class="text-right align-middle">Rp {{ number_format($item->total_masuk, 0, ',', '.') }}</td>
                            <td class="text-right align-middle">Rp {{ number_format($item->total_keluar, 0, ',', '.') }}</td>

                            <td class="text-right align-middle {{ $item->saldo > 0 ? 'text-danger font-weight-bold' : 'text-success' }}">
                                Rp {{ number_format($item->saldo, 0, ',', '.') }}
                            </td>
                            <td class="text-center align-middle">
                                <a href="{{ route('billing.uj-ditahan.show', $item->id) }}" class="btn btn-sm btn-info text-white">
                                    <i class="fa fa-list"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">Tidak ada pencatatan Uang Jalan Ditahan pada periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
