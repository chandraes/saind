@extends('layouts.app')
@section('content')
<div class="container-fluid ban-finance">
    @include('billing.uj-ditahan-ban.feedback')
    <div class="ban-hero"><div class="ban-hero-content">
        <div class="ban-hero-heading">@include('billing.uj-ditahan-ban.wheel')<div><span class="ban-period"><i class="fa fa-calendar" aria-hidden="true"></i>{{ \Carbon\Carbon::createFromDate($account->tahun, $account->bulan, 1)->translatedFormat('F Y') }}</span><h3>UJ Ditahan Ban · {{ $account->vehicle?->nomor_lambung ?? '-' }}</h3><p><i class="fa fa-user-circle-o me-2" aria-hidden="true"></i>Driver: {{ $account->vehicle?->driver?->nama ?? '-' }}</p></div></div>
        <div class="d-flex gap-2 flex-wrap ban-hero-actions"><a class="btn btn-outline-light" href="{{ route('billing.uj-ditahan-ban', ['bulan' => $account->bulan, 'tahun' => $account->tahun]) }}"><i class="fa fa-arrow-left me-2" aria-hidden="true"></i>Kembali</a>
            @if($account->saldo > 0)<button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#withdraw-ban"><i class="fa fa-exchange me-2" aria-hidden="true"></i>Cairkan saldo</button>@endif

        </div>
    </div></div>
    @include('billing.uj-ditahan-ban.totals', ['masuk' => $account->total_masuk, 'keluar' => $account->total_keluar, 'saldo' => $account->saldo])
    <div class="ban-panel"><div class="ban-panel-head"><div class="d-flex align-items-center gap-3"><span class="ban-section-icon"><i class="fa fa-history" aria-hidden="true"></i></span><div><h5 class="ban-section-title">Riwayat mutasi</h5><p class="ban-section-subtitle">Transaksi terbaru ditampilkan lebih dahulu. Gunakan pencarian untuk menelusuri uraian atau rekening.</p></div></div><span class="ban-count"><i class="fa fa-list-ul me-1" aria-hidden="true"></i><span data-ban-count>0</span> mutasi</span></div><div class="ban-table-wrap">
        <table class="table table-hover align-middle w-100" data-ban-table data-kind="details" data-url="{{ route('billing.uj-ditahan-ban.show', $account) }}"><thead class="table-success"><tr><th>No</th><th>Tanggal</th><th>Uraian</th><th>Driver</th><th>Nominal Masuk</th><th>Nominal Keluar</th><th>Rekening</th><th>Bukti</th></tr></thead><tbody></tbody><tfoot><tr><th colspan="4">Grand total hasil pencarian</th><th data-total-masuk></th><th data-total-keluar></th><th colspan="2"></th></tr></tfoot></table>
    </div></div>
</div>
@if($account->saldo > 0)
<div class="modal fade ban-modal" id="withdraw-ban" tabindex="-1" aria-labelledby="withdraw-ban-title" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content border-0 shadow"><div class="modal-header"><h5 class="modal-title fw-bold" id="withdraw-ban-title"><i class="fa fa-exchange" aria-hidden="true"></i>Pencairan UJ Ditahan Ban</h5><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
<form method="POST" action="{{ route('billing.uj-ditahan-ban.cairkan', $account) }}" enctype="multipart/form-data" data-maintenance-form data-confirm="Catat pencairan dan potong saldo UJ Ditahan Ban periode ini?" novalidate>@csrf
<input type="hidden" name="request_token" value="{{ \Illuminate\Support\Str::uuid() }}">
<div class="modal-body p-4"><div class="bg-success bg-opacity-10 rounded p-3 mb-4">Saldo periode tersedia: <strong>Rp {{ number_format($account->saldo, 0, ',', '.') }}</strong><div class="small text-muted mt-1">Habiskan saldo bulan sebelumnya terlebih dahulu. Saldo diperiksa ulang saat disimpan.</div></div><div class="row g-3">
<div class="col-md-6"><label class="form-label" for="nominal-ban">Nominal pencairan <span class="text-danger">*</span></label><div class="input-group"><span class="input-group-text">Rp</span><input id="nominal-ban" class="form-control" data-maintenance-money inputmode="numeric" required autocomplete="off" value="{{ old('nominal') }}"></div><input type="hidden" name="nominal" data-maintenance-money-value value="{{ old('nominal') }}"></div>
<div class="col-md-6"><label class="form-label" for="bukti-ban">Bukti PDF <span class="text-danger">*</span></label><input id="bukti-ban" name="bukti_pdf" type="file" accept="application/pdf,.pdf" class="form-control" required><div class="form-text">Maksimal 5 MB.</div></div>
<div class="col-md-4"><label for="bank-ban" class="form-label">Bank tujuan <span class="text-danger">*</span></label><input id="bank-ban" name="bank" class="form-control" maxlength="100" required value="{{ old('bank') }}"></div>
<div class="col-md-4"><label for="rekening-ban" class="form-label">Nomor rekening <span class="text-danger">*</span></label><input id="rekening-ban" name="no_rekening" class="form-control" maxlength="100" inputmode="numeric" required value="{{ old('no_rekening') }}"></div>
<div class="col-md-4"><label for="nama-ban" class="form-label">Atas nama <span class="text-danger">*</span></label><input id="nama-ban" name="nama_rekening" class="form-control" maxlength="255" required value="{{ old('nama_rekening') }}"></div>
<div class="col-12"><label for="uraian-ban" class="form-label">Uraian <span class="text-danger">*</span></label><textarea id="uraian-ban" name="keterangan" class="form-control" rows="2" maxlength="1000" required>{{ old('keterangan') }}</textarea></div>
</div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success"><i class="fa fa-check-circle me-2" aria-hidden="true"></i>Simpan pencairan</button></div>
</form></div></div></div>
@endif

@endsection
@include('billing.uj-ditahan-ban.assets')
