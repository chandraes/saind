@extends('layouts.app') <!-- Sesuaikan dengan layout Anda -->

@section('content')
<div class="container-fluid">
    <!-- Header Card: Info Master -->
    <div class="card shadow mb-4 border-left-info">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        Informasi Kendaraan (Periode: {{ \Carbon\Carbon::create()->month($master->bulan)->translatedFormat('F') }} {{ $master->tahun }})
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ $master->vehicle->nomor_lambung ?? '-' }} - ({{ $master->vehicle->driver->nama ?? 'Tanpa Driver Utama' }})
                    </div>
                </div>
                <div class="col-auto text-right">
                    <div class="text-xs font-weight-bold text-uppercase mb-1 text-muted">Sisa Saldo Berjalan</div>
                    <div class="h3 mb-0 font-weight-bold {{ $master->saldo > 0 ? 'text-danger' : 'text-success' }}">
                        Rp {{ number_format($master->saldo, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Ledger/Histori -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Rincian Transaksi (Ledger)</h6>
            <div>
                <a href="{{ route('billing.uj-ditahan') }}" class="btn btn-sm btn-secondary shadow-sm">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>

                <!-- Placeholder tombol pencairan jika masih ada saldo -->
                @if($master->saldo > 0)
                <button type="button" class="btn btn-sm btn-success shadow-sm ml-2">
                    <i class="fa fa-money-bill-wave"></i> Cairkan Saldo
                </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th width="15%">Tanggal & Waktu</th>
                            <th width="30%">Keterangan</th>
                            <th width="15%">Driver (Saat Trx)</th>
                            <th class="text-right text-success" width="15%">Masuk (+)</th>
                            <th class="text-right text-danger" width="15%">Keluar (-)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($details as $d)
                        <tr>
                            <td class="text-center align-middle">{{ $loop->iteration }}</td>
                            <td class="align-middle">{{ $d->created_at->format('d-m-Y H:i') }}</td>
                            <td class="align-middle">{{ $d->keterangan }}</td>
                            <td class="align-middle">{{ $d->driver->nama ?? '-' }}</td>

                            <!-- Kolom Uang Masuk -->
                            <td class="text-right align-middle font-weight-bold text-success">
                                {{ $d->jenis == 'masuk' ? 'Rp ' . number_format($d->nominal, 0, ',', '.') : '-' }}
                            </td>

                            <!-- Kolom Uang Keluar -->
                            <td class="text-right align-middle font-weight-bold text-danger">
                                {{ $d->jenis == 'keluar' ? 'Rp ' . number_format($d->nominal, 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">Belum ada histori transaksi untuk data ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light font-weight-bold">
                        <tr>
                            <td colspan="4" class="text-right">Total Kumulatif:</td>
                            <td class="text-right text-success">Rp {{ number_format($master->total_masuk, 0, ',', '.') }}</td>
                            <td class="text-right text-danger">Rp {{ number_format($master->total_keluar, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
