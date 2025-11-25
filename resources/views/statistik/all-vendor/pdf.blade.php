@extends('layouts.doc-nologo-2')
@section('content')
<div class="container-fluid">
    <center>
        <h2>PERFORM UNIT</h2>
        <h2>BULAN {{Str::upper($nama_bulan)}} {{$tahun}}</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row mt-3">
        <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="25%">Vendor</th>
                <th width="20%">Vehicle (Lambung)</th>
                <th width="15%">Rute Pendek</th>
                <th width="15%">Rute Panjang</th>
                <th width="20%">Total Rute</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotalPendek = 0;
                $grandTotalPanjang = 0;
                $grandTotalAll = 0;
                $previousVendor = null;
            @endphp

            @foreach ($data as $row)
                @php
                    // Logika Pemisah Vendor
                    $isNewVendor = ($previousVendor !== null && $previousVendor !== $row->vendor_name);
                    $previousVendor = $row->vendor_name;

                    // Hitungan
                    $totalRow = $row->total_rute_pendek + $row->total_rute_panjang;
                    $grandTotalPendek += $row->total_rute_pendek;
                    $grandTotalPanjang += $row->total_rute_panjang;
                    $grandTotalAll += $totalRow;

                    // Warna text (Merah jika 0)
                    $textClass = ($totalRow == 0) ? 'text-danger fw-bold' : '';
                @endphp

                {{-- Baris Pemisah (Separator) --}}
                {{-- Di PDF, lebih aman pakai TR kosong daripada margin CSS --}}
                @if ($isNewVendor)
                    <tr class="vendor-separator">
                        <td colspan="6"></td>
                    </tr>
                @endif

                <tr class="{{ $textClass }}">
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="fw-bold">{{ $row->vendor_name }}</td>
                    <td class="text-center">{{ $row->nomor_lambung }}</td>
                    <td class="text-center">{{ number_format($row->total_rute_pendek, 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($row->total_rute_panjang, 0, ',', '.') }}</td>
                    <td class="text-center fw-bold" style="background-color: #fcfcfc;">
                        {{ number_format($totalRow, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right" style="padding-right: 15px; text-transform: uppercase;">Grand Total</td>
                <td class="text-center">{{ number_format($grandTotalPendek, 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($grandTotalPanjang, 0, ',', '.') }}</td>
                <td class="text-center" style="background-color: #ddd;">
                    {{ number_format($grandTotalAll, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>
    </div>
</div>
@push('css')
    <style>
        /* Setup Dasar Halaman */
        @page {
            margin: 1cm 1cm;
        }
        /* Header Laporan */
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2, .header h3 {
            margin: 2px 0;
            text-transform: uppercase;
        }
        .header p {
            font-size: 10px;
            margin-top: 5px;
            font-style: italic;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse; /* Wajib untuk border yang rapi */
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #444; /* Border sedikit lebih gelap */
            padding: 6px 4px;
            vertical-align: middle;
        }

        /* Styling Header Tabel */
        thead th {
            background-color: #f2f2f2; /* Abu-abu muda hemat tinta */
            color: #000;
            font-weight: bold;
            text-align: center;
        }

        /* Helper Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }

        /* Warna Merah untuk 0 (Gunakan kode hex agar support PDF) */
        .text-danger { color: #d9534f; }

        /* Styling Total Row (Footer) */
        .total-row td {
            background-color: #e9ecef;
            font-weight: bold;
        }

        /* Styling Pemisah Vendor (Separator) */
        .vendor-separator td {
            border-left: none;
            border-right: none;
            background-color: #fff; /* Putih */
            height: 7px; /* Tinggi jarak */
        }
        /* Hapus border atas/bawah agar terlihat seperti jarak kosong */
        tr.vendor-separator + tr td {
            border-top: 2px solid #000; /* Garis tebal di atas vendor baru */
        }
    </style>
@endpush
@endsection
