<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Biodata {{ $karyawan->nama }}</title>
    <style>
        @page { margin: 24pt 28pt; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8pt; color: #263b34; line-height: 1.2; }
        table { width: 100%; border-collapse: collapse; }
        .header { border-bottom: 2pt solid #21845a; padding-bottom: 8pt; margin-bottom: 6pt; }
        .company { font-size: 10pt; color: #21845a; font-weight: bold; }
        h1 { font-size: 18pt; margin: 4pt 0; color: #203d31; }
        .muted { color: #748078; font-size: 8pt; }
        .layout > tbody > tr > td { vertical-align: top; }
        .info { width: 66%; padding-right: 24pt; }
        .documents { width: 34%; }
        .section { background: #edf5f0; color: #21734f; padding: 3pt 7pt; font-weight: bold; font-size: 9pt; margin: 0 0 4pt; }
        .fields { margin-bottom: 6pt; table-layout: fixed; }
        .fields td { padding: 2pt 5pt; vertical-align: top; overflow-wrap: break-word; }
        .label { width: 34%; color: #6e7873; }
        .value { width: 66%; }
        .photo-box { border: 1pt solid #dce7df; background: #fbfcfb; text-align: center; padding: 9pt; margin-bottom: 12pt; }
        .photo-title { font-weight: bold; margin-bottom: 7pt; font-size: 9pt; }
        .photo-box img { max-width: 215pt; max-height: 130pt; }
        .empty { padding: 45pt 0; font-size: 8pt; color: #89958e; }
        .footer { position: fixed; bottom: -10pt; left: 0; right: 0; border-top: 1pt solid #dce7df; padding-top: 5pt; font-size: 7pt; color: #89958e; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">{{ $global_app_perusahaan ?? 'PT SAIND' }}</div>
        <h1>Biodata Pegawai</h1>
        <div class="muted">{{ $karyawan->kode }}{{ $karyawan->nomor }} &nbsp; | &nbsp; {{ $karyawan->jabatan?->nama ?? 'Jabatan belum tersedia' }} &nbsp; | &nbsp; {{ $karyawan->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}</div>
    </div>
    <table class="layout"><tbody><tr>
        <td class="info">
            @php($sections = [
                'Identitas Pegawai' => ['Nama lengkap' => $karyawan->nama, 'Nama panggilan' => $karyawan->nickname, 'NIK' => $karyawan->nik, 'NPWP' => $karyawan->npwp, 'Tempat / tanggal lahir' => $karyawan->tempat_lahir.', '.$dates['lahir']],
                'Kontak & Alamat' => ['Alamat' => $karyawan->alamat, 'Nomor HP' => $karyawan->no_hp, 'Nomor WhatsApp' => $karyawan->no_wa],
                'Kepesertaan BPJS' => ['BPJS Ketenagakerjaan' => ($karyawan->apa_bpjs_tk ? 'Terdaftar - ' : 'Tidak terdaftar - ').($karyawan->bpjs_tk ?: '-'), 'BPJS Kesehatan' => ($karyawan->apa_bpjs_kesehatan ? 'Terdaftar - ' : 'Tidak terdaftar - ').($karyawan->bpjs_kesehatan ?: '-')],
                'Rekening Bank' => ['Nama bank' => $karyawan->bank, 'Nomor rekening' => $karyawan->no_rekening, 'Pemilik rekening' => $karyawan->nama_rekening],
                'Kepegawaian' => ['Mulai bekerja' => $dates['mulai'], 'Status' => $karyawan->status === 'aktif' ? 'Aktif' : 'Nonaktif', 'Perjanjian kerja' => $karyawan->perjanjian_kerja ? 'Tersedia (buka dari detail web pegawai)' : 'Belum tersedia']
            ])
            @foreach ($sections as $title => $fields)
                <div class="section">{{ $title }}</div>
                <table class="fields"><tbody>@foreach ($fields as $label => $value)<tr><td class="label">{{ $label }}</td><td class="value">{{ $value ?: '-' }}</td></tr>@endforeach</tbody></table>
            @endforeach
        </td>
        <td class="documents">
            @foreach (['foto_diri' => 'Foto Diri', 'foto_ktp' => 'Foto KTP'] as $field => $label)
                <div class="photo-box"><div class="photo-title">{{ $label }}</div>@if ($photos[$field])<img src="{{ $photos[$field] }}" alt="{{ $label }}">@else<div class="empty">{{ $label }} belum tersedia</div>@endif</div>
            @endforeach
        </td>
    </tr></tbody></table>
    <div class="footer">Biodata Pegawai &nbsp; | &nbsp; {{ $karyawan->kode }}{{ $karyawan->nomor }} &nbsp; | &nbsp; {{ $karyawan->nama }} @if($karyawan->perjanjian_kerja) | <a href="{{ route('karyawan.document', ['karyawan' => $karyawan->id, 'document' => 'perjanjian_kerja']) }}">Perjanjian kerja (login SAIND)</a>@endif</div>
</body>
</html>
