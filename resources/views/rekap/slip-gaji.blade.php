<!DOCTYPE html>
<html>
<head>
    <title>Slip Gaji - {{ $d->nama }}</title>
    <style>
        /* 1. RESET TOTAL */
        @page {
            margin: 0;
            size: a4 portrait;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            width: 21cm;   /* Lebar A4 Pas */
            height: 29.7cm; /* Tinggi A4 Pas */
        }

        /* 2. KONTAINER UTAMA (ABSOLUTE) */
        /* Kita "paku" posisi slip agar tidak bisa lari ke halaman lain */
        .slip-area {
            position: absolute;
            left: 0;
            width: 21cm;
            height: 14.85cm; /* Setengah A4 Presisi */
            overflow: hidden; /* Mencegah apapun keluar dari kotak ini */
        }

        .area-top { top: 0; }
        .area-bottom { top: 14.85cm; border-top: 1px dashed #999; }

        /* 3. KONTAINER ISI (Agar tidak overflow ke samping) */
        .content-wrapper {
            width: 19cm; /* 21cm - 2cm margin = 19cm */
            margin-left: 1cm; /* Margin Kiri 1cm */
            margin-top: 0.8cm; /* Margin Atas 0.8cm */
        }

        /* 4. TABEL & FONT */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* PENTING: Mencegah tabel melar ke kanan */
        }

        td, th {
            font-size: 7.5pt; /* Sedikit dikecilkan agar muat banyak baris */
            vertical-align: middle; /* Middle agar teks terlihat center vertikal */
            padding: 1px 4px; /* Padding atas-bawah ditipiskan */
            word-wrap: break-word;
        }

        .header-company { font-weight: bold; font-size: 11pt; }
        .header-title { font-weight: bold; font-size: 11pt; text-align: right; }

        /* Garis-garis */
        .border-bottom { border-bottom: 2px solid #000; }
        .border-all { border: 1px solid #000; }
        .bg-grey { background-color: #f0f0f0; }

        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }

        /* Box Terbilang */
        .terbilang-box {
            margin-top: 5px;
            border: 1px solid #aaa;
            padding: 5px;
            font-style: italic;
            font-size: 7pt;
            background: #fdfdfd;
        }

        /* Tanda Gunting */
        .cut-here {
            position: absolute;
            bottom: 5px;
            width: 100%;
            text-align: center;
            font-size: 7pt;
            color: #666;
        }

        /* Tambahkan di block <style> pada file slip-gaji.blade.php */
        .journal-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-top: 5px;
        }
        .journal-table th {
            background-color: #e0e0e0;
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            font-weight: bold;
        }
        .journal-table td {
            border: 1px solid #ccc; /* Border dalam lebih tipis agar rapi */
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            padding: 2px 4px;
        }
        .journal-table tr:last-child td {
            border-bottom: 1px solid #000;
        }
        .bg-total {
            background-color: #f2f2f2;
            font-weight: bold;
            border-top: 2px solid #000 !important;
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
