@extends('layouts.app')

@section('styles')
<style>
    /* Card Modern */
    .settings-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        background: #fff;
        overflow: hidden;
    }

    /* Header Section */
    .settings-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 25px 30px;
        border-bottom: 1px solid #eee;
    }

    /* === PERBAIKAN IMAGE OVERFLOW === */

    /* 1. Wrapper Kotak Upload */
    .image-upload-box {
        position: relative;
        width: 100%;
        max-width: 250px; /* Batas lebar agar tidak terlalu panjang */
        height: 180px;    /* Tinggi tetap agar rapi */

        border: 2px dashed #dee2e6;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f8f9fa;
        overflow: hidden; /* PENTING: Potong apapun yang keluar */
    }

    .image-upload-box:hover {
        border-color: #0d6efd;
        background: #f1f7ff;
    }

    /* 2. Style Gambar di dalam kotak */
    .image-upload-box img {
        max-width: 100%;  /* Tidak boleh lebih lebar dari kotak */
        max-height: 100%; /* Tidak boleh lebih tinggi dari kotak */
        object-fit: contain; /* Gambar akan menyesuaikan diri agar muat (tidak gepeng/crop) */
        padding: 10px;
        display: block;
    }

    /* 3. Khusus Favicon (Kita buat lebih kecil dan persegi) */
    .favicon-box {
        max-width: 120px !important;
        height: 120px !important;
    }

    /* === END PERBAIKAN === */

    .upload-placeholder {
        text-align: center;
        color: #6c757d;
        pointer-events: none;
    }

    .d-none-input {
        display: none;
    }

    .form-check-input:checked {
        background-color: #dc3545;
        border-color: #dc3545;
    }
</style>
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="settings-card mb-4">
                    <div class="settings-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 fw-bold text-dark"><i class="fa fa-cogs me-2"></i>Pengaturan Aplikasi</h4>
                            <p class="text-muted mb-0 small">Kelola identitas utama aplikasi Anda di sini.</p>
                        </div>
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold rounded-pill">
                            <i class="fa fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success m-4 rounded-3 border-0 bg-success bg-opacity-10 text-success">
                            <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
                        </div>
                    @endif

                    <div class="card-body p-4 p-md-5">

                        <div class="row mb-5">
                            <div class="col-md-4 mb-3">
                                <h6 class="fw-bold text-dark">Identitas Umum</h6>
                                <p class="text-muted small">Nama yang akan muncul pada judul tab browser, footer, dan email.</p>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary small">NAMA APLIKASI</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white text-muted border-end-0"><i class="fa fa-heading"></i></span>
                                        <input type="text" name="app_name" class="form-control border-start-0 ps-0 py-2"
                                               value="{{ $settings['app_name'] ?? '' }}"
                                               placeholder="Default: {{ config('app.name') }}">
                                    </div>
                                    <div class="form-text text-muted fst-italic ms-1">
                                        *Biarkan kosong jika ingin menggunakan nama default sistem.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="text-muted opacity-25 mb-5">

                        <div class="row mb-5">
                            <div class="col-md-4 mb-3">
                                <h6 class="fw-bold text-dark">Logo Aplikasi</h6>
                                <p class="text-muted small">Logo utama yang muncul di Navbar dan Login page. Format: PNG/JPG (Transparan disarankan).</p>
                            </div>
                            <div class="col-md-8">
                                <div class="d-flex flex-column flex-sm-row align-items-start gap-4">

                                    <div class="text-center">
                                        <label for="logoInput" class="image-upload-box shadow-sm" id="logoPreviewBox">
                                            @if(!empty($settings['app_logo']))
                                                <img src="{{ asset('storage/' . $settings['app_logo']) }}" id="logoPreviewImg">
                                            @else
                                                <div class="upload-placeholder" id="logoPlaceholder">
                                                    <i class="fa fa-cloud-upload-alt fs-1"></i>
                                                    <div class="small mt-1">Upload Logo</div>
                                                </div>
                                                <img src="" id="logoPreviewImg" style="display:none;">
                                            @endif
                                        </label>
                                        <input type="file" name="app_logo" id="logoInput" class="d-none-input" accept="image/*" onchange="previewImage(this, 'logoPreviewImg', 'logoPlaceholder')">
                                        <label for="logoInput" class="btn btn-outline-primary btn-sm mt-2 rounded-pill px-3">Pilih File</label>
                                    </div>

                                    <div class="flex-grow-1">
                                        @if(!empty($settings['app_logo']))
                                            <div class="alert alert-light border rounded-3 p-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="delete_app_logo" id="delLogo">
                                                    <label class="form-check-label text-danger fw-semibold" for="delLogo">
                                                        Hapus & Reset ke Default
                                                    </label>
                                                </div>
                                                <small class="text-muted d-block mt-1">
                                                    Jika diaktifkan, logo custom akan dihapus dan kembali ke logo bawaan Laravel.
                                                </small>
                                            </div>
                                        @else
                                            <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info rounded-3 p-3 small">
                                                <i class="fa fa-info-circle me-1"></i> Saat ini menggunakan <strong>Logo Default</strong> sistem.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="text-muted opacity-25 mb-5">

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <h6 class="fw-bold text-dark">Favicon</h6>
                                <p class="text-muted small">Ikon kecil yang muncul di tab browser. Disarankan ukuran persegi (32x32 atau 64x64).</p>
                            </div>
                            <div class="col-md-8">
                                <div class="d-flex flex-column flex-sm-row align-items-start gap-4">

                                    <div class="text-center">
                                        <label for="favInput" class="image-upload-box favicon-box shadow-sm" id="favPlaceholderBox">
                                            @if(!empty($settings['app_favicon']))
                                                <img src="{{ asset('storage/' . $settings['app_favicon']) }}" id="favPreviewImg">
                                            @else
                                                <div class="upload-placeholder" id="favPlaceholder">
                                                    <i class="fa fa-globe fs-3"></i>
                                                    <div class="small mt-1" style="font-size: 10px">Favicon</div>
                                                </div>
                                                <img src="" id="favPreviewImg" style="display:none;">
                                            @endif
                                        </label>
                                        <input type="file" name="app_favicon" id="favInput" class="d-none-input" accept="image/*" onchange="previewImage(this, 'favPreviewImg', 'favPlaceholder')">
                                        <label for="favInput" class="btn btn-outline-primary btn-sm mt-2 rounded-pill px-3">Pilih File</label>
                                    </div>

                                    <div class="flex-grow-1">
                                        @if(!empty($settings['app_favicon']))
                                            <div class="alert alert-light border rounded-3 p-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="delete_app_favicon" id="delFav">
                                                    <label class="form-check-label text-danger fw-semibold" for="delFav">
                                                        Hapus & Reset ke Default
                                                    </label>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> <div class="card-footer bg-light p-3 d-block d-md-none text-end">
                         <button type="submit" class="btn btn-primary w-100 fw-bold rounded-pill">
                             <i class="fa fa-save me-1"></i> Simpan Perubahan
                         </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

{{-- Script untuk Instant Image Preview (Tetap sama) --}}
<script>
    function previewImage(input, imgId, placeholderId) {
        const preview = document.getElementById(imgId);
        const placeholder = document.getElementById(placeholderId);

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                if(placeholder) placeholder.style.display = 'none';
            }

            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
