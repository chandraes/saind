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
                        <i class="fa fa-cloud-lightning me-2"></i> Dashboard
                    </a>
                    <a href="{{ route('billing.index') }}" class="btn btn-outline-info">
                        <i class="fa fa-database me-2"></i> BILLING
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- TAMBAHAN: Informasi Total Saldo Bulan Ini -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Total Saldo Ditahan ({{ \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F') }} {{ $tahun }})
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($totalSaldoBulanIni, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
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
            <!-- Form Filter (Diperbaiki alignment-nya agar sejajar) -->
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
                                <!-- Menampilkan tanda seru jika bulan & tahun ini memiliki saldo -->
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
                    <thead class="table-dark">
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
                            <td class="align-middle font-weight-bold">{{ $item->vehicle->nomor_lambung ?? '-' }}</td>
                            <td class="align-middle">{{ $item->vehicle->driver->nama ?? '-' }}</td>
                            <td class="text-right align-middle">Rp {{ number_format($item->total_masuk, 0, ',', '.') }}</td>
                            <td class="text-right align-middle">Rp {{ number_format($item->total_keluar, 0, ',', '.') }}</td>

                            <!-- Highlight merah tebal jika saldo masih ada -->
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
