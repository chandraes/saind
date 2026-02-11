<?php
    $total_pemasukan = $d->gaji_pokok + $d->tunjangan_jabatan + $d->tunjangan_keluarga;
    $total_potongan_bpjs = $d->potongan_bpjs_tk + $d->potongan_bpjs_kesehatan;
    $gaji_bersih = $total_pemasukan - $total_potongan_bpjs;
    $total_dibayarkan = $gaji_bersih - $d->kasbon;
?>

<div class="text-center">
    <div style="font-size: 11pt; font-weight: bold; text-transform: uppercase;">
        {{ $global_app_perusahaan }}
    </div>
    <div style="font-size: 8pt; margin-top: 2px;">
        {{ $global_app_alamat }}
    </div>
    <div style="border-bottom: 2px solid #000; margin-top: 5px; margin-bottom: 2px; width: 100%;"></div>
    <div style="border-bottom: 1px solid #000; margin-bottom: 10px; width: 100%;"></div>
</div>

<div class="text-center" style="margin-bottom: 10px;">
    <div style="font-size: 10pt; font-weight: bold; text-decoration: underline;">SLIP GAJI</div>
    <div style="font-size: 8pt;">{{ strtoupper($bulan) }} {{ $tahun }}</div>
</div>

<table class="info-table" style="margin-bottom: 8px;">
    <tr>
        <td width="15%">Tanggal</td><td width="2%">:</td><td width="33%">{{$tanggal_cutoff}}</td>
        <td width="15%">Jabatan</td><td width="2%">:</td><td width="33%">{{ $d->jabatan }}</td>
    </tr>
    <tr>
        <td>NIK</td><td>:</td><td>{{ $d->nik }}</td>
        <td>Bank</td><td>:</td><td>{{ $d->bank }}</td>
    </tr>
    <tr>
        <td>Nama</td><td>:</td><td class="fw-bold">{{ $d->nama }}</td>
        <td>No. Rek</td><td>:</td><td>{{ $d->no_rekening }}</td>
    </tr>
</table>

<table class="journal-table">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="45%">Uraian (Description)</th>
            <th width="25%">Pemasukan (+)</th>
            <th width="25%">Potongan (-)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-center">1</td>
            <td>Gaji Pokok</td>
            <td class="text-end">{{ number_format($d->gaji_pokok, 0, ',', '.') }}</td>
            <td class="text-end bg-total"></td>
        </tr>
        <tr>
            <td class="text-center">2</td>
            <td>Tunjangan Jabatan</td>
            <td class="text-end">{{ number_format($d->tunjangan_jabatan, 0, ',', '.') }}</td>
            <td class="text-end bg-total"></td>
        </tr>
        <tr>
            <td class="text-center">3</td>
            <td>Tunjangan Keluarga</td>
            <td class="text-end">{{ number_format($d->tunjangan_keluarga, 0, ',', '.') }}</td>
            <td class="text-end bg-total"></td>
        </tr>

        <tr>
            <td class="text-center">4</td>
            <td>Iuran BPJS Ketenagakerjaan (2%)</td>
            <td class="text-end bg-total"></td>
            <td class="text-end">{{ number_format($d->potongan_bpjs_tk, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-center">5</td>
            <td>Iuran BPJS Kesehatan (1%)</td>
            <td class="text-end bg-total"></td>
            <td class="text-end">{{ number_format($d->potongan_bpjs_kesehatan, 0, ',', '.') }}</td>
        </tr>

        <tr class="bg-total">
            <td colspan="2" class="text-end">TOTAL</td>
            <td class="text-end">{{ number_format($total_pemasukan, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format($total_potongan_bpjs, 0, ',', '.') }}</td>
        </tr>
         <tr>
            <td colspan="4" class="fw-bold" style="border-right:none;"></td>
        </tr>
        <tr>
            <td colspan="3" class="fw-bold" style="border-right:none;">Gaji Bersih</td>
            <td class="text-end fw-bold">{{ number_format($gaji_bersih, 0, ',', '.') }}</td>
        </tr>

        <tr>
            <td colspan="3" style="color: red; border-right:none;">Kasbon / Cicilan</td>
            <td class="text-end" style="color: red;">{{ number_format($d->kasbon, 0, ',', '.') }}</td>
        </tr>

        <tr class="bg-grand">
            <td colspan="3" class="text-center">TOTAL GAJI YANG DIBAYARKAN</td>
            <td class="text-end">{{ number_format($total_dibayarkan, 0, ',', '.') }}</td>
        </tr>
    </tbody>
</table>

<div style="margin-top: 5px; font-style: italic; font-size: 7.5pt; border: 1px dashed #ccc; padding: 4px; background: #fdfdfd;">
    Terbilang: # {{ ucwords(App\Helpers\PayrollHelper::terbilang($total_dibayarkan)) }} Rupiah #
</div>

<table style="margin-top: 15px;">
    <tr>
        <td width="50%" class="text-center">
            <div style="font-size: 8pt;">Penerima,</div>
            <div style="height: 40px;"></div> <div style="font-size: 8pt; font-weight: bold; text-decoration: underline;">{{ $d->nama }}</div>
            <div style="font-size: 7pt;">{{ $d->jabatan }}</div>
        </td>

        <td width="50%" class="text-center">
            <div style="font-size: 8pt;">Mengetahui,</div>
            <div style="height: 40px;"></div> <div style="font-size: 8pt; font-weight: bold; text-decoration: underline;">{{$global_app_keuangan}}</div>
            <div style="font-size: 7pt;">Manager Adm & Keuangan</div>
        </td>
    </tr>
</table>
