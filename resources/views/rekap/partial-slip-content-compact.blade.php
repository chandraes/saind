<table class="border-bottom" style="margin-bottom: 5px; padding-bottom: 3px;">
    <tr>
        <td width="60%">
            <div class="header-company">PT. NAMA PERUSAHAAN</div>
            <div style="font-size: 7pt;">Jl. Alamat Perusahaan No. 123, Kota</div>
        </td>
        <td width="40%" class="text-end">
            <div class="header-title">SLIP GAJI</div>
            <div style="font-size: 7pt;">Periode: {{ strtoupper($bulan) }} {{ $tahun }}</div>
        </td>
    </tr>
</table>

<table style="margin-bottom: 5px; font-size: 7.5pt;">
    <tr>
        <td width="12%">NIK</td><td width="1%">:</td><td width="30%">{{ $d->nik }}</td>
        <td width="12%">Jabatan</td><td width="1%">:</td><td width="44%">{{ $d->jabatan }}</td>
    </tr>
    <tr>
        <td>Nama</td><td>:</td><td class="fw-bold">{{ $d->nama }}</td>
        <td>Bank</td><td>:</td><td>{{ $d->bank }} - {{ $d->no_rekening }}</td>
    </tr>
</table>

<table class="journal-table">
    <thead>
        <tr style="background: #eee;">
            <th width="5%">No</th>
            <th width="45%">Uraian (Description)</th>
            <th width="25%">Pemasukan (+)</th>
            <th width="25%">Potongan (-)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-center" style="text-align: center">1</td>
            <td>Gaji Pokok</td>
            <td class="text-end">{{ number_format($d->gaji_pokok, 0, ',', '.') }}</td>
            <td class="text-end bg-grey"></td>
        </tr>

        <tr>
            <td class="text-center" style="text-align: center">2</td>
            <td>Tunjangan Jabatan</td>
            <td class="text-end">{{ number_format($d->tunjangan_jabatan, 0, ',', '.') }}</td>
            <td class="text-end bg-grey"></td>
        </tr>

        <tr>
            <td class="text-center" style="text-align: center">3</td>
            <td>Tunjangan Keluarga</td>
            <td class="text-end">{{ number_format($d->tunjangan_keluarga, 0, ',', '.') }}</td>
            <td class="text-end bg-grey"></td>
        </tr>

        <tr>
            <td class="text-center" style="text-align: center">4</td>
            <td>Tunj. BPJS Ketenagakerjaan (Perusahaan)</td>
            <td class="text-end">{{ number_format($d->bpjs_tk, 0, ',', '.') }}</td>
            <td class="text-end bg-grey"></td>
        </tr>

        <tr>
            <td class="text-center" style="text-align: center">5</td>
            <td>Tunj. BPJS Kesehatan (Perusahaan)</td>
            <td class="text-end">{{ number_format($d->bpjs_k, 0, ',', '.') }}</td>
            <td class="text-end bg-grey"></td>
        </tr>

        <tr>
            <td class="text-center" style="text-align: center">6</td>
            <td>Iuran BPJS Ketenagakerjaan (2%)</td>
            <td class="text-end bg-grey"></td>
            <td class="text-end">{{ number_format($d->potongan_bpjs_tk, 0, ',', '.') }}</td>
        </tr>

        <tr>
            <td class="text-center" style="text-align: center">7</td>
            <td>Iuran BPJS Kesehatan (1%)</td>
            <td class="text-end bg-grey"></td>
            <td class="text-end">{{ number_format($d->potongan_bpjs_kesehatan, 0, ',', '.') }}</td>
        </tr>

        <tr>
            <td class="text-center" style="text-align: center">8</td>
            <td>Kasbon / Cicilan</td>
            <td class="text-end bg-grey"></td>
            <td class="text-end" style="color: red;">{{ number_format($d->kasbon, 0, ',', '.') }}</td>
        </tr>

        <tr class="bg-total">
            <td colspan="2" class="text-end">TOTAL</td>
            <td class="text-end">{{ number_format($d->pendapatan_kotor, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format($d->potongan_bpjs_tk + $d->potongan_bpjs_kesehatan + $d->kasbon, 0, ',', '.') }}</td>
        </tr>
    </tbody>
</table>

<table style="margin-top: 3px; border: 1px solid #000; background: #e9ecef;">
    <tr>
        <td width="60%" style="padding: 3px 5px; font-weight: bold; font-size: 8pt;">
            GAJI BERSIH (TAKE HOME PAY)
        </td>
        <td width="40%" class="text-end" style="padding: 3px 5px; font-weight: bold; font-size: 10pt;">
            Rp. {{ number_format($d->sisa_gaji_dibayar, 0, ',', '.') }}
        </td>
    </tr>
</table>

<table style="margin-top: 5px;">
    <tr>
        <td width="65%" class="valign-top">
            <div class="terbilang-box" style="font-size: 6.5pt; padding: 3px;">
                Terbilang: # {{ ucwords(App\Helpers\PayrollHelper::terbilang($d->sisa_gaji_dibayar)) }} Rupiah #
            </div>
            <div style="font-size: 6pt; color: #888; margin-top: 2px;">
                * Dicetak otomatis tanggal: {{ date('d/m/Y H:i') }}
            </div>
        </td>
        <td width="35%" class="text-center valign-top">
            <div style="font-size: 8pt; margin-bottom: 20px;">
                Admin Keuangan
            </div>
            <div style="font-weight: bold; text-decoration: underline; font-size: 8pt;">
                ( ....................... )
            </div>
        </td>
    </tr>
</table>
