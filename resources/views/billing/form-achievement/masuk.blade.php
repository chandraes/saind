@extends('layouts.app')

@section('content')
<div class="container-fluid pb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('billing.index')}}">Billing</a></li>
                    <li class="breadcrumb-item active">Form Achievement Masuk</li>
                </ol>
            </nav>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-success py-3">
                    <h5 class="text-white mb-0 fw-bold">
                        <i class="fa fa-trophy me-2"></i>FORM ACHIEVEMENT MASUK
                    </h5>
                </div>
                <div class="card-body p-4">
                    @include('swal')

                    <form action="{{route('billing.form-achievement.masuk.store')}}" method="post" id="masukForm">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-muted small">TANGGAL</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fa fa-calendar text-secondary"></i></span>
                                    <input type="text" class="form-control bg-light border-0" value="{{date('d M Y')}}" readonly>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <label for="uraian" class="form-label fw-bold text-muted small">URAIAN</label>
                                <input type="text" class="form-control @error('uraian') is-invalid @enderror"
                                    name="uraian" id="uraian" placeholder="Contoh: Bonus Project A" required maxlength="20" value="{{ old('uraian') }}">
                                @error('uraian') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12">
                                <label for="nominal_transaksi" class="form-label fw-bold text-muted small">NOMINAL DITERIMA</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white border-0">Rp</span>
                                    <input type="text" class="form-control form-control-lg fw-bold @error('nominal_transaksi') is-invalid @enderror"
                                        name="nominal_transaksi" id="nominal_transaksi" required>
                                </div>
                                @error('nominal_transaksi') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12 mt-4">
                                <div class="p-3 rounded-3 bg-light border">
                                    <h6 class="fw-bold mb-3 small text-uppercase text-secondary">Informasi Rekening Tujuan (Kas Besar)</h6>
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <label class="small text-muted d-block">Bank</label>
                                            <span class="fw-bold text-dark">{{$rekening->nama_bank}}</span>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="small text-muted d-block">Nama Rekening</label>
                                            <span class="fw-bold text-dark">{{$rekening->nama_rekening}}</span>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="small text-muted d-block">Nomor Rekening</label>
                                            <span class="fw-bold text-dark">{{$rekening->nomor_rekening}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button class="btn btn-success btn-lg fw-bold shadow-sm" type="submit">
                                <i class="fa fa-save me-2"></i>SIMPAN DATA
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
                title: 'Simpan Data Achievement?',
                text: "Pastikan nominal yang Anda masukkan sudah benar.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Simpan!',
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
