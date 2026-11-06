@push('css')
<style>
    body { background-color: #f8fafc; }

    .menu-card {
        border-radius: 16px;
        transition: all 0.3s ease-in-out;
        cursor: pointer;
    }

    .menu-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1) !important;
    }

    .menu-card:hover h6 {
        color: #0d6efd !important;
    }

    .icon-wrapper {
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* ====== TAMBAHAN CLASS UNTUK MENU KOSONG ====== */
    .disabled-card {
        opacity: 0.45; /* Membuatnya terlihat transparan/pudar */
        cursor: not-allowed; /* Mengubah kursor jadi tanda dilarang */
        pointer-events: none; /* Mematikan semua interaksi klik/hover */
        filter: grayscale(100%); /* Membuat gambar SVG dan teks jadi abu-abu total */
    }
</style>
@endpush
