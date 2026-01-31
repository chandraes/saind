<table>
    <thead>
        <tr>
            <th colspan="14" style="text-align: center; font-size: 14pt; font-weight: bold;">
                REKAP GAJI DIREKSI & STAFF
            </th>
        </tr>
        <tr>
            <th colspan="14" style="text-align: center; font-size: 12pt; font-weight: bold;">
                Periode: {{ Carbon\Carbon::create()->month($bulan)->locale('id')->monthName }} {{ $tahun }}
            </th>
        </tr>
        <tr></tr> {{-- Baris Kosong --}}
        <tr style="background-color: #c6e0b4; border: 1pt solid black;">
            <th rowspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1pt solid black;">No</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1pt solid black;">Nama</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1pt solid black;">Jabatan</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1pt solid black;">Gaji Pokok</th>
            <th colspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1pt solid black;">Tunjangan</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1pt solid black;">BPJS-TK (4,89%)</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1pt solid black;">BPJS-K (4%)</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1pt solid black;">Pot. BPJS-TK (2%)</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1pt solid black;">Pot. BPJS-K (1%)</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1pt solid black;">Total Kotor</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1pt solid black;">Total Bersih</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1pt solid black;">Kasbon</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1pt solid black;">Sisa Gaji Dibayar</th>
        </tr>
        <tr style="background-color: #c6e0b4;">
            <th style="font-weight: bold; text-align: center; border: 1pt solid black;">Jabatan</th>
            <th style="font-weight: bold; text-align: center; border: 1pt solid black;">Keluarga</th>
        </tr>
    </thead>
    <tbody>
        @php
            $gt = ['gp' => 0, 'tj' => 0, 'tk' => 0, 'btk' => 0, 'bk' => 0, 'ptk' => 0, 'pk' => 0, 'pkotor' => 0, 'pbersih' => 0, 'kasbon' => 0, 'total' => 0];
        @endphp
        @foreach(array_merge($direksi->all(), $data->all()) as $index => $item)
            @php
                $res = $payroll->calculateComponent($item);
                // Tetap menggunakan $bulan (integer) untuk kalkulasi agar tidak OutOfRangeException
                $kasbon = isset($item->kode) ? $payroll->calculateKasbon($item, $bulan, $tahun) : 0;
                $sisa = $res['pendapatan_bersih'] - $kasbon;

                // Akumulasi Grand Total
                $gt['gp'] += $item->gaji_pokok;
                $gt['tj'] += $item->tunjangan_jabatan;
                $gt['tk'] += $item->tunjangan_keluarga;
                $gt['btk'] += $res['bpjs_tk'];
                $gt['bk'] += $res['bpjs_k'];
                $gt['ptk'] += $res['pot_tk'];
                $gt['pk'] += $res['pot_k'];
                $gt['pkotor'] += $res['pendapatan_kotor'];
                $gt['pbersih'] += $res['pendapatan_bersih'];
                $gt['kasbon'] += $kasbon;
                $gt['total'] += $sisa;
            @endphp
            <tr>
                <td style="border: 1pt solid black; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1pt solid black;">{{ $item->nama }}</td>
                <td style="border: 1pt solid black;">{{ $item->jabatan->nama ?? $item->jabatan }}</td>
                <td style="border: 1pt solid black; text-align: right;">{{ $item->gaji_pokok }}</td>
                <td style="border: 1pt solid black; text-align: right;">{{ $item->tunjangan_jabatan }}</td>
                <td style="border: 1pt solid black; text-align: right;">{{ $item->tunjangan_keluarga }}</td>
                <td style="border: 1pt solid black; text-align: right;">{{ $res['bpjs_tk'] }}</td>
                <td style="border: 1pt solid black; text-align: right;">{{ $res['bpjs_k'] }}</td>
                <td style="border: 1pt solid black; text-align: right;">{{ $res['pot_tk'] }}</td>
                <td style="border: 1pt solid black; text-align: right;">{{ $res['pot_k'] }}</td>
                <td style="border: 1pt solid black; text-align: right;">{{ $res['pendapatan_kotor'] }}</td>
                <td style="border: 1pt solid black; text-align: right;">{{ $res['pendapatan_bersih'] }}</td>
                <td style="border: 1pt solid black; text-align: right; color: #ff0000;">{{ $kasbon }}</td>
                <td style="border: 1pt solid black; text-align: right; font-weight: bold;">{{ $sisa }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background-color: #d9d9d9; font-weight: bold;">
            <td colspan="3" style="border: 1pt solid black; text-align: center;">GRAND TOTAL</td>
            <td style="border: 1pt solid black; text-align: right;">{{ $gt['gp'] }}</td>
            <td style="border: 1pt solid black; text-align: right;">{{ $gt['tj'] }}</td>
            <td style="border: 1pt solid black; text-align: right;">{{ $gt['tk'] }}</td>
            <td style="border: 1pt solid black; text-align: right;">{{ $gt['btk'] }}</td>
            <td style="border: 1pt solid black; text-align: right;">{{ $gt['bk'] }}</td>
            <td style="border: 1pt solid black; text-align: right;">{{ $gt['ptk'] }}</td>
            <td style="border: 1pt solid black; text-align: right;">{{ $gt['pk'] }}</td>
            <td style="border: 1pt solid black; text-align: right;">{{ $gt['pkotor'] }}</td>
            <td style="border: 1pt solid black; text-align: right;">{{ $gt['pbersih'] }}</td>
            <td style="border: 1pt solid black; text-align: right; color: #ff0000;">{{ $gt['kasbon'] }}</td>
            <td style="border: 1pt solid black; text-align: right; background-color: #ffff00;">{{ $gt['total'] }}</td>
        </tr>
    </tfoot>
</table>
