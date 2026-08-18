@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold text-uppercase" style="letter-spacing: 2px;">
                <i class="fa fa-archive text-secondary me-2"></i> Rekapitulasi Data Cutoff (Saldo 0)
            </h2>
            <hr class="w-25 mx-auto text-muted">
        </div>
    </div>

       <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex flex-wrap gap-3 justify-content-center justify-content-md-start">
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-cloud me-2"></i> Dashboard
                    </a>
                    <a href="{{ route('rekap.index') }}" class="btn btn-outline-info">
                        <i class="fa fa-database me-2"></i> REKAP
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Tambahkan Blok Card Total Ini -->
    <div class="row mb-4">
        <!-- CARD TOTAL MASUK -->
        <div class="col-md-6 mb-3 mb-md-0">
            <div class="card border-0 shadow text-white h-100" style="background-color: #6c757d; border-radius: 12px;">
                <div class="card-body p-4">
                    <div class="text-white-50 text-uppercase fw-semibold mb-1" style="font-size: 0.85rem; letter-spacing: 0.8px;">
                        Total Pemasukan (Cutoff)
                    </div>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($totalMasuk ?? 0, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>

        <!-- CARD TOTAL KELUAR -->
        <div class="col-md-6">
            <div class="card border-0 shadow text-white h-100" style="background-color: #495057; border-radius: 12px;">
                <div class="card-body p-4">
                    <div class="text-white-50 text-uppercase fw-semibold mb-1" style="font-size: 0.85rem; letter-spacing: 0.8px;">
                        Total Pengeluaran (Cutoff)
                    </div>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($totalKeluar ?? 0, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-secondary">Histori Kendaraan Terselesaikan (Cutoff)</h6>
        </div>
        <div class="card-body">
            <!-- Form Filter -->
            <form action="{{ route('rekap.uj-ditahan') }}" method="GET" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-3">
                        <label for="bulan" class="form-label font-weight-bold">Bulan Cutoff</label>
                        <select name="bulan" id="bulan" class="form-select form-control">
                            @php
                                $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            @endphp
                            @foreach($namaBulan as $index => $nama)
                                <option value="{{ $index + 1 }}" {{ $bulan == ($index + 1) ? 'selected' : '' }}>
                                    {{ $nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="tahun" class="form-label font-weight-bold">Tahun Cutoff</label>
                        <select name="tahun" id="tahun" class="form-select form-control">
                            @foreach($listTahun as $thn)
                                <option value="{{ $thn }}" {{ $tahun == $thn ? 'selected' : '' }}>{{ $thn }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <button type="submit" class="btn btn-secondary w-100"><i class="fa fa-search me-1"></i> Filter Data</button>
                    </div>
                </div>
            </form>

            <hr>

            <!-- Table List Cutoff -->
            <div class="table-responsive">
                <!-- Tidak menggunakan ID dataTable bawaan jika ingin mengandalkan Pagination Laravel murni agar tidak berat di browser -->
                <table class="table table-bordered table-striped table-hover" width="100%" cellspacing="0">
                    <thead class="table-secondary">
                        <tr>
                            <th class="text-center">No</th>
                            <th>Nomor Lambung</th>
                            <th>Driver Utama</th>
                            <th class="text-right">Total Masuk</th>
                            <th class="text-right">Total Keluar</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                        <tr>
                            <!-- Penomoran otomatis menyesuaikan halaman pagination -->
                            <td class="text-center align-middle">{{ $data->firstItem() + $loop->index }}</td>
                            <td class="align-middle font-weight-bold">{{ $item->vehicle?->nomor_lambung ?? '-' }}</td>
                            <td class="align-middle">{{ $item->vehicle->driver?->nama ?? '-' }}</td>
                            <td class="text-right align-middle">Rp {{ number_format($item->total_masuk, 0, ',', '.') }}</td>
                            <td class="text-right align-middle">Rp {{ number_format($item->total_keluar, 0, ',', '.') }}</td>
                            <td class="text-center align-middle">
                                <span class="badge bg-success">Selesai / Cutoff</span>
                            </td>
                            <td class="text-center align-middle">
                                <a href="{{ route('billing.uj-ditahan.show', ['id' => $item->id, 'from' => 'rekap']) }}" class="btn btn-sm btn-info text-white">
                                    <i class="fa fa-list"></i> Histori
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">Tidak ada data histori cutoff pada periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Link Pagination Laravel -->
            <div class="d-flex justify-content-end mt-3">
                {{ $data->appends(request()->query())->links() }}
            </div>

        </div>
    </div>
</div>
@endsection
