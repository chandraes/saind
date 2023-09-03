<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>@yield('title', 'Document')</title>
    <style>
        header {
            position: block;
            float: right;
            top: -30px;
            width: 100%;
            left: 0px;
            height: 50px;
            text-align: right;
            line-height: 35px;
        }
        hr {

        }
        .table-pdf {
            border: 1px solid;
            padding-left: 5px;
            padding-right: 5px;
        }
        .text-pdf {
            font-size: 12pt;
        }
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
        .column-2 {
            max-width: 90%;
            height: 45%;
            max-height: 45%;
            vertical-align: middle;
            justify-content: center; /* Mengatur gambar ke tengah secara vertikal */
            align-items: center;
        }
        .row-2:after {
            content: "";
            display: table;
            clear: both;
            vertical-align: middle;
            justify-content: center; /* Mengatur gambar ke tengah secara vertikal */
            align-items: center;
        }
    </style>
</head>
<body>
    <header>
        <img src="{{public_path('images/saind.png')}}" alt="saind" width="100" style="padding-left: 200px">
        <br>
        <img src="{{public_path('images/saind-2.jpg')}}" alt="saind" width="170">
    </header>
    <header>
        @stack('header')
    </header>
    <br>
<div class="container-fluid" style="margin-top: 10%">@yield('content')</div>
</body>
</html>
