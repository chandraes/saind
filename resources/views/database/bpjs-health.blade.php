@extends('layouts.app')
@section('content')
<div class="container-fluid bpjs-page">
    <header class="bpjs-header">
        <div class="bpjs-heading"><div class="bpjs-brand-icon"><img src="{{ asset('images/bpjs-health.svg') }}" width="34" height="34" alt=""></div><div><div class="bpjs-eyebrow">Database · Penggajian</div><h1>Dasar minimum BPJS Kesehatan</h1><p>Kelola dasar iuran tahunan untuk perhitungan gaji yang konsisten.</p></div></div>
        <a class="btn bpjs-back" href="{{ route('database') }}"><i class="fa fa-arrow-left" aria-hidden="true"></i> Database</a>
    </header>
    <section class="bpjs-rules" aria-label="Ketentuan perhitungan">
        <div class="bpjs-rule"><span class="bpjs-rule-icon"><i class="fa fa-building-o" aria-hidden="true"></i></span><div><span>Bagian perusahaan</span><strong>4<span>%</span></strong></div></div>
        <div class="bpjs-rule"><span class="bpjs-rule-icon"><i class="fa fa-user-o" aria-hidden="true"></i></span><div><span>Bagian pegawai</span><strong>1<span>%</span></strong></div></div>
        <div class="bpjs-rule"><span class="bpjs-rule-icon"><i class="fa fa-shield" aria-hidden="true"></i></span><div><span>Batas atas dasar iuran</span><strong class="bpjs-cap">Rp12.000.000</strong></div></div>
    </section>
    <div class="bpjs-formula"><i class="fa fa-info-circle" aria-hidden="true"></i><p>Dasar iuran berasal dari <strong>gaji pokok + tunjangan jabatan + tunjangan keluarga</strong>, dengan batas minimum sesuai tahun dan maksimum Rp12 juta. Komisi dan BBM tidak termasuk. Pengaturan ini tidak mengubah BPJS-TK.</p></div>
    <div class="bpjs-layout">
        <section class="bpjs-card bpjs-editor" aria-labelledby="bpjs-form-title">
            <div class="bpjs-card-heading"><div><h2 id="bpjs-form-title">Pengaturan tahun {{ $year }}</h2><p>Isi nilai sesuai ketetapan yang berlaku.</p></div><span class="bpjs-status {{ $setting ? 'is-saved' : 'is-empty' }}"><i class="fa {{ $setting ? 'fa-check-circle' : 'fa-clock-o' }}" aria-hidden="true"></i> {{ $setting ? 'Tersimpan' : 'Belum diatur' }}</span></div>
            <form method="get" class="bpjs-year-filter"><label for="filter-year"><i class="fa fa-calendar-o" aria-hidden="true"></i> Tahun berlaku</label><div class="bpjs-year-controls"><input id="filter-year" name="year" type="number" class="form-control" min="1901" max="2155" value="{{ $year }}" required><button class="btn bpjs-year-button" type="submit"><i class="fa fa-search" aria-hidden="true"></i> Tampilkan</button></div></form>
            <form method="post" action="{{ route('bpjs-health.store') }}" id="bpjs-health-form" class="bpjs-form">@csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <div class="bpjs-field"><label for="basis" class="form-label">Jenis dasar upah <span aria-hidden="true">*</span></label><select id="basis" name="basis" class="form-select" required><option value="UMK" @selected(old('basis', $setting?->basis) === 'UMK')>UMK — Kabupaten/Kota</option><option value="UMP" @selected(old('basis', $setting?->basis) === 'UMP')>UMP — Provinsi</option></select></div>
                <div class="bpjs-field"><label for="minimum-wage" class="form-label">Dasar minimum {{ $year }} <span aria-hidden="true">*</span></label><div class="bpjs-currency"><span>Rp</span><input id="minimum-wage" name="minimum_wage_display" type="text" inputmode="numeric" autocomplete="off" class="form-control" value="{{ old('minimum_wage', $setting?->minimum_wage) }}" required placeholder="0" aria-describedby="minimum-wage-help"></div><input id="minimum-wage-raw" type="hidden" name="minimum_wage" value="{{ old('minimum_wage', $setting?->minimum_wage) }}"><div class="form-text" id="minimum-wage-help">Nominal rupiah penuh, maksimal Rp12.000.000.</div></div>
                <div class="bpjs-field"><label for="reference" class="form-label">Referensi penetapan <span aria-hidden="true">*</span></label><textarea id="reference" name="reference" class="form-control" rows="3" minlength="5" maxlength="2000" required placeholder="Contoh: nomor keputusan dan/atau tautan sumber resmi" aria-describedby="reference-help">{{ old('reference', $setting?->reference) }}</textarea><div class="form-text" id="reference-help"><i class="fa fa-file-text-o" aria-hidden="true"></i> Memudahkan pemeriksaan kembali dasar yang digunakan.</div></div>
                <div class="bpjs-save-area"><button class="btn bpjs-save" type="submit"><i class="fa fa-check" aria-hidden="true"></i> Simpan pengaturan {{ $year }}</button><p><i class="fa fa-lock" aria-hidden="true"></i> Hanya admin dan super user yang dapat mengubah.</p></div>
            </form>
        </section>
        <section class="bpjs-card bpjs-history" aria-labelledby="bpjs-history-title">
            <div class="bpjs-card-heading"><div><h2 id="bpjs-history-title">Pengaturan tersimpan</h2><p>Dasar iuran untuk setiap tahun penggajian.</p></div><span class="bpjs-count">{{ $settings->count() }} tahun</span></div>
            <div class="bpjs-table-area"><table id="bpjs-settings-table" class="table align-middle"><thead class="table-success"><tr><th>Tahun</th><th>Dasar</th><th class="text-end">Minimum</th><th>Referensi</th><th class="text-center">Aksi</th></tr></thead><tbody>@foreach($settings as $row)<tr><td class="fw-bold">{{ $row->year }}</td><td><span class="bpjs-basis">{{ $row->basis }}</span></td><td class="bpjs-table-money">Rp{{ number_format($row->minimum_wage, 0, ',', '.') }}</td><td class="bpjs-reference">{{ $row->reference }}</td><td class="text-center"><a class="btn bpjs-edit" href="{{ route('bpjs-health.index', ['year' => $row->year]) }}" aria-label="Ubah pengaturan {{ $row->year }}"><i class="fa fa-pencil" aria-hidden="true"></i> Ubah</a></td></tr>@endforeach</tbody></table></div>
            <footer class="bpjs-history-note"><i class="fa fa-history" aria-hidden="true"></i><p><strong>Rekap gaji lama tetap utuh.</strong> Perubahan digunakan untuk perhitungan baru sesuai tahun periode gaji. Eksekusi gaji diblokir jika pengaturan tahun tersebut belum tersedia.</p></footer>
        </section>
    </div>
    @if($errors->any() || session('success'))<div id="bpjs-feedback" hidden data-error="{{ $errors->any() ? 'true' : 'false' }}" data-message="{{ $errors->any() ? $errors->first() : session('success') }}"></div>@endif
</div>
@endsection
@push('css')<link rel="stylesheet" href="{{ asset('bpjs-health-settings.css').'?v='.filemtime(public_path('bpjs-health-settings.css')) }}">@endpush
@push('js')<script src="{{ asset('bpjs-health-settings.js').'?v='.filemtime(public_path('bpjs-health-settings.js')) }}" defer></script>@endpush
