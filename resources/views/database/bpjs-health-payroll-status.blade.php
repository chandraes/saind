@if($bpjsSetting)
<div class="alert alert-light border mb-3"><strong>BPJS Kesehatan {{ $bpjsYear }}</strong> · Minimum {{ $bpjsSetting->basis }} Rp{{ number_format($bpjsSetting->minimum_wage, 0, ',', '.') }} · Maksimum Rp12.000.000. Dasar: gaji pokok + tunjangan jabatan + tunjangan keluarga.</div>
@else
<div class="alert alert-warning mb-3" role="alert"><strong>Dasar minimum BPJS Kesehatan {{ $bpjsYear }} belum diatur.</strong> Nominal BPJS pada pratinjau belum final; proses gaji diblokir. <a href="{{ route('bpjs-health.index', ['year' => $bpjsYear]) }}">Lengkapi pengaturan di Database</a>.</div>
@endif
