@extends('layouts.app')
@section('content')
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><div class="text-success small fw-bold text-uppercase mb-1">Biodata pegawai</div><h2 class="fw-bold mb-1">{{ $karyawan->nama }}</h2><p class="text-muted mb-0">{{ $karyawan->jabatan?->nama ?? 'Jabatan belum tersedia' }} · {{ $karyawan->kode }}{{ $karyawan->nomor }}</p></div>
        <div class="d-flex flex-wrap gap-2"><a href="{{ route('karyawan.index') }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Daftar Pegawai</a><a href="{{ route('karyawan.show', $karyawan) }}" target="_blank" rel="noopener" class="btn btn-outline-primary"><i class="fa fa-file-pdf-o me-1"></i> Biodata PDF</a><a href="{{ route('karyawan.edit', $karyawan) }}" class="btn btn-success"><i class="fa fa-pencil me-1"></i> Ubah Pegawai</a></div>
    </div>
    <div class="row g-4">
        <div class="col-lg-4"><div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4 text-center">
            @if ($photos['foto_diri'])<img src="{{ asset('storage/'.preg_replace('#^public/#', '', $karyawan->foto_diri)) }}" class="rounded-3 img-fluid mb-3" style="max-height:240px;object-fit:contain" alt="Foto {{ $karyawan->nama }}">@else<div class="rounded-3 bg-light p-5 mb-3 text-muted"><i class="fa fa-user fa-4x" aria-hidden="true"></i><p class="small mt-3 mb-0">Foto diri belum tersedia</p></div>@endif
            <h5 class="fw-bold mb-1">{{ $karyawan->nickname }}</h5><p class="text-muted small">Mulai bekerja {{ $dates['mulai'] }}</p><span class="badge rounded-pill px-3 py-2 {{ $karyawan->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}">{{ $karyawan->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}</span>
        </div></div></div>
        <div class="col-lg-8">
            @php($sections = [
                'Identitas & Kontak' => ['Nama lengkap' => $karyawan->nama, 'Nama panggilan' => $karyawan->nickname, 'NIK' => $karyawan->nik, 'NPWP' => $karyawan->npwp, 'Tempat / tanggal lahir' => $karyawan->tempat_lahir.', '.$dates['lahir'], 'Alamat' => $karyawan->alamat, 'Nomor HP' => $karyawan->no_hp, 'Nomor WhatsApp' => $karyawan->no_wa],
                'Gaji & Tunjangan' => ['Gaji pokok' => 'Rp'.number_format($karyawan->gaji_pokok, 0, ',', '.'), 'Tunjangan jabatan' => 'Rp'.number_format($karyawan->tunjangan_jabatan, 0, ',', '.'), 'Tunjangan keluarga' => 'Rp'.number_format($karyawan->tunjangan_keluarga, 0, ',', '.')],
                'Kepesertaan BPJS' => ['BPJS Ketenagakerjaan' => ($karyawan->apa_bpjs_tk ? 'Terdaftar · ' : 'Tidak terdaftar · ').($karyawan->bpjs_tk ?: '-'), 'BPJS Kesehatan' => ($karyawan->apa_bpjs_kesehatan ? 'Terdaftar · ' : 'Tidak terdaftar · ').($karyawan->bpjs_kesehatan ?: '-')],
                'Rekening Bank' => ['Bank' => $karyawan->bank, 'Nomor rekening' => $karyawan->no_rekening, 'Nama pemilik rekening' => $karyawan->nama_rekening]
            ])
            @foreach ($sections as $title => $fields)
                <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4"><h5 class="fw-bold mb-4">{{ $title }}</h5><dl class="row mb-0 g-3">@foreach ($fields as $label => $value)<dt class="col-sm-4 text-muted fw-normal">{{ $label }}</dt><dd class="col-sm-8 mb-0 text-break">{{ $value ?: '—' }}</dd>@endforeach</dl></div></div>
            @endforeach
            <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4"><h5 class="fw-bold mb-3">Dokumen KTP</h5>@if ($photos['foto_ktp'])<img src="{{ asset('storage/'.preg_replace('#^public/#', '', $karyawan->foto_ktp)) }}" class="img-fluid rounded-3" style="max-height:320px;object-fit:contain" alt="Foto KTP {{ $karyawan->nama }}">@else<p class="text-muted mb-0">Foto KTP belum tersedia.</p>@endif</div></div>
        </div>
    </div>
</div>
@endsection
