@extends('layouts.app') <!-- Sesuaikan dengan layout Anda -->

@section('content')
@php $heldCashArea = \App\Support\HeldCashArea::name(); @endphp
@php $readOnlyLedger = ($driverLedger ?? false) || \App\Support\HeldCashArea::readOnly(); @endphp
<div class="container-fluid">
    @if ($driverLedger ?? false)
        <h1 class="h3 mb-3">Kas UJ Ditahan {{ $fundLabel }}</h1>
        <div class="card border-success bg-success bg-opacity-10 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="fw-semibold mb-1"><i class="fa fa-credit-card me-2" aria-hidden="true"></i>Total Saldo Semua Periode</div>
                    <div class="h2 fw-bold mb-0">Rp {{ number_format($totalBalance, 0, ',', '.') }}</div>
                </div>
                <div class="text-muted">{{ $activePeriodCount }} periode dengan saldo tertahan</div>
            </div>
        </div>
        <form method="get" class="card shadow-sm rounded-3 mb-4" aria-labelledby="ledger-period-title">
            <div class="card-body p-3 p-md-4">
                <h2 id="ledger-period-title" class="h6 fw-bold mb-3"><i class="fa fa-calendar-o me-2 text-success" aria-hidden="true"></i>Periode Mutasi</h2>
                <div class="row g-3 align-items-end">
                    <div class="col-7 col-md-4 col-lg-3">
                        <label for="bulan" class="form-label">Bulan</label>
                        <select id="bulan" name="bulan" class="form-select" aria-describedby="held-balance-legend">
                            @foreach (range(1, 12) as $month)
                                <option value="{{ $month }}" @selected($master->bulan === $month)>{{ now()->startOfMonth()->month($month)->translatedFormat('F') }}{{ in_array($month, $activeBalanceMonths, true) ? ' ❗' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-5 col-md-3 col-lg-2">
                        <label for="tahun" class="form-label">Tahun</label>
                        <input id="tahun" name="tahun" class="form-control" type="number" min="2000" max="2100" value="{{ $master->tahun }}">
                    </div>
                    <div class="col-12 col-md-auto d-grid">
                        <button type="submit" class="btn btn-success"><i class="fa fa-filter me-2" aria-hidden="true"></i>Tampilkan periode</button>
                    </div>
                </div>
                <div id="held-balance-legend" class="small text-danger mt-3">&#10071; Ada saldo tertahan pada tahun yang ditampilkan</div>
                @if ($errors->any())<p class="text-danger mt-2 mb-0" role="alert">{{ $errors->first() }}</p>@endif
            </div>
        </form>
    @endif
    <!-- SweetAlert Session Handler dari layout/include -->
    @include('billing.form-maintenance.filter-oli.feedback')

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
                @if ($driverLedger ?? false)
                    <a href="{{ route('home') }}" class="btn btn-sm btn-secondary">Kembali ke Dashboard</a>
                @elseif(request('from') == 'rekap')
                    <a href="{{ route('rekap.uj-ditahan') }}" class="btn btn-sm btn-secondary shadow-sm">
                        <i class="fa fa-arrow-left"></i> Kembali ke Rekap
                    </a>
                @else
                    <a href="{{ route($heldCashArea.'.uj-ditahan', ['bulan' => $master->bulan, 'tahun' => $master->tahun]) }}" class="btn btn-sm btn-secondary shadow-sm">
                        <i class="fa fa-arrow-left"></i> Kembali ke UJ Ditahan Sparepart
                    </a>
                @endif

                <!-- Tombol pencairan dengan Bootstrap 5 attributes -->
                @if(! $readOnlyLedger && $master->saldo > 0)
                <button type="button" class="btn btn-sm btn-success shadow-sm ml-2" data-bs-toggle="modal" data-bs-target="#modalCairkan">
                    <i class="fa fa-money-bill-wave me-1"></i> Cairkan Saldo
                </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table @if ($driverLedger ?? false) id="note-inbox-table" @endif class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="table-success">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th width="12%">Tanggal</th>
                            <th width="23%">Uraian</th>
                            <th width="15%">Info Rekening</th>
                            <th class="text-center" width="15%">Bukti Dokumen</th>
                            <th class="text-right text-success" width="15%">Masuk (+)</th>
                            <th class="text-right text-danger" width="15%">Keluar (-)</th>@unless ($readOnlyLedger)<th>Aksi</th>@endunless
                        </tr>
                    </thead>
                    <tbody>
                        @include('billing.uj-ditahan.ledger-rows')
                    </tbody>
                    <tfoot class="table-light font-weight-bold">
                        <tr>
                            <!-- Colspan disesuaikan dari 4 menjadi 5 karena ada kolom baru -->
                            <td colspan="5" class="text-right">Total Kumulatif:</td>
                            <td class="text-right text-success">Rp {{ number_format($master->total_masuk, 0, ',', '.') }}</td>
                            <td class="text-right text-danger">Rp {{ number_format($master->total_keluar, 0, ',', '.') }}</td>@unless ($readOnlyLedger)<td></td>@endunless
                        </tr>
                    </tfoot>
                </table>
                @if ($driverLedger ?? false)<div id="note-inbox-loading" role="status" class="text-center py-2" data-next="{{ $details->nextPageUrl() }}"></div>@endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Pencairan Saldo (Dikunci dengan static backdrop) -->
@if(! $readOnlyLedger && $master->saldo > 0)
<div class="modal fade" id="modalCairkan" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modalCairkanLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="formPencairan" action="{{ route('billing.uj-ditahan.cairkan') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalCairkanLabel">Form Pencairan Saldo UJ Ditahan Sparepart</h5>
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

                    <!-- Input PDF -->
                    <div class="form-group mb-3">
                        <label for="bukti_pdf" class="font-weight-bold">Dokumen Bukti (PDF, Maks. 5MB)</label>
                        <input type="file" class="form-control" id="bukti_pdf" name="bukti_pdf" accept="application/pdf" required>
                        <small class="text-muted">Harus berupa file berformat .pdf</small>
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
@include('billing.uj-ditahan.payout-cancellation')
@endsection

@unless ($driverLedger ?? false)
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
                // Pastikan HTML5 file validation (wajib diisi & format file) terlewati dulu
                if (!this.checkValidity()) {
                    return;
                }

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
                        // 1. Kunci tombol submit agar tidak bisa diklik dua kali
                        const submitBtn = formPencairan.querySelector('button[type="submit"]');
                        if(submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Memproses...';
                        }

                        // 2. Munculkan SweetAlert Loading State & Kunci Layar
                        Swal.fire({
                            title: 'Memproses Transaksi...',
                            html: 'Mohon tunggu sebentar, sedang mengirim pesan WhatsApp dan memproses data. <b>Jangan tutup atau refresh halaman ini.</b>',
                            allowOutsideClick: false, // User tidak bisa klik diluar modal
                            allowEscapeKey: false,    // User tidak bisa tekan ESC
                            showConfirmButton: false, // Sembunyikan tombol OK
                            didOpen: () => {
                                Swal.showLoading();   // Tampilkan animasi loading
                            }
                        });

                        // 3. Eksekusi form
                        formPencairan.submit();
                    }
                });
            });
        }
    });
</script>
@endpush

@endunless

@if ($driverLedger ?? false)
@push('js')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/incremental-data-table.js') }}"></script>
<script src="{{ asset('assets/js/load-note-inbox.js') }}"></script>
@endpush
@endif
