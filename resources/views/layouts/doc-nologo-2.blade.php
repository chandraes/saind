<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" href="{{public_path('assets/plugins/bootstrap/css/bootstrap.min.css')}}">
    <title>@yield('title', 'Document')</title>
    <style>
        header {
            position: fixed;
            display: block !important;
            float: right;
            top: -20px;
            width: 100% !important;
            left: 0px;
            height: 50px;
            text-align: right;
        }
        /* .table-pdf {
            border: 1px solid;
            padding-left: 5px;
            padding-right: 5px;
        } */
        /* .text-pdf {
            font-size: 8pt;
        } */
        .text-10 {
            font-size: 10pt;
        }
        .page-break {
            page-break-after: always;
        }
        .column-pdf {
            float: left;
            width: 50%;
        }
        .row-pdf:after {
            content: "";
            display: table;
            clear: both;
        }
        .column-4 {
            float: left;
            width: 25%;
        }

        @page {
        margin: 1cm; /* Mengurangi margin kertas agar area cetak lebih luas */
        }
        .table-pdf {
            border-collapse: collapse; /* Sangat penting agar border tidak double dan memakan tempat */
            width: 100%;
            table-layout: fixed; /* Memaksa tabel mengikuti lebar kontainer */
        }
        .table-pdf th, .table-pdf td {
            border: 1px solid #000;
            padding: 2px 4px; /* Padding sangat kecil agar teks tidak overflow */
            word-wrap: break-word; /* Memaksa teks panjang turun ke bawah */
            font-size: 7pt; /* Ukuran font lebih kecil khusus untuk tabel padat */
        }
        .text-pdf {
            font-size: 7pt;
        }
        /* Mengatur lebar spesifik untuk kolom yang isinya sedikit */
        .col-nik { width: 40px; }
        .col-nama { width: 100px; }
        .col-jabatan { width: 80px; }
        .col-angka { width: 55px; }
    </style>

    @stack('css')
</head>
<body>
    <br>
<div class="container-fluid">@yield('content')</div>
</body>
</html>
