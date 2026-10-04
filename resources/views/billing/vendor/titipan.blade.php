@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Header Section -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-11">
            <div class="d-flex align-items-center justify-content-between pb-3 border-bottom">
                <div>
                    <h3 class="fw-bold text-primary mb-1">
                        <i class="bi bi-wallet2 me-2"></i>Form Titipan Vendor
                    </h3>
                    <p class="text-muted mb-0 small">Input data penambahan titipan vendor dengan cepat dan terstruktur.</p>
                </div>
                <div>
                    <span class="badge bg-white text-dark border shadow-sm px-3 py-2 rounded-pill fs-6 fw-normal">
                        <i class="bi bi-calendar3 text-primary me-1"></i> {{ date('d-m-Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    @include('swal')

    <form action="{{route('billing.vendor.titipan-store')}}" method="post" id="masukForm">
        @csrf
        <div class="row justify-content-center">
            <div class="col-md-11">

                <!-- Section 1: Informasi Transaksi & Vendor -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h6 class="card-title fw-bold text-dark mb-0">
                            <i class="fa fa-users text-primary me-2"></i>Informasi Transaksi & Vendor
                        </h6>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-secondary small">Tanggal</label>
                                <input type="text" class="form-control bg-light" name="" id="" value="{{date('d-m-Y')}}" disabled>
                            </div>
                            <div class="col-md-3">
                                <label for="id" class="form-label fw-semibold text-secondary small">Nama Vendor <span class="text-danger">*</span></label>
                                <select class="form-select @if ($errors->has('id')) is-invalid @endif" name="id" id="id" onchange="funGetVendor()" required>
                                    <option selected disabled value="">-- Pilih Vendor --</option>
                                    @foreach ($vendor as $d)
                                        <option value="{{$d->id}}">{{$d->nama}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="nomor_lambung" class="form-label fw-semibold text-secondary small">Nomor Lambung</label>
                                <input type="text" class="form-control bg-light" name="nomor_lambung" id="nomor_lambung" disabled placeholder="-">
                            </div>
                            <div class="col-md-3">
                                <label for="nilai" class="form-label fw-semibold text-secondary small">Nilai <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary fw-bold">Rp</span>
                                    <input type="text" class="form-control @if ($errors->has('nilai')) is-invalid @endif" name="nilai" id="nilai" required placeholder="0">
                                </div>
                                @if ($errors->has('nilai'))
                                    <div class="text-danger small mt-1">{{$errors->first('nilai')}}</div>
                                @endif
                            </div>
                            <div class="col-md-12">
                                <label for="uraian" class="form-label fw-semibold text-secondary small">Uraian <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @if ($errors->has('uraian')) is-invalid @endif" name="uraian" id="uraian" placeholder="Masukkan uraian titipan..." required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Rekening Tujuan Transfer -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h6 class="card-title fw-bold text-dark mb-0">
                            <i class="bi bi-bank2 text-primary me-2"></i>Rekening Tujuan Transfer
                        </h6>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="transfer_ke" class="form-label fw-semibold text-secondary small">Nama Rekening</label>
                                <input type="text" class="form-control @if ($errors->has('transfer_ke')) is-invalid @endif" name="transfer_ke" id="transfer_ke" value="{{old('transfer_ke')}}" maxlength="15" placeholder="Nama pemilik rekening">
                                @if ($errors->has('transfer_ke'))
                                    <div class="invalid-feedback">{{$errors->first('transfer_ke')}}</div>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label for="bank" class="form-label fw-semibold text-secondary small">Bank</label>
                                <input type="text" class="form-control @if ($errors->has('bank')) is-invalid @endif" name="bank" id="bank" value="{{old('bank')}}" maxlength="10" placeholder="Contoh: BCA / Mandiri">
                                @if ($errors->has('bank'))
                                    <div class="invalid-feedback">{{$errors->first('bank')}}</div>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <label for="no_rekening" class="form-label fw-semibold text-secondary small">Nomor Rekening</label>
                                <input type="text" class="form-control @if ($errors->has('no_rekening')) is-invalid @endif" name="no_rekening" id="no_rekening" value="{{old('no_rekening')}}" placeholder="Nomor rekening bank">
                                @if ($errors->has('no_rekening'))
                                    <div class="invalid-feedback">{{$errors->first('no_rekening')}}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-2 mt-4 mb-5">
                    <a href="{{route('billing.index')}}" class="btn btn-light border px-4 fw-semibold text-secondary">
                        <i class="bi bi-x-lg me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary px-5 fw-semibold shadow-sm">
                        <i class="bi bi-check-lg me-1"></i> Simpan Data
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@push('js')
    {{-- Cleave.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>
    <script>
        var cleaveNilai;

        $(document).ready(function() {
            // Inisialisasi Cleave.js untuk format mata uang
            cleaveNilai = new Cleave('#nilai', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });
        });

        // Submit form dengan pencegahan double click
        $('#masukForm').submit(function(e){
            e.preventDefault();
            let form = this;
            let submitBtn = $(form).find('button[type="submit"]');

            if (submitBtn.is(':disabled')) {
                return false;
            }

            Swal.fire({
                title: 'Apakah anda yakin?',
                text: "Pastikan data titipan sudah sesuai sebelum disimpan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Memproses...');
                    $('#spinner').show();
                    form.submit();
                }
            });
        });

        function funGetVendor() {
            var id = $('#id').val();
            $.ajax({
                url: "{{route('billing.vendor.get-vehicle')}}",
                type: "GET",
                data: {
                    id: id
                },
                success: function(data){
                    funGetPlafonTitipan();
                    $('#nomor_lambung').val(data);

                    var text = $("#id option:selected").text();
                    var id_vendor = $('#id').val();

                    $('#uraian').val('Titipan ' + text);

                    var vendor = {!! json_encode($vendor) !!};
                    for (let i = 0; i < vendor.length; i++) {
                        if (vendor[i].id == id_vendor) {
                            $('#transfer_ke').val(vendor[i].nama_rekening);
                            $('#bank').val(vendor[i].bank);
                            $('#no_rekening').val(vendor[i].no_rekening);
                        }
                    }
                }
            });
        }

        function funGetPlafonTitipan() {
            var id = $('#id').val();
            $.ajax({
                url: "{{route('billing.vendor.get-plafon-titipan')}}",
                type: "GET",
                data: {
                    id: id
                },
                success: function(data){
                    if (data > 0) {
                        if (cleaveNilai) {
                            cleaveNilai.setRawValue(data);
                        } else {
                            $('#nilai').val(data);
                        }
                    } else {
                        if (cleaveNilai) {
                            cleaveNilai.setRawValue('');
                        } else {
                            $('#nilai').val('');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Sisa saldo melebihi plafon!',
                        });
                    }
                }
            });
        }
    </script>
@endpush
