@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold text-uppercase" style="letter-spacing: 2px;">
                <i class="fa fa-truck text-primary me-2"></i> Rekapitulasi Uang Jalan Ditahan
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

    <!-- Informasi Rekapitulasi (Total Masuk, Keluar, Saldo) -->
    <div class="row mb-4">

        <!-- CARD 1: TOTAL MASUK (Warna Hijau) -->
        <div class="col-xl-4 col-md-12 mb-3 mb-xl-0">
            <div class="card border-0 shadow-lg text-white position-relative overflow-hidden h-100"
                 style="background: linear-gradient(135deg, #198754 0%, #0f5132 100%); border-radius: 18px;">
                <div class="position-absolute" style="top: -20px; right: -20px; width: 130px; height: 130px; background: rgba(255, 255, 255, 0.12); border-radius: 50%; pointer-events: none;"></div>
                <div class="position-absolute" style="bottom: -35px; right: 40px; width: 150px; height: 150px; background: rgba(255, 255, 255, 0.06); border-radius: 50%; pointer-events: none;"></div>

                <div class="card-body p-4 position-relative" style="z-index: 1;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="badge bg-white text-success px-3 py-2 rounded-pill font-weight-bold shadow-sm mb-2 d-inline-block" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                <i class="fa fa-calendar me-1"></i> PERIODE {{ strtoupper(\Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F')) }} {{ $tahun }}
                            </span>
                            <div class="text-white-50 text-uppercase fw-semibold mb-1" style="font-size: 0.85rem; letter-spacing: 0.8px;">
                                Total Masuk
                            </div>
                            <h3 class="fw-bold mb-0 text-white" style="letter-spacing: -0.5px;">
                                Rp {{ number_format($totalMasukBulanIni ?? 0, 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="d-flex align-items-center justify-content-center rounded-circle shadow-sm"
                             style="width: 60px; height: 60px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(6px); border: 1px solid rgba(255, 255, 255, 0.3);">
                            <i class="fa fa-arrow-down fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 2: TOTAL KELUAR (Warna Oranye) -->
        <div class="col-xl-4 col-md-12 mb-3 mb-xl-0">
            <div class="card border-0 shadow-lg text-white position-relative overflow-hidden h-100"
                 style="background: linear-gradient(135deg, #fd7e14 0%, #d35400 100%); border-radius: 18px;">
                <div class="position-absolute" style="top: -20px; right: -20px; width: 130px; height: 130px; background: rgba(255, 255, 255, 0.12); border-radius: 50%; pointer-events: none;"></div>
                <div class="position-absolute" style="bottom: -35px; right: 40px; width: 150px; height: 150px; background: rgba(255, 255, 255, 0.06); border-radius: 50%; pointer-events: none;"></div>

                <div class="card-body p-4 position-relative" style="z-index: 1;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="badge bg-white text-warning px-3 py-2 rounded-pill font-weight-bold shadow-sm mb-2 d-inline-block" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                <i class="fa fa-calendar me-1"></i> PERIODE {{ strtoupper(\Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F')) }} {{ $tahun }}
                            </span>
                            <div class="text-white-50 text-uppercase fw-semibold mb-1" style="font-size: 0.85rem; letter-spacing: 0.8px;">
                                Total Keluar
                            </div>
                            <h3 class="fw-bold mb-0 text-white" style="letter-spacing: -0.5px;">
                                Rp {{ number_format($totalKeluarBulanIni ?? 0, 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="d-flex align-items-center justify-content-center rounded-circle shadow-sm"
                             style="width: 60px; height: 60px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(6px); border: 1px solid rgba(255, 255, 255, 0.3);">
                            <i class="fa fa-arrow-up fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 3: TOTAL SALDO DITAHAN (Warna Merah) -->
        <div class="col-xl-4 col-md-12">
            <div class="card border-0 shadow-lg text-white position-relative overflow-hidden h-100"
                 style="background: linear-gradient(135deg, #dc3545 0%, #851419 100%); border-radius: 18px;">
                <div class="position-absolute" style="top: -20px; right: -20px; width: 130px; height: 130px; background: rgba(255, 255, 255, 0.12); border-radius: 50%; pointer-events: none;"></div>
                <div class="position-absolute" style="bottom: -35px; right: 40px; width: 150px; height: 150px; background: rgba(255, 255, 255, 0.06); border-radius: 50%; pointer-events: none;"></div>

                <div class="card-body p-4 position-relative" style="z-index: 1;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="badge bg-white text-danger px-3 py-2 rounded-pill font-weight-bold shadow-sm mb-2 d-inline-block" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                <i class="fa fa-calendar me-1"></i> PERIODE {{ strtoupper(\Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F')) }} {{ $tahun }}
                            </span>
                            <div class="text-white-50 text-uppercase fw-semibold mb-1" style="font-size: 0.85rem; letter-spacing: 0.8px;">
                                Total Saldo Ditahan
                            </div>
                            <h3 class="fw-bold mb-0 text-white" style="letter-spacing: -0.5px;">
                                Rp {{ number_format($totalSaldoBulanIni ?? 0, 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="d-flex align-items-center justify-content-center rounded-circle shadow-sm"
                             style="width: 60px; height: 60px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(6px); border: 1px solid rgba(255, 255, 255, 0.3);">
                            <i class="fa fa-bank fa-2x text-white"></i>
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
                            <td class="text-right align-middle text-danger font-weight-bold">
                                Rp {{ number_format($item->saldo, 0, ',', '.') }}
                            </td>
                            <td class="text-center align-middle">
                                <a href="{{ route('billing.uj-ditahan.show', $item->id) }}" class="btn btn-sm btn-info text-white mb-1">
                                    <i class="fa fa-list"></i> Detail
                                </a>
                                <!-- Tombol Form Cutoff -->
                                @if(auth()->check() && in_array(auth()->user()->role, ['su', 'admin']))
                                <!-- Tombol Form Cutoff -->
                                <form action="{{ route('billing.uj-ditahan.cutoff', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="button" class="btn btn-sm btn-warning text-dark mb-1 btn-cutoff"
                                            data-nomor="{{ $item->vehicle?->nomor_lambung ?? '-' }}"
                                            data-saldo="{{ number_format($item->saldo, 0, ',', '.') }}">
                                        <i class="fa fa-cut me-1"></i> Cutoff
                                    </button>
                                </form>
                            @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">Tidak ada pencatatan Uang Jalan Ditahan (dengan saldo aktif) pada periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cutoffButtons = document.querySelectorAll('.btn-cutoff');

        cutoffButtons.forEach(button => {
            button.addEventListener('click', function () {
                const form = this.closest('form');
                const nomorLambung = this.getAttribute('data-nomor');
                const saldo = this.getAttribute('data-saldo');

                Swal.fire({
                    title: 'Eksekusi Cutoff?',
                    html: 'Anda akan melakukan cutoff untuk kendaraan <b class="text-primary">' + nomorLambung + '</b>.<br><br>Seluruh sisa saldo sebesar <b class="text-danger">Rp ' + saldo + '</b> akan dikeluarkan, dicatat sebagai "Cutoff", dan saldo akan menjadi 0.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fa fa-check me-1"></i> Ya, Eksekusi Cutoff!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endpush
