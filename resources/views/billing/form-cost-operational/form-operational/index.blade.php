@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            @include('swal')
            <div class="card shadow border-0">
                <div class="card-header bg-dark text-white p-3 text-center">
                    <h3 class="mb-0 text-uppercase tracking-wider">
                        <i class="fa fa-file-invoice-dollar me-2"></i>Form Operational
                    </h3>
                </div>
                <div class="card-body p-4">
                    <form action="{{route('billing.form-cost-operational.cost-operational.store')}}" method="post" id="masukForm">
                        @csrf
                        <h5 class="text-secondary border-bottom pb-2 mb-3">
                            <i class="fa fa-info-circle me-1"></i> Informasi Transaksi
                        </h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold text-muted">Tanggal</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fa fa-calendar"></i></span>
                                    <input type="text" class="form-control bg-light" name="tanggal" value="{{date('d M Y')}}" disabled>
                                </div>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label for="cost_operational_id" class="form-label fw-bold text-muted">Uraian / Kategori</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa fa-list"></i></span>
                                    <select class="form-select" name="cost_operational_id" id="cost_operational_id" required onchange="updateNominal()">
                                        <option value="" disabled selected>-- Pilih Kategori Cost Operational --</option>
                                        @foreach ($data as $d)
                                            <option value="{{$d->id}}" data-nominal="{{$d->nominal}}">
                                                {{$d->nama}} (Limit: {{$d->jumlah_limit}}x / {{$d->periode}})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                           <div class="col-12 mb-4">
                                <label for="nominal_transaksi" class="form-label fw-bold text-muted">Nominal Transaksi (Otomatis Terkunci)</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-primary text-white fw-bold">Rp</span>
                                    <input type="text" class="form-control fw-bold bg-light" id="nominal_transaksi" placeholder="0" readonly>
                                </div>
                            </div>
                        </div>

                        <h5 class="text-secondary border-bottom pb-2 mb-3 mt-2">
                            <i class="fa fa-university me-1"></i> Informasi Transfer Penerima
                        </h5>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="transfer_ke" class="form-label fw-bold text-muted">Nama Penerima</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa fa-user"></i></span>
                                    <input type="text" class="form-control @error('transfer_ke') is-invalid @enderror"
                                           name="transfer_ke" id="transfer_ke" required maxlength="15" placeholder="Nama Rekening">
                                </div>
                                @error('transfer_ke') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="bank" class="form-label fw-bold text-muted">Bank</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa fa-building"></i></span>
                                    <input type="text" class="form-control @error('bank') is-invalid @enderror"
                                           name="bank" id="bank" required maxlength="10" placeholder="Contoh: BCA, Mandiri">
                                </div>
                                @error('bank') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="no_rekening" class="form-label fw-bold text-muted">Nomor Rekening</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa fa-credit-card"></i></span>
                                    <input type="text" class="form-control @error('no_rekening') is-invalid @enderror"
                                           name="no_rekening" id="no_rekening" required placeholder="0000-0000-0000">
                                </div>
                                @error('no_rekening') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="row mt-4 pt-2">
                            <div class="col-md-6 mb-2">
                                <a href="{{route('billing.index')}}" class="btn btn-outline-secondary w-100 py-2">
                                    <i class="fa fa-arrow-left me-1"></i> Kembali ke Billing
                                </a>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button class="btn btn-success w-100 py-2 fw-bold shadow-sm" type="submit">
                                    <i class="fa fa-paper-plane me-1"></i> Simpan Transaksi
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script>
    // Fungsi otomatis memasukkan nominal standar dari kategori yang dipilih
    function updateNominal() {
        var selectElement = document.getElementById('cost_operational_id');
        var selectedOption = selectElement.options[selectElement.selectedIndex];
        var nominalValue = selectedOption.getAttribute('data-nominal');

        var nominalInput = document.getElementById('nominal_transaksi');
        nominalInput.value = nominalValue;

        if (window.cleaveNominalTransaksi) {
            window.cleaveNominalTransaksi.destroy();
        }
        window.cleaveNominalTransaksi = new Cleave('#nominal_transaksi', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            numeralDecimalMark: ',',
            delimiter: '.'
        });
    }

    $(document).ready(function() {
        new Cleave('#no_rekening', {
            delimiter: '-',
            blocks: [4, 4, 8]
        });
        
        $('#masukForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah anda yakin?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    this.submit();
                }
            })
        });
    });
</script>
@endpush
