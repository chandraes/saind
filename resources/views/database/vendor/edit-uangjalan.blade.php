@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Kesepakatan Uang Jalan Vendor</u></h1>
            <h5 class="text-muted">{{ $data->nama }}</h5>
        </div>
    </div>

    @php
        $isAuthorized = in_array(Auth::user()->role, ['admin', 'su']);
    @endphp

    @if (session('error'))
    <div class="row mt-2">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <strong>{{ session('error') }}</strong>
            </div>
        </div>
    </div>
    @endif

    <form id="formUangJalan" action="{{ route('uj.vendor.uang-jalan.update', $data->id) }}" method="post">
        @csrf
        <input type="hidden" name="vendor_id" value="{{ $data->id }}">

        <div class="row mt-3 mb-3 justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle mb-0">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th style="width: 35%;">Rute</th>
                                        <th style="width: 30%;">Harga Uang Jalan (Rp)</th>
                                        <th style="width: 30%;">UJ Ditahan (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rutes as $v)
                                        @php
                                            $ujRecord = $vendorUjMap->get($v->id);
                                            $valUangJalan = $ujRecord ? number_format($ujRecord->hk_uang_jalan, 0, ',', '.') : number_format($v->uang_jalan ?? 0, 0, ',', '.');
                                            $valUjDitahan = $ujRecord ? number_format($ujRecord->uj_ditahan, 0, ',', '.') : '0';
                                        @endphp
                                        <tr>
                                            <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                                            <td>{{ $v->nama }}</td>
                                            <td>
                                                <input type="hidden" name="rute_id[]" value="{{ $v->id }}">
                                                <input type="text"
                                                       class="form-control number-format"
                                                       name="uang_jalan[]"
                                                       value="{{ $valUangJalan }}"
                                                       required
                                                       @if(!$isAuthorized) readonly @endif>
                                            </td>
                                            <td>
                                                <input type="text"
                                                       class="form-control number-format"
                                                       name="uj_ditahan[]"
                                                       value="{{ $valUjDitahan }}"
                                                       required
                                                       @if(!$isAuthorized) readonly @endif>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center text-center mt-3">
            <div class="col-md-6 d-flex gap-2 justify-content-center">
                <button type="submit" class="btn btn-primary px-4"><i class="fa fa-save me-1"></i> Simpan & Selesai</button>
                <a href="{{ route('vendor.index') }}" class="btn btn-danger px-4"><i class="fa fa-times me-1"></i> Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/cleave.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inisialisasi Cleave.js
        document.querySelectorAll('.number-format').forEach(function (element) {
            new Cleave(element, {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });
        });

        // Intersept submit form dengan SweetAlert2
        const form = document.getElementById('formUangJalan');
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Konfirmasi Perubahan',
                text: 'Apakah Anda yakin ingin menyimpan kesepakatan uang jalan vendor ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
