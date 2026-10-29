@extends('layouts.app')
@section('content')
<div class="container-fluid ban-finance">
    @include('billing.uj-ditahan-ban.feedback')
    <div class="ban-hero"><div class="ban-hero-content">
        <div class="ban-hero-heading">@include('billing.uj-ditahan-ban.wheel')<div><span class="ban-period"><i class="fa fa-calendar" aria-hidden="true"></i> {{ \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y') }}</span><h3>UJ Ditahan Ban</h3><p>{{ $recap ? 'Telusuri periode saldo selesai dan riwayat penggunaan dana ban kendaraan.' : 'Pantau dana ban kendaraan, tinjau mutasi, dan kelola pencairan dalam satu tempat.' }}</p></div></div>
        <div class="d-flex gap-2 flex-wrap ban-hero-actions"><a href="{{ route($recap ? 'rekap.index' : 'billing.index') }}" class="btn btn-outline-light"><i class="fa fa-arrow-left me-2" aria-hidden="true"></i>Kembali</a><a href="{{ route($recap ? 'billing.uj-ditahan-ban' : 'rekap.uj-ditahan-ban') }}" class="btn btn-light"><i class="fa {{ $recap ? 'fa-credit-card' : 'fa-archive' }} me-2" aria-hidden="true"></i>{{ $recap ? 'Saldo aktif' : 'Rekap saldo selesai' }}</a></div>
    </div></div>
    @include('billing.uj-ditahan-ban.totals', ['masuk' => 0, 'keluar' => 0, 'saldo' => 0])
    <div class="ban-panel">
        <div class="ban-panel-head"><div class="d-flex align-items-center gap-3"><span class="ban-section-icon"><i class="fa {{ $recap ? 'fa-archive' : 'fa-truck' }}" aria-hidden="true"></i></span><div><h5 class="ban-section-title">{{ $recap ? 'Periode saldo selesai' : 'Saldo kendaraan' }}</h5><p class="ban-section-subtitle">{{ $recap ? 'Saldo nol, dengan seluruh mutasi tetap tersimpan.' : 'Pilih periode untuk melihat rincian dana setiap kendaraan.' }}</p></div></div><span class="ban-count"><i class="fa fa-list-ul me-1" aria-hidden="true"></i><span data-ban-count>0</span> data</span></div>
        <div class="ban-filter">
        <form class="row g-3 align-items-end" method="GET">
            <div class="col-6 col-md-3"><label class="form-label" for="bulan"><i class="fa fa-calendar-o me-1" aria-hidden="true"></i> Bulan</label><select name="bulan" id="bulan" class="form-select maintenance-select">@for($month = 1; $month <= 12; $month++)<option value="{{ $month }}" @selected($bulan === $month)>{{ \Carbon\Carbon::createFromDate(2026, $month, 1)->translatedFormat('F') }}{{ in_array($month, $activeBalanceMonths, true) ? ' ❗' : '' }}</option>@endfor</select></div>
            <div class="col-6 col-md-2"><label class="form-label" for="tahun"><i class="fa fa-calendar me-1" aria-hidden="true"></i> Tahun</label><select name="tahun" id="tahun" class="form-select maintenance-select">@for($year = max(now()->year + 1, $tahun); $year >= min(2020, $tahun); $year--)<option value="{{ $year }}" @selected($tahun === $year)>{{ $year }}</option>@endfor</select></div>
            <div class="col-md-auto"><button type="submit" class="btn btn-success"><i class="fa fa-filter me-2" aria-hidden="true"></i>Tampilkan periode</button></div>
        </form>
        <div class="small text-danger mt-3">❗ Menandakan bulan yang masih memiliki saldo tertahan pada tahun yang dipilih.</div>
        </div><div class="ban-table-wrap">
        <div class="small text-muted mb-2"><i class="fa fa-info-circle me-1" aria-hidden="true"></i> Gulir tabel untuk memuat data berikutnya.</div>
        <table class="table table-hover align-middle w-100" data-ban-table data-kind="accounts" data-url="{{ route($recap ? 'rekap.uj-ditahan-ban' : 'billing.uj-ditahan-ban', compact('bulan', 'tahun')) }}">
            <thead class="table-success"><tr><th>No</th><th>Nomor Lambung</th><th>Masuk</th><th>Keluar</th><th>Saldo</th><th>Aksi</th></tr></thead>
            <tbody></tbody><tfoot><tr><th colspan="2">Grand total hasil pencarian</th><th data-total-masuk></th><th data-total-keluar></th><th data-total-saldo></th><th></th></tr></tfoot>
        </table>
    </div></div>
</div>
@endsection
@include('billing.uj-ditahan-ban.assets')
