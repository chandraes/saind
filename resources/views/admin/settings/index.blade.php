@extends('layouts.app')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/mdbassit/Coloris@latest/dist/coloris.min.css"/>


<style>
    /* Card Modern */
    .settings-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        background: #fff;
    }

    /* Header Section */
    .settings-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 25px 30px;
        border-bottom: 1px solid #eee;
    }

    /* Wrapper Kotak Upload */
    .image-upload-box {
        position: relative;
        width: 100%;
        max-width: 250px;
        height: 180px;
        border: 2px dashed #dee2e6;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f8f9fa;
        overflow: hidden;
    }

    .image-upload-box:hover {
        border-color: #0d6efd;
        background: #f1f7ff;
    }

    .image-upload-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        padding: 10px;
        display: block;
    }

    .favicon-box {
        max-width: 120px !important;
        height: 120px !important;
    }

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

    /* Styling Coloris Custom */
    .clr-field {
        display: block;
        position: relative;
    }

    .clr-field input {
        padding-left: 42px !important;
    }

    .clr-field button {
        border-radius: 8px !important;
        width: 28px !important;
        height: 28px !important;
        left: 8px !important;
    }

    .color-swatch-btn {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        cursor: pointer;
        transition: transform 0.2s;
    }

    .color-swatch-btn:hover {
        transform: scale(1.2);
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="settings-card mb-4">
                    <div
                        class="settings-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <h4 class="mb-1 fw-bold text-dark"><i class="fa fa-cogs me-2"></i>Pengaturan Aplikasi</h4>
                            <p class="text-muted mb-0 small">Kelola identitas utama aplikasi Anda di sini.</p>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('pengaturan') }}"
                                class="btn btn-outline-secondary px-4 py-2 fw-bold rounded-pill">
                                <i class="fa fa-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold rounded-pill">
                                <i class="fa fa-save me-1"></i> Simpan
                            </button>
                        </div>
                    </div>

                    @if(session('success'))
                    <div class="alert alert-success m-4 rounded-3 border-0 bg-success bg-opacity-10 text-success">
                        <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
                    </div>
                    @endif

                    <div class="card-body p-4 p-md-5">

                        <!-- SECTION 1: IDENTITAS UMUM -->
                        <div class="row mb-5">
                            <div class="col-md-4 mb-3">
                                <h6 class="fw-bold text-dark">Identitas Umum</h6>
                                <p class="text-muted small">Nama aplikasi dan warna tema navigasi utama.</p>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold text-secondary small">NAMA APLIKASI</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white text-muted border-end-0"><i
                                                class="fa fa-home"></i></span>
                                        <input type="text" name="app_name" class="form-control border-start-0 ps-0 py-2"
                                            value="{{ $settings['app_name'] ?? '' }}"
                                            placeholder="Default: {{ config('app.name') }}">
                                    </div>
                                    <div class="form-text text-muted fst-italic ms-1">
                                        *Biarkan kosong jika ingin menggunakan nama default sistem.
                                    </div>
                                </div>

                                <!-- WARNA HEADER NAVBAR -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary small">WARNA HEADER
                                        NAVBAR</label>
                                    <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                                        <!-- Input tanpa dibungkus clr-field manual -->
                                        <div style="max-width: 250px;" class="flex-grow-1">
                                            <input type="text" id="app_nav_bg_input" name="app_nav_bg"
                                                class="form-control py-2"
                                                value="{{ $settings['app_nav_bg'] ?? '#212529' }}">
                                        </div>

                                        <!-- Preset Pilihan Cepat -->
                                        <div class="d-flex align-items-center gap-2 border-start ps-3">
                                            <span class="small text-muted me-1">Preset:</span>
                                            <button type="button" class="color-swatch-btn" style="background: #212529;"
                                                onclick="setNavColor('#212529')" title="Dark"></button>
                                            <button type="button" class="color-swatch-btn" style="background: #0d6efd;"
                                                onclick="setNavColor('#0d6efd')" title="Primary Blue"></button>
                                            <button type="button" class="color-swatch-btn" style="background: #198754;"
                                                onclick="setNavColor('#198754')" title="Success Green"></button>
                                            <button type="button" class="color-swatch-btn" style="background: #dc3545;"
                                                onclick="setNavColor('#dc3545')" title="Danger Red"></button>
                                            <button type="button" class="color-swatch-btn" style="background: #6f42c1;"
                                                onclick="setNavColor('#6f42c1')" title="Purple"></button>
                                            <button type="button" class="color-swatch-btn" style="background: #087990;"
                                                onclick="setNavColor('#087990')" title="Teal"></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="text-muted opacity-25 mb-5">

                        <!-- SECTION 2: PERUSAHAAN -->
                        <div class="row mb-5">
                            <div class="col-md-4 mb-3">
                                <h6 class="fw-bold text-dark">Perusahaan</h6>
                                <p class="text-muted small">Nama yang akan muncul pada laporan dan slip gaji.</p>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary small">NAMA PERUSAHAAN</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white text-muted border-end-0"><i
                                                class="fa fa-building"></i></span>
                                        <input type="text" name="app_perusahaan"
                                            class="form-control border-start-0 ps-0 py-2"
                                            value="{{ $settings['app_perusahaan'] ?? '' }}"
                                            placeholder="Contoh: PT. Maju Jaya">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary small">MANAGER ADM &
                                        KEUANGAN</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white text-muted border-end-0"><i
                                                class="fa fa-user"></i></span>
                                        <input type="text" name="app_keuangan"
                                            class="form-control border-start-0 ps-0 py-2"
                                            value="{{ $settings['app_keuangan'] ?? '' }}" placeholder="Contoh: ABC">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary small">ALAMAT PERUSAHAAN</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white text-muted border-end-0"><i
                                                class="fa fa-map-marker"></i></span>
                                        <textarea name="app_alamat" class="form-control border-start-0 ps-0 py-2"
                                            rows="2"
                                            placeholder="Alamat lengkap perusahaan...">{{ $settings['app_alamat'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="text-muted opacity-25 mb-5">

                        <!-- SECTION 3: LOGO -->
                        <div class="row mb-5">
                            <div class="col-md-4 mb-3">
                                <h6 class="fw-bold text-dark">Logo Aplikasi</h6>
                                <p class="text-muted small">Logo utama yang muncul di Navbar dan Login page.</p>
                            </div>
                            <div class="col-md-8">
                                <div class="d-flex flex-column flex-sm-row align-items-start gap-4">
                                    <div class="text-center">
                                        <label for="logoInput" class="image-upload-box shadow-sm" id="logoPreviewBox">
                                            @if(!empty($settings['app_logo']))
                                            <img src="{{ asset('storage/' . $settings['app_logo']) }}"
                                                id="logoPreviewImg">
                                            @else
                                            <div class="upload-placeholder" id="logoPlaceholder">
                                                <i class="fa fa-cloud-upload-alt fs-1"></i>
                                                <div class="small mt-1">Upload Logo</div>
                                            </div>
                                            <img src="" id="logoPreviewImg" style="display:none;">
                                            @endif
                                        </label>
                                        <input type="file" name="app_logo" id="logoInput" class="d-none-input"
                                            accept="image/*"
                                            onchange="previewImage(this, 'logoPreviewImg', 'logoPlaceholder')">
                                        <label for="logoInput"
                                            class="btn btn-outline-primary btn-sm mt-2 rounded-pill px-3">Pilih
                                            File</label>
                                    </div>

                                    <div class="flex-grow-1">
                                        @if(!empty($settings['app_logo']))
                                        <div class="alert alert-light border rounded-3 p-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="delete_app_logo"
                                                    id="delLogo">
                                                <label class="form-check-label text-danger fw-semibold" for="delLogo">
                                                    Hapus & Reset ke Default
                                                </label>
                                            </div>
                                            <small class="text-muted d-block mt-1">
                                                Jika diaktifkan, logo custom akan dihapus dan kembali ke logo bawaan.
                                            </small>
                                        </div>
                                        @else
                                        <div
                                            class="alert alert-info border-0 bg-info bg-opacity-10 text-info rounded-3 p-3 small">
                                            <i class="fa fa-info-circle me-1"></i> Saat ini menggunakan <strong>Logo
                                                Default</strong> sistem.
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="text-muted opacity-25 mb-5">

                        <!-- SECTION 4: FAVICON -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <h6 class="fw-bold text-dark">Favicon</h6>
                                <p class="text-muted small">Ikon kecil yang muncul di tab browser (32x32 atau 64x64).
                                </p>
                            </div>
                            <div class="col-md-8">
                                <div class="d-flex flex-column flex-sm-row align-items-start gap-4">
                                    <div class="text-center">
                                        <label for="favInput" class="image-upload-box favicon-box shadow-sm"
                                            id="favPlaceholderBox">
                                            @if(!empty($settings['app_favicon']))
                                            <img src="{{ asset('storage/' . $settings['app_favicon']) }}"
                                                id="favPreviewImg">
                                            @else
                                            <div class="upload-placeholder" id="favPlaceholder">
                                                <i class="fa fa-globe fs-3"></i>
                                                <div class="small mt-1" style="font-size: 10px">Favicon</div>
                                            </div>
                                            <img src="" id="favPreviewImg" style="display:none;">
                                            @endif
                                        </label>
                                        <input type="file" name="app_favicon" id="favInput" class="d-none-input"
                                            accept="image/*"
                                            onchange="previewImage(this, 'favPreviewImg', 'favPlaceholder')">
                                        <label for="favInput"
                                            class="btn btn-outline-primary btn-sm mt-2 rounded-pill px-3">Pilih
                                            File</label>
                                    </div>

                                    <div class="flex-grow-1">
                                        @if(!empty($settings['app_favicon']))
                                        <div class="alert alert-light border rounded-3 p-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox"
                                                    name="delete_app_favicon" id="delFav">
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

                    </div>
                    <div class="card-footer bg-light p-3 d-block d-md-none text-end">
                        <button type="submit" class="btn btn-primary w-100 fw-bold rounded-pill">
                            <i class="fa fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<!-- CDN Coloris JS -->
<script src="https://cdn.jsdelivr.net/gh/mdbassit/Coloris@latest/dist/coloris.min.js"></script>

<script>
    // 1. Script Preview Upload Gambar
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

    // 2. Inisialisasi Coloris & Event Listener
  document.addEventListener('DOMContentLoaded', function () {
    // HAPUS baris Coloris.init();

    // Langsung panggil konfigurasi Coloris
    Coloris({
        el: '#app_nav_bg_input',
        theme: 'large',
        themeMode: 'light',
        format: 'hex',
        focusInput: true,    // Popover warna langsung muncul saat input diklik
        selectInput: true,   // Otomatis menyeleksi teks hex saat diklik
        swatches: [
            '#212529', '#0d6efd', '#198754', '#dc3545',
            '#6f42c1', '#087990', '#fd7e14', '#20c997'
        ]
    });

    // Event saat warna dipilih dari popover
    document.addEventListener('coloris:pick', event => {
        updateNavbarPreview(event.detail.color);
    });

    // Event saat mengetik kode hex manual
    document.getElementById('app_nav_bg_input').addEventListener('input', function(e) {
        updateNavbarPreview(e.target.value);
    });
});

// Update warna navbar di bagian atas secara real-time
function updateNavbarPreview(color) {
    const navbar = document.querySelector('nav.navbar');
    if (navbar) {
        navbar.style.backgroundColor = color;
    }
}

// Fungsi preset warna cepat
function setNavColor(color) {
    const input = document.getElementById('app_nav_bg_input');
    input.value = color;
    input.dispatchEvent(new Event('input', { bubbles: true }));
    updateNavbarPreview(color);
}
</script>
@endpush
