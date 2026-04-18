@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <h2 class="fw-bold text-uppercase border-bottom pb-2 d-inline-block">Edit Group WhatsApp</h2>
        </div>
    </div>

    @if (session('error'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{{ session('error') }}',
            });
        });
    </script>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white py-3">
                    <h5 class="card-title mb-0"><i class="fa fa-pencil me-2"></i> Form Ubah Data Grup</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('pengaturan.wa.update', $data->id) }}" method="post" id="masukForm">
                        @csrf
                        @method('PATCH')

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="untuk" class="form-label fw-bold">Peruntukan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa fa-tag"></i></span>
                                    <input type="text" class="form-control" name="untuk" id="untuk" value="{{ $data->untuk }}" disabled readonly>
                                </div>
                                <small class="text-muted">Peruntukan grup tidak dapat diubah.</small>
                            </div>

                            <input type="hidden" name="group_id" id="group_id_hidden" value="{{ $data->group_id }}">

                            <div class="col-md-6">
                                <label for="nama_group" class="form-label fw-bold">Pilih Grup WA <span class="text-danger">*</span></label>
                                <select class="form-select" name="nama_group" id="nama_group" required>
                                    <option value="" disabled>-- Pilih Grup --</option>
                                    @foreach ($group as $g)
                                        <option value="{{ $g['id'] }}" {{ $data->nama_group == $g['id'] ? 'selected' : '' }}>
                                            {{ $g['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a class="btn btn-outline-secondary" href="{{ url()->previous() }}">
                                <i class="fa fa-arrow-left me-1"></i> Kembali
                            </a>
                            <button class="btn btn-success px-4" type="submit">
                                <i class="fa fa-save me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush

@push('js')
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        // Inisialisasi Select2
        $('#nama_group').select2({
            theme: 'bootstrap-5',
            width: '100%' // Memastikan select2 memenuhi kolom
        });

        // Event listener saat select2 berubah nilainya
        $('#nama_group').on('change', function() {
            var group_name = $('#nama_group option:selected').text().trim();
            $('#group_id_hidden').val(group_name);
        });

        // Konfirmasi SweetAlert saat form disubmit
        $('#masukForm').submit(function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Simpan',
                text: "Apakah Anda yakin ingin menyimpan perubahan data grup ini?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754', // Warna hijau success bootstrap
                cancelButtonColor: '#6c757d', // Warna abu-abu secondary
                confirmButtonText: '<i class="fa fa-check"></i> Ya, Simpan!',
                cancelButtonText: '<i class="fa fa-times"></i> Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    if ($('#spinner').length) {
                        $('#spinner').show();
                    }
                    this.submit();
                }
            });
        });
    });
</script>
@endpush
