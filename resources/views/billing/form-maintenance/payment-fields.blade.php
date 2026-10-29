<div class="row g-3 mb-3">
    <div class="col-12">
        <label for="pembayaran" class="form-label fw-semibold">Metode pembayaran</label>
        <select id="pembayaran" name="pembayaran" class="form-select maintenance-select" data-payment-method required>
            <option value="" disabled @selected(!old('pembayaran'))>Pilih metode pembayaran</option>
            <option value="uj_ditahan" @selected(old('pembayaran') === 'uj_ditahan')>UJ Ditahan Sparepart</option>
            @if ($kasBesarEnabled)
                <option value="kas_besar" @selected(old('pembayaran') === 'kas_besar')>Kas Besar</option>
            @endif
            @if (config('maintenance.dibayar_sendiri_visible'))
                <option value="dibayar_sendiri" @selected(old('pembayaran') === 'dibayar_sendiri')>Dibayar Sendiri</option>
            @endif
        </select>
    </div>
    <div @class(['col-12', 'd-none' => old('pembayaran') !== 'uj_ditahan']) data-uj-balance @if(old('pembayaran') !== 'uj_ditahan') hidden @endif>
        <div class="border rounded-3 bg-white p-3 h-100">
            <span class="small text-muted d-block">Saldo UJ Ditahan Sparepart kendaraan saat ini</span>
            <strong class="fs-5 {{ $ujDitahanBalance > 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($ujDitahanBalance, 0, ',', '.') }}</strong>
            <span class="small text-muted d-block">Saldo diperiksa ulang dan dipotong saat disetujui, mulai dari bulan paling lama.</span>
            @if ($ujDitahanBalance <= 0)<span class="small text-danger d-block">Belum ada saldo yang dapat digunakan.</span>@endif
        </div>
    </div>
</div>
<div data-bank-fields @class(['row g-3 mb-4', 'd-none' => !in_array(old('pembayaran'), ['kas_besar', 'uj_ditahan'], true)]) @if(!in_array(old('pembayaran'), ['kas_besar', 'uj_ditahan'], true)) hidden @endif>
    <div class="col-12">
        <label for="nominal_display" class="form-label">Total nominal (Rp)</label>
        <div class="input-group"><span class="input-group-text">Rp</span><input id="nominal_display" type="text" inputmode="numeric" autocomplete="off" value="{{ old('total_nominal') }}" class="form-control" data-maintenance-money aria-describedby="nominalHelp" required></div><div id="nominalHelp" class="form-text">Rupiah penuh, contoh: 1.500.000.</div>
        <input type="hidden" name="total_nominal" data-maintenance-money-value value="{{ old('total_nominal') }}">
    </div>
    <div class="col-12"><label for="bank" class="form-label">Bank tujuan</label><input id="bank" name="nama_bank" maxlength="50" value="{{ old('nama_bank') }}" class="form-control" required></div>
    <div class="col-12"><label for="rekening" class="form-label">Nomor rekening</label><input id="rekening" name="nomor_rekening" maxlength="50" value="{{ old('nomor_rekening') }}" class="form-control" required></div>
    <div class="col-12"><label for="atasNama" class="form-label">Atas nama</label><input id="atasNama" name="nama_rekening" maxlength="100" value="{{ old('nama_rekening') }}" class="form-control" required></div>
</div>
