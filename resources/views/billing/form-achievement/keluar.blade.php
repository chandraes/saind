@extends('layouts.app')

@section('content')
<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('billing.index')}}">Billing</a></li>
                    <li class="breadcrumb-item active">Form Achievement Keluar</li>
                </ol>
            </nav>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-danger py-3">
                    <h5 class="text-white mb-0 fw-bold">
                        <i class="fa fa-arrow-circle-down me-2"></i>FORM ACHIEVEMENT KELUAR
                    </h5>
                </div>
                <div class="card-body p-4">
                    @include('swal')

                    <form action="{{route('billing.form-achievement.keluar.store')}}" method="post" id="masukForm">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-muted small">TANGGAL</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fa fa-calendar text-secondary"></i></span>
                                    <input type="text" class="form-control bg-light border-0" name="tanggal" id="tanggal" value="{{date('d M Y')}}" readonly>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <label for="uraian" class="form-label fw-bold text-muted small">URAIAN</label>
                                <input type="text" class="form-control @error('uraian') is-invalid @enderror"
                                    name="uraian" id="uraian" placeholder="Contoh: Pembayaran Bonus" required maxlength="20" value="{{ old('uraian') }}">
                                @error('uraian') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12">
                                <label for="nominal_transaksi" class="form-label fw-bold text-muted small">NOMINAL KELUAR</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-danger text-white border-0">Rp</span>
                                    <input type="text" class="form-control form-control-lg fw-bold text-danger @error('nominal_transaksi') is-invalid @enderror"
                                        name="nominal_transaksi" id="nominal_transaksi" required value="{{ old('nominal_transaksi') }}">
                                </div>
                                @error('nominal_transaksi') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12 mt-4">
                                <div class="p-3 rounded-3 bg-light border border-danger border-opacity-25">
                                    <h6 class="fw-bold mb-3 small text-uppercase text-danger"><i class="fa fa-university me-1"></i>Informasi Rekening Tujuan (Penerima)</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="bank" class="form-label small text-muted">Nama Bank</label>
                                            <input type="text" class="form-control @error('bank') is-invalid @enderror" name="bank" id="bank" required value="{{ old('bank') }}" placeholder="BCA / Mandiri">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="transfer_ke" class="form-label small text-muted">Nama Penerima</label>
                                            <input type="text" class="form-control @error('transfer_ke') is-invalid @enderror" name="transfer_ke" id="transfer_ke" required value="{{ old('transfer_ke') }}" placeholder="A.n. Rekening">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="no_rekening" class="form-label small text-muted">Nomor Rekening</label>
                                            <input type="text" class="form-control @error('no_rekening') is-invalid @enderror" name="no_rekening" id="no_rekening" required value="{{ old('no_rekening') }}" placeholder="123456789">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4 pt-3">
                            <button class="btn btn-danger btn-lg fw-bold shadow-sm" type="submit">
                                <i class="fa fa-paper-plane me-2"></i>KIRIM DANA
                            </button>
                            <a href="{{route('billing.index')}}" class="btn btn-link text-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(function() {
        // Form Masking
        new Cleave('#nominal_transaksi', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            numeralDecimalMark: ',',
            delimiter: '.'
        });

        // Form Submit Confirmation
        $('#masukForm').on('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Kirim Dana Keluar?',
                text: "Pastikan nominal dan rekening tujuan sudah benar. Saldo Kas Besar akan dikurangi.",
                icon: 'warning', // Gunakan icon warning untuk pengeluaran
                showCancelButton: true,
                confirmButtonColor: '#dc3545', // Warna merah sesuai btn-danger
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Kirim!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Sedang memproses...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    this.submit();
                }
            });
        });
    });
</script>
@endpush
