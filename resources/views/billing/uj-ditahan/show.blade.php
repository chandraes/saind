@extends('layouts.app') <!-- Sesuaikan dengan layout Anda -->

@section('content')
<div class="container-fluid">
    <!-- SweetAlert Session Handler dari layout/include -->
    @include('swal')

    <!-- Header Card: Info Master -->
    <div class="card shadow mb-4 border-left-info">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        Informasi Kendaraan (Periode: {{ \Carbon\Carbon::create()->month((int) $master->bulan)->translatedFormat('F') }} {{ $master->tahun }})
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

                <!-- Tombol pencairan dengan Bootstrap 5 attributes -->
                @if($master->saldo > 0)
                <button type="button" class="btn btn-sm btn-success shadow-sm ml-2" data-bs-toggle="modal" data-bs-target="#modalCairkan">
                    <i class="fa fa-money-bill-wave me-1"></i> Cairkan Saldo
                </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="table-success">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th width="15%">Tanggal & Waktu</th>
                            <th width="25%">Keterangan</th>
                            <th width="15%">Info Rekening (Tujuan)</th>
                            <th class="text-right text-success" width="15%">Masuk (+)</th>
                            <th class="text-right text-danger" width="15%">Keluar (-)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($details as $d)
                        <tr>
                            <td class="text-center align-middle">{{ $loop->iteration }}</td>
                            <td class="align-middle">{{ $d->created_at->format('d-m-Y H:i') }}</td>
                            <td class="align-middle">
                                {{ $d->keterangan }}
                                @if($d->driver)
                                    <br><small class="text-muted">Driver: {{ $d->driver->nama }}</small>
                                @endif
                            </td>
                            <td class="align-middle">
                                @if($d->bank && $d->no_rekening)
                                    <strong>{{ $d->bank }}</strong> - {{ $d->no_rekening }}<br>
                                    <small>a.n {{ $d->nama_rekening }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

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

<!-- Modal Pencairan Saldo (Dikunci dengan static backdrop) -->
@if($master->saldo > 0)
<div class="modal fade" id="modalCairkan" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modalCairkanLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="formPencairan" action="{{ route('billing.uj-ditahan.cairkan') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalCairkanLabel">Form Pencairan Saldo UJ Ditahan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="uj_ditahan_id" value="{{ $master->id }}">

                    <div class="alert alert-warning text-dark">
                        Maksimal pencairan: <strong>Rp {{ number_format($master->saldo, 0, ',', '.') }}</strong>
                    </div>

                    <div class="form-group mb-3">
                        <label for="nominal" class="font-weight-bold">Nominal Cair (Rp)</label>
                        <input type="text" class="form-control" id="nominal" name="nominal" required placeholder="Contoh: 1.500.000" autocomplete="off">
                    </div>

                    <div class="form-group mb-3">
                        <label for="keterangan" class="font-weight-bold">Keterangan / Berita</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="2" required placeholder="Contoh: Pencairan UJ mobil 01 bulan Agustus"></textarea>
                    </div>

                    <hr>
                    <h6 class="font-weight-bold mb-3 text-primary">Rekening Tujuan:</h6>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="bank">Bank</label>
                            <input type="text" class="form-control" id="bank" name="bank" required placeholder="Contoh: BCA">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="no_rekening">Nomor Rekening</label>
                            <input type="text" class="form-control" id="no_rekening" name="no_rekening" required placeholder="Contoh: 1234567890">
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="nama_rekening">Nama Rekening Penerima</label>
                        <input type="text" class="form-control" id="nama_rekening" name="nama_rekening" required placeholder="Contoh: Budi Santoso">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Proses Pencairan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
@push('js')
<!-- JavaScript Inisialisasi Cleave.js & SweetAlert -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var inputNominal = document.getElementById('nominal');
        var cleaveNominal = null;
        var saldoMaksimal = {{ $master->saldo }};

        // 1. Inisialisasi Cleave.js untuk format angka Rupiah
        if (inputNominal) {
            cleaveNominal = new Cleave(inputNominal, {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });

            // 2. Real-time validation saat user mengetik
            inputNominal.addEventListener('input', function () {
                var rawValue = cleaveNominal.getRawValue();
                var nominalCair = parseInt(rawValue) || 0;

                if (nominalCair > saldoMaksimal) {
                    // Reset input menjadi 0 secara otomatis
                    cleaveNominal.setRawValue(0);

                    // Tampilkan SweetAlert Warning
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nominal Melebihi Saldo!',
                        html: 'Nominal yang diketik melebihi sisa saldo berjalan (<b>Rp {{ number_format($master->saldo, 0, ",", ".") }}</b>).<br><br>Nominal otomatis direset ke <b>0</b>.',
                        confirmButtonText: 'Paham',
                        customClass: {
                            confirmButton: 'btn btn-warning'
                        }
                    }).then(function() {
                        inputNominal.focus();
                    });
                }
            });
        }

        // 3. Intercept submit form untuk konfirmasi akhir
        var formPencairan = document.getElementById('formPencairan');
        if (formPencairan) {
            formPencairan.addEventListener('submit', function(e) {
                e.preventDefault(); // Tahan pengiriman form otomatis

                var rawValue = cleaveNominal ? cleaveNominal.getRawValue() : inputNominal.value;
                var nominalCair = parseInt(rawValue) || 0;

                // Validasi: Nominal Kosong / <= 0
                if (nominalCair <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nominal Tidak Valid',
                        text: 'Silakan masukkan nominal pencairan yang valid (lebih dari 0).',
                        confirmButtonText: 'Tutup',
                        customClass: {
                            confirmButton: 'btn btn-warning'
                        }
                    });
                    return;
                }

                // Modal Konfirmasi SweetAlert sebelum submit
                Swal.fire({
                    title: 'Konfirmasi Pencairan',
                    html: 'Apakah Anda yakin ingin mencairkan saldo sebesar<br><h4 class="text-success font-weight-bold mt-2">Rp ' + cleaveNominal.getFormattedValue() + '</h4>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fa fa-check me-1"></i> Ya, Proses!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then(function(result) {
                    if (result.isConfirmed) {
                        formPencairan.submit(); // Eksekusi form jika user mengonfirmasi
                    }
                });
            });
        }
    });
</script>
@endpush
