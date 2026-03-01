<!DOCTYPE html>
<html>
<head>
    <title>Slip Gaji - {{ $d->nama }}</title>
    <style>
        /* RESET & PAGE SETUP */
        @page { margin: 0; size: a4 portrait; }

        body {
            font-family: Arial, sans-serif;
            margin: 0; padding: 0;
            width: 21cm; height: 29.7cm;
            font-size: 8pt;
        }

        /* CONTAINER UTAMA (Pembungkus Setengah Halaman) */
        .slip-area {
            position: absolute; left: 0; width: 100%; /* Full width kertas */
            height: 14.85cm; /* Setengah A4 Presisi */
            overflow: hidden; /* Potong jika ada yang bandel keluar */
        }
        .area-top { top: 0; }
        .area-bottom { top: 14.85cm; border-top: 1px dashed #999; }

        /* KUNCI LEBAR KONTEN (SOLUSI OVERFLOW) */
        /* Lebar A4 = 21cm. Kita pakai 19cm saja agar ada margin aman 1cm kiri-kanan */
        .content-wrapper {
            width: 19cm;
            margin: 0 auto; /* Tengah secara horizontal */
            padding-top: 0.5cm;
        }

        /* TYPOGRAPHY & UTILS */
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }

        /* TABLE STYLES */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* PENTING: Agar kolom tidak melebar paksa */
        }

        /* Tabel Info */
        .info-table td {
            padding: 1px 0;
            vertical-align: top;
        }

        /* Tabel Jurnal */
        .journal-table {
            border: 1px solid #000;
            margin-top: 5px;
        }
        .journal-table th {
            background: #eee;
            border: 1px solid #000;
            padding: 3px;
            font-size: 7.5pt; /* Font header diperkecil sedikit */
            text-transform: uppercase;
        }
        .journal-table td {
            border: 1px solid #000;
            padding: 2px 4px;
            font-size: 8pt;
            word-wrap: break-word; /* Agar teks panjang turun ke bawah, bukan melebar */
        }

        .bg-total { background-color: #f8f9fa; font-weight: bold; }
        .bg-grand { background-color: #e2e3e5; font-weight: bold; }

        /* TANDA GUNTING */
        .cut-here {
            position: absolute; bottom: 5px; left: 0; width: 100%;
            text-align: center; font-size: 7pt; color: #888;
        }
    </style>
</head>
<body>

    <div class="slip-area area-top">
        <div class="content-wrapper">
            @include('rekap.partial-slip-content-compact')
        </div>
        <div class="cut-here"></div>
    </div>

    <div class="slip-area area-bottom">
        <div class="content-wrapper">
            @include('rekap.partial-slip-content-compact')
        </div>
    </div>

</body>
</html>
