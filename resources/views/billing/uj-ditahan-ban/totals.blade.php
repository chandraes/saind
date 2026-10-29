<div class="row g-3 mb-4">
    @foreach(['masuk' => ['Total masuk', $masuk, 'fa-arrow-down', 'Akumulasi dana diterima'], 'keluar' => ['Total keluar', $keluar, 'fa-arrow-up', 'Akumulasi dana digunakan'], 'saldo' => ['Saldo periode', $saldo, 'fa-credit-card', 'Sisa saldo pada periode ini']] as $key => $item)
    <div class="col-md-4"><div class="ban-kpi ban-kpi--{{ $key }}"><div class="d-flex justify-content-between align-items-start gap-2"><div class="flex-grow-1"><div class="ban-kpi-label">{{ $item[0] }}</div><div class="ban-kpi-value" data-card-{{ $key }}>Rp {{ number_format($item[1], 0, ',', '.') }}</div></div><span class="ban-kpi-icon" aria-hidden="true"><i class="fa {{ $item[2] }}"></i></span></div><div class="ban-kpi-note"><i class="fa fa-check-circle" aria-hidden="true"></i> {{ $item[3] }}</div></div></div>
    @endforeach
</div>
