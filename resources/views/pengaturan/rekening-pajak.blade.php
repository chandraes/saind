@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="fw-bold text-uppercase"><i class="fa fa-university me-2"></i>Rekening Pajak</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{route('home')}}" class="btn btn-outline-secondary btn-sm me-2">
                <i class="fa fa-home"></i> Dashboard
            </a>
            <a href="{{route('pengaturan')}}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-cog"></i> Pengaturan
            </a>
        </div>
    </div>

    @include('swal')

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 text-muted">Informasi Rekening</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{route('pengaturan.rekening-pajak.store')}}" method="post" id="formRekening">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="nama_rek" class="form-label fw-semibold">Nama Rekening</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-user"></i></span>
                                    <input type="text" class="form-control" name="nama_rek" id="nama_rek"
                                        placeholder="Contoh: Bendahara Pengeluaran" value="{{$data['nama_rek'] ?? ''}}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="bank" class="form-label fw-semibold">Nama Bank</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-university"></i></span>
                                    <input type="text" class="form-control" name="bank" id="bank"
                                        placeholder="Contoh: Bank Mandiri" value="{{$data['bank'] ?? ''}}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="no_rek" class="form-label fw-semibold">Nomor Rekening</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-credit-card"></i></span>
                                    <input type="text" class="form-control" name="no_rek" id="no_rek"
                                        placeholder="Masukkan nomor rekening" value="{{$data['no_rek'] ?? ''}}" required>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-primary px-5" onclick="confirmSave()">
                                <i class="fa fa-save me-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmSave() {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data rekening akan diperbarui sesuai inputan Anda.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formRekening').submit();
            }
        })
    }
</script>
@endpush
