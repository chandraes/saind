@php
    $title = $isWithdrawal ? 'Pengembalian Deposit' : 'Penambahan Deposit';
    $submitLabel = $isWithdrawal ? 'Simpan Pengembalian' : 'Simpan Deposit';
@endphp
@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/kas-besar-deposit.css') }}">
@endpush
<div class="container deposit-page {{ $isWithdrawal ? 'deposit-page--withdrawal' : '' }}">
    <nav aria-label="Breadcrumb" class="deposit-breadcrumb"><a href="{{ route('billing.index') }}">Billing</a><i class="fa fa-angle-right" aria-hidden="true"></i><span>{{ $title }}</span></nav>
    @include('swal')
    <section class="deposit-shell" aria-labelledby="depositTitle">
        <header class="deposit-header">
            <div><div class="deposit-eyebrow">KAS BESAR · {{ $isWithdrawal ? 'DANA KELUAR' : 'DANA MASUK' }}</div><h1 id="depositTitle">{{ $title }}</h1><p>{{ $isWithdrawal ? 'Periksa saldo, isi nominal, lalu pastikan rekening penerima.' : 'Periksa kode, isi nominal, lalu pastikan rekening tujuan.' }}</p></div>
            <div class="deposit-header-icon" aria-hidden="true"><i class="fa {{ $isWithdrawal ? 'fa-reply' : 'fa-money' }}"></i></div>
        </header>
        <form action="{{ route($isWithdrawal ? 'kas-besar.keluar.store' : 'kas-besar.masuk.store') }}" method="post" id="depositForm">
            @csrf
            <input type="hidden" name="request_token" value="{{ old('request_token', (string) \Illuminate\Support\Str::uuid()) }}">
            <input type="hidden" name="nominal_transaksi" id="nominal_transaksi" value="{{ old('nominal_transaksi') }}">
            <div class="deposit-body">
                <div class="deposit-section-title"><span class="deposit-section-icon"><i class="fa fa-file-text-o" aria-hidden="true"></i></span><div><h2>Detail deposit</h2><p>Informasi transaksi yang akan dicatat.</p></div></div>
                <div class="deposit-fields">
                    @if ($isWithdrawal)
                    <div class="deposit-field"><label for="saldo_tersedia">Saldo Tersedia</label><div class="input-group"><span class="input-group-text">Rp</span><input id="saldo_tersedia" class="form-control deposit-code" value="{{ number_format($saldo, 0, ',', '.') }}" readonly aria-describedby="saldoHelp"></div><div id="saldoHelp" class="deposit-help">Saldo dapat berubah sebelum disimpan.</div></div>
                    @else
                    <div class="deposit-field"><label for="kode_deposit">Kode Deposit</label><input id="kode_deposit" class="form-control deposit-code" value="D{{ sprintf('%02d', $nomor) }}" readonly aria-describedby="kodeHelp"><div id="kodeHelp" class="deposit-help">Nomor final ditentukan saat disimpan.</div></div>
                    @endif
                    <div class="deposit-field"><label for="uraian">Uraian</label><input id="uraian" class="form-control" value="{{ $isWithdrawal ? 'Withdraw' : 'Deposit' }}" readonly></div>
                    <div class="deposit-field"><label for="nominal_display">{{ $isWithdrawal ? 'Nominal Pengembalian' : 'Nominal Deposit' }} <span class="text-danger">*</span></label>
                        <div class="input-group deposit-amount"><span class="input-group-text">Rp</span><input id="nominal_display" class="form-control @error('nominal_transaksi') is-invalid @enderror" type="text" inputmode="numeric" autocomplete="off" placeholder="0" required value="{{ old('nominal_transaksi') }}" aria-describedby="nominalHelp"></div>
                        <div id="nominalHelp" class="deposit-help">Rupiah bulat, lebih dari nol.</div>
                        @error('nominal_transaksi')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
                <section class="deposit-account" aria-labelledby="accountTitle">
                    <div class="deposit-section-title"><span class="deposit-section-icon"><i class="fa fa-university" aria-hidden="true"></i></span><div><h2 id="accountTitle">Rekening tujuan</h2><p>{{ $isWithdrawal ? 'Dana dikembalikan ke rekening penerima berikut.' : 'Dana ditransfer ke rekening Kas Besar berikut.' }}</p></div></div>
                    @if ($rekening)
                    <dl class="deposit-account-details"><div><dt>Bank</dt><dd>{{ $rekening->nama_bank }}</dd></div><div><dt>Nomor rekening</dt><dd class="deposit-account-number">{{ $rekening->nomor_rekening }}</dd></div><div><dt>Atas nama</dt><dd>{{ $rekening->nama_rekening }}</dd></div></dl>
                    @else
                    <div class="deposit-account-empty" role="alert"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> {{ $isWithdrawal ? 'Rekening pengembalian deposit belum diatur.' : 'Rekening Kas Besar belum diatur.' }} Silakan hubungi admin.</div>
                    @endif
                </section>
                <div class="deposit-notice"><i class="fa fa-info-circle" aria-hidden="true"></i><p>Saldo Kas Besar langsung {{ $isWithdrawal ? 'berkurang setelah pengembalian' : 'bertambah setelah deposit' }} disimpan. Pastikan data sudah benar sebelum melanjutkan.</p></div>
            </div>
            <footer class="deposit-footer"><p><i class="fa fa-check-circle-o" aria-hidden="true"></i> Periksa sekali lagi sebelum menyimpan.</p><div class="deposit-actions"><a href="{{ route('billing.index') }}" class="btn deposit-cancel">Batal</a><button id="depositSubmit" class="btn deposit-submit" type="submit" @disabled(! $rekening)><i class="fa fa-check-circle" aria-hidden="true"></i> {{ $submitLabel }}</button></div></footer>
        </form>
    </section>
</div>
@push('js')
<script>
$(function () {
    const form = document.getElementById('depositForm');
    const button = document.getElementById('depositSubmit');
    const canonical = document.getElementById('nominal_transaksi');
    const cleave = new Cleave('#nominal_display', {numeral: true, numeralThousandsGroupStyle: 'thousand', numeralDecimalScale: 0, numeralPositiveOnly: true, numeralDecimalMark: ',', delimiter: '.'});
    let confirming = false;
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        if (confirming) { return; }
        canonical.value = cleave.getRawValue();
        if (!/^[1-9][0-9]*$/.test(canonical.value)) {
            Swal.fire({icon: 'warning', title: 'Nominal belum valid', text: 'Masukkan nominal rupiah bulat lebih dari nol.'});
            return;
        }
        confirming = true;
        Swal.fire({title: @json($isWithdrawal ? 'Simpan pengembalian deposit?' : 'Simpan deposit?'), text: @json($isWithdrawal ? 'Pengembalian Rp ' : 'Deposit Rp ') + document.getElementById('nominal_display').value + @json($isWithdrawal ? ' akan langsung mengurangi saldo Kas Besar.' : ' akan langsung menambah saldo Kas Besar.'), icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, simpan', cancelButtonText: 'Periksa kembali', confirmButtonColor: '#198754'}).then(function (result) {
            if (result.isConfirmed) {
                button.disabled = true;
                $('#spinner').show();
                HTMLFormElement.prototype.submit.call(form);
            } else { confirming = false; }
        });
    });
});
</script>
@endpush
