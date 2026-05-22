<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Perform Unit - {{ $nama_vendor }}</title>
    <style>
        /* CSS CONFIGURATION FOR PROFESSIONAL PRINTING */
        @page {
            size: A4 landscape;
            margin: 12mm 10mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #2d3748;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .header-container {
            text-align: center;
            margin-bottom: 18px;
            border-bottom: 2px solid #1a202c;
            padding-bottom: 8px;
        }
        .header-container h2 {
            margin: 0 0 4px 0;
            font-size: 16px;
            font-weight: 700;
            color: #1a202c;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-container h4 {
            margin: 0 0 4px 0;
            font-size: 12px;
            font-weight: 600;
            color: #4a5568;
        }
        .header-container p {
            margin: 0;
            font-size: 10px;
            color: #718096;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #2b6cb0;
            margin: 15px 0 6px 0;
            text-transform: uppercase;
            display: flex;
            align-items: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        th, td {
            border: 1px solid #cbd5e0;
            padding: 5px 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }
        th {
            background-color: #edf2f7;
            color: #2d3748;
            font-weight: 700;
            font-size: 9px;
            text-transform: uppercase;
        }
        .th-date {
            background-color: #e2e8f0;
        }
        .th-sub {
            background-color: #f7fafc;
            font-size: 8px;
            font-weight: 600;
        }

        /* Typography Styling Inside Cells */
        .text-start { text-align: left; padding-left: 6px; }
        .font-bold { font-weight: bold; }
        .cell-unit-title { font-size: 10px; color: #1a202c; }
        .cell-nopol { font-size: 8px; color: #718096; font-weight: normal; }

        .color-rute {
            color: #2b6cb0;
            font-weight: 600;
            line-height: 1.2;
        }
        .color-tonase {
            color: #2f855a;
            font-weight: 600;
            line-height: 1.2;
        }
        .text-muted-dash {
            color: #a0aec0;
        }

        /* Summary Section Cards & Tables */
        .summary-box {
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px;
            width: 320px;
            margin-top: 15px;
        }
        .summary-box table { margin-bottom: 0; box-shadow: none; }
        .summary-box table td { border: none; padding: 3px 0; text-align: left; font-size: 10px; }

        .bg-summary-header { background-color: #2d3748; color: #ffffff; }
        .bg-summary-row { background-color: #f8fafc; }

        /* Page breaking layout flags */
        .page-break {
            page-break-after: always;
        }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header-container">
        <h2>Statistik Perform Unit</h2>
        <h4>Vendor: {{ strtoupper($nama_vendor) }}</h4>
        <p>Periode Data: {{ \Carbon\Carbon::parse($start_date)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($end_date)->translatedFormat('d F Y') }}</p>
    </div>

    @php
        // Pecah array tanggal menjadi kelompok per 7 hari dengan mempertahankan index aslinya
        $dateChunks = array_chunk($dates_array, 7, true);
    @endphp

    @foreach ($dateChunks as $chunkIndex => $chunk)
        <div class="section-title">
            • DATA OPERASIONAL MINGGU KE-{{ $chunkIndex + 1 }}
            ({{ \Carbon\Carbon::parse(reset($chunk))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(end($chunk))->format('d/m/Y') }})
        </div>

        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 14%;">Unit / Kendaraan</th>

                    @foreach ($chunk as $dateStr)
                        <th colspan="2" class="th-date" style="width: {{ 86 / count($chunk) }}%;">
                            {{ \Carbon\Carbon::parse($dateStr)->translatedFormat('d M Y') }}
                        </th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($chunk as $dateStr)
                        <th class="th-sub">Rute</th>
                        <th class="th-sub">Ton</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($statistics as $nomor_lambung => $statistic)
                    @php $v = $statistic['vehicle']; @endphp
                    <tr>
                        <td class="text-start">
                            <span class="font-bold cell-unit-title">{{ $v->nomor_lambung }}</span><br>
                            <span class="cell-nopol">{{ $v->nopol }}</span>
                        </td>

                        @foreach ($chunk as $idx => $dateStr)
                            @php $dayData = $statistic['data'][$idx]; @endphp

                            <td>
                                @if ($dayData['rute'] !== '-')
                                    <div class="color-rute">{!! str_replace(',', '<br>', $dayData['rute']) !!}</div>
                                @else
                                    <span class="text-muted-dash">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($dayData['tonase'] !== '-')
                                    <div class="color-tonase">{!! str_replace(',', '<br>', $dayData['tonase']) !!}</div>
                                @else
                                    <span class="text-muted-dash">-</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    <div class="page-break"></div>

    <div class="section-title" style="font-size: 13px; color: #1a202c; border-bottom: 1px solid #cbd5e0; padding-bottom: 4px;">
        • RINGKASAN AKUMULASI TOTAL PERIODE
    </div>

    <table style="margin-top: 10px;">
        <thead>
            <tr class="bg-summary-header">
                <th style="width: 25%; background-color: #2d3748; color: white;">Unit / Kendaraan</th>
                <th style="width: 20%; background-color: #2d3748; color: white;">Total Rute Panjang</th>
                <th style="width: 20%; background-color: #2d3748; color: white;">Total Rute Pendek</th>
                <th style="width: 15%; background-color: #2d3748; color: white;">Total Ritase</th>
                <th style="width: 20%; background-color: #2d3748; color: white;">Total Tonase Muatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($statistics as $nomor_lambung => $statistic)
                @php
                    $v = $statistic['vehicle'];
                    $totalRitase = $statistic['long_route_count'] + $statistic['short_route_count'];
                @endphp
                <tr class="{{ $loop->iteration % 2 == 0 ? 'bg-summary-row' : '' }}">
                    <td class="text-start font-bold">
                        {{ $v->nomor_lambung }} <span class="cell-nopol">({{ $v->nopol }})</span>
                    </td>
                    <td class="color-rute" style="font-size: 11px;">{{ number_format($statistic['long_route_count'], 0, ',', '.') }} Rit</td>
                    <td style="color: #dd6b20; font-weight: 600; font-size: 11px;">{{ number_format($statistic['short_route_count'], 0, ',', '.') }} Rit</td>
                    <td class="font-bold" style="font-size: 11px; background-color: #edf2f7;">{{ number_format($totalRitase, 0, ',', '.') }} Rit</td>
                    <td class="color-tonase" style="font-size: 11px;">{{ number_format($statistic['total_tonase'], 2, ',', '.') }} Ton</td>
                </tr>
            @endforeach

            <tr style="background-color: #1a202c; color: #ffffff; font-weight: bold;">
                <td class="text-start" style="padding-left: 10px; font-size: 10px; background-color: #1a202c; color: white;">GRAND TOTAL VENDOR</td>
                <td style="font-size: 11px; background-color: #1a202c; color: white;">{{ number_format($grand_total_long_route, 0, ',', '.') }} Rit</td>
                <td style="font-size: 11px; background-color: #1a202c; color: white;">{{ number_format($grand_total_short_route, 0, ',', '.') }} Rit</td>
                <td style="font-size: 11px; background-color: #2d3748; color: white;">{{ number_format($grand_total_long_route + $grand_total_short_route, 0, ',', '.') }} Rit</td>
                <td style="font-size: 11px; background-color: #2f855a; color: white;">{{ number_format($grand_total_tonase, 2, ',', '.') }} Ton</td>
            </tr>
        </tbody>
    </table>

    <div class="summary-box">
        <div class="font-bold" style="margin-bottom: 6px; font-size: 11px; color: #2d3748;">METRIKS UTILISASI VENDOR:</div>
        <table>
            <tr>
                <td style="width: 60%;"><strong>Persentase Utilisasi Rute Panjang</strong></td>
                <td>: <span class="font-bold" style="color: #2b6cb0; font-size: 12px;">{{ number_format($persentase_utilisasi, 2, ',', '.') }} %</span></td>
            </tr>
            <tr>
                <td><strong>Jumlah Unit Terfilter Beroperasi</strong></td>
                <td>: <span class="font-bold" style="font-size: 11px;">{{ number_format($jumlah_vehicle, 0, ',', '.') }} Unit</span></td>
            </tr>
        </table>
    </div>

</body>
</html>
