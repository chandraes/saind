<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Preview Gaji {{ $monthName }} {{ $tahun }}</title>
    <style>
        body { font-family: sans-serif; font-size: 9px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #000; padding: 4px; }
        th { background-color: #e9ecef; text-align: center; font-weight: bold; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .header { text-align: center; margin-bottom: 20px; }
        /* Watermark Preview */
       .watermark {
            position: fixed;
            /** Membuat watermark berada di titik tengah layar **/
            top: 50%;
            left: 50%;
            /** Menggeser kembali watermark setengah dari ukurannya sendiri agar benar-benar presisi di tengah **/
            transform: translate(-50%, -50%) rotate(-45deg);

            width: 100%;
            text-align: center;
            color: rgba(166, 166, 166, 0.15); /* Warna merah sangat transparan */
            font-size: 100px;
            font-weight: bold;
            z-index: -1000; /* Di belakang tabel */
            white-space: nowrap;
        }
    </style>
</head>
<body>
    @if(isset($is_preview) && $is_preview)
        <div class="watermark">DRAFT PRATINJAU</div>
    @endif

    <div class="header">
        <h2 style="margin:0">REKAP GAJI DIREKSI & STAFF</h2>
        <h3 style="margin:5px 0">{{ strtoupper($monthName) }} {{ $tahun }}</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Nama</th>
                <th rowspan="2">Jabatan</th>
                <th rowspan="2">Gaji Pokok</th>
                <th colspan="2">Tunjangan</th>
                <th rowspan="2">BPJS-TK (4,89%)</th>
                <th rowspan="2">BPJS-K (4%)</th>
                <th rowspan="2">Pot. TK (2%)</th>
                <th rowspan="2">Pot. K (1%)</th>
                <th rowspan="2">Kotor</th>
                <th rowspan="2">Bersih</th>
                <th rowspan="2">Kasbon</th>
                <th rowspan="2">Sisa Gaji</th>
            </tr>
            <tr>
                <th>Jabatan</th>
                <th>Kel.</th>
            </tr>
        </thead>
        <tbody>
            @php
                $gt = ['gp' => 0, 'tj' => 0, 'tk' => 0, 'btk' => 0, 'bk' => 0, 'ptk' => 0, 'pk' => 0, 'pkotor' => 0, 'pbersih' => 0, 'kasbon' => 0, 'total' => 0];
            @endphp
            @foreach(array_merge($direksi->all(), $data->all()) as $index => $item)
                @php
                    $res = $payroll->calculateComponent($item);
                    $kasbon = isset($item->kode) ? $payroll->calculateKasbon($item, $bulan, $tahun) : 0;

                    $pKotor = round($res['pendapatan_kotor']);
                    $pBersih = round($res['pendapatan_bersih']);
                    $sisaGaji = $pBersih - $kasbon;

                    // Akumulasi Total
                    $gt['gp'] += $item->gaji_pokok; $gt['tj'] += $item->tunjangan_jabatan;
                    $gt['tk'] += $item->tunjangan_keluarga; $gt['btk'] += round($res['bpjs_tk']);
                    $gt['bk'] += round($res['bpjs_k']); $gt['ptk'] += round($res['pot_tk']);
                    $gt['pk'] += round($res['pot_k']); $gt['pkotor'] += $pKotor;
                    $gt['pbersih'] += $pBersih; $gt['kasbon'] += $kasbon;
                    $gt['total'] += $sisaGaji;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->nama }}</td>
                    <td>{{ $item->jabatan->nama ?? $item->jabatan }}</td>
                    <td class="text-end">{{ number_format($item->gaji_pokok, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->tunjangan_jabatan, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->tunjangan_keluarga, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format(round($res['bpjs_tk']), 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format(round($res['bpjs_k']), 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format(round($res['pot_tk']), 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format(round($res['pot_k']), 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($pKotor, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($pBersih, 0, ',', '.') }}</td>
                    <td class="text-end" style="color: red;">{{ number_format($kasbon, 0, ',', '.') }}</td>
                    <td class="text-end fw-bold">{{ number_format($sisaGaji, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="fw-bold">
            <tr style="background-color: #f2f2f2;">
                <td colspan="3" class="text-center">GRAND TOTAL</td>
                <td class="text-end">{{ number_format($gt['gp'], 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($gt['tj'], 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($gt['tk'], 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($gt['btk'], 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($gt['bk'], 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($gt['ptk'], 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($gt['pk'], 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($gt['pkotor'], 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($gt['pbersih'], 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($gt['kasbon'], 0, ',', '.') }}</td>
                <td class="text-end" style="background-color: yellow;">{{ number_format($gt['total'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
