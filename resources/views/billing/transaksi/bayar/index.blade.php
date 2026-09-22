@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Nota Bayar</u></h1>
            <h2><u>{{$vendor->nama}}</u></h2>
        </div>
    </div>

    @php
    $total_tagihan = $dataTersedia ? $dataTersedia->sum('nominal_bayar') : 0;
    $ppn = $vendor->ppn == 1 && $dataTersedia ? floor($dataTersedia->sum('nominal_bayar') * 0.11) : 0;
    $pph = $vendor->pph == 1 && $dataTersedia ? floor($dataTersedia->sum('nominal_bayar') * ($vendor->pph_val/100)) : 0;
    $total_uang_jalan = $dataTersedia ? $dataTersedia->sum('kas_uang_jalan.nominal_transaksi') : 0;
    $total_netto = $dataTersedia ? $total_tagihan - $dataTersedia->sum('kas_uang_jalan.nominal_transaksi') : 0;
    $grant_total = $total_netto - $pph + $ppn;
    @endphp

    @include('swal')

    <div class="row justify-content-between align-items-center mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    @if (auth()->user()->role != 'asisten-user' && auth()->user()->role != 'vendor')
                    <td><a href="{{route('billing.index')}}"><img src="{{asset('images/billing.svg')}}" alt="dokumen"
                                width="30"> Billing</a></td>
                    @endif
                    <td><a href="{{route('billing.nota-bayar', ['vendor' => $vendor->id])}}"><img
                                src="{{asset('images/back.svg')}}" alt="dokumen" width="30"> Kembali</a></td>
                </tr>
            </table>
        </div>

        <div class="col-md-6 text-end mb-3">
            @if($cartCount > 0)
            <a href="{{ route('transaksi.nota-bayar.keranjang', $vendor) }}" class="btn btn-primary position-relative">
                <i class="fa fa-shopping-cart me-1"></i> Lihat Keranjang
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ $cartCount }}
                </span>
            </a>
            @else
            <button class="btn btn-secondary position-relative" disabled title="Keranjang masih kosong">
                <i class="fa fa-shopping-cart me-1"></i> Lihat Keranjang
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary">
                    0
                </span>
            </button>
            @endif
        </div>
    </div>
</div>

<div class="container-fluid mt-2 mb-5">
    <div class="card border-secondary">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            {{-- <h5 class="mb-0"><i class="fa fa-list me-2"></i> Transaksi Belum Dipilih ({{ $dataTersedia->count() }})</h5>

            @if(auth()->user()->role !== 'vendor' && $dataTersedia->count() > 0)
            <form action="{{ route('transaksi.nota-bayar.keranjang-semua', $vendor) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fa fa-check-double me-1"></i> Masukkan Semua ke Keranjang
                </button>
            </form>
            @endif --}}
        </div>

        <div class="card-body table-responsive">
            <form action="{{ route('transaksi.nota-bayar.masuk-keranjang', $vendor) }}" method="POST"
                id="formMasukKeranjang" autocomplete="off">
                @csrf

                @if(auth()->user()->role !== 'vendor' && $dataTersedia->count() > 0)
                <!-- Summary Card Item Terpilih -->
                <div class="card border-primary shadow-sm mb-4">
                    <div class="card-body bg-light p-3">
                        <div class="row g-2 align-items-center">

                            <!-- Grid Rincian Angka (Responsif untuk Tablet & Mobile) -->
                            <div class="col-12 col-xl-9">
                                <div class="row g-2 text-center text-md-start">

                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                        <div class="p-2 bg-white rounded border h-100">
                                            <small class="text-muted d-block text-truncate">Terpilih</small>
                                            <span class="fw-bold fs-6 text-primary"><span id="selectedCount">0</span>
                                                Data</span>
                                        </div>
                                    </div>

                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                        <div class="p-2 bg-white rounded border h-100">
                                            <small class="text-muted d-block text-truncate">Bruto</small>
                                            <span class="fw-bold fs-6 text-dark">Rp <span id="selBruto">0</span></span>
                                        </div>
                                    </div>

                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                        <div class="p-2 bg-white rounded border h-100">
                                            <small class="text-muted d-block text-truncate">Uang Jalan</small>
                                            <span class="fw-bold fs-6 text-danger">Rp <span
                                                    id="selUangJalan">0</span></span>
                                        </div>
                                    </div>

                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                        <div class="p-2 bg-white rounded border h-100">
                                            <small class="text-muted d-block text-truncate">Netto</small>
                                            <span class="fw-bold fs-6 text-dark">Rp <span id="selNetto">0</span></span>
                                        </div>
                                    </div>

                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                        <div class="p-2 bg-white rounded border h-100">
                                            <small class="text-muted d-block text-truncate">PPN (+)</small>
                                            <span class="fw-bold fs-6 text-secondary">Rp <span
                                                    id="selPpn">0</span></span>
                                        </div>
                                    </div>

                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                        <div class="p-2 bg-white rounded border h-100">
                                            <small class="text-muted d-block text-truncate">PPh (-)</small>
                                            <span class="fw-bold fs-6 text-secondary">Rp <span
                                                    id="selPph">0</span></span>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- Grand Total Highlight & Tombol Action -->
                            <div class="col-12 col-xl-3 mt-3 mt-xl-0">
                                <div class="d-flex d-md-block justify-content-between align-items-center gap-2">
                                    <div class="p-2 bg-success text-white rounded mb-md-2 flex-grow-1 text-center">
                                        <small class="d-block text-white-50 fw-semibold"
                                            style="font-size: 0.75rem;">GRAND TOTAL</small>
                                        <strong class="fs-6">Rp <span id="selGrandTotal">0</span></strong>
                                    </div>
                                    <button type="submit" class="btn btn-primary py-2 px-3 w-100" id="btnSubmitSelected"
                                        disabled>
                                        <i class="fa fa-plus me-1"></i> Masukkan Keranjang
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                @endif

                <table class="table table-bordered table-hover" id="tersediaTable">
                    <thead class="table-light">
                        <tr>
                            @if(auth()->user()->role !== 'vendor')
                            <th class="text-center align-middle" style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="checkAll">
                            </th>
                            @endif
                            <th class="text-center align-middle">No</th>
                            <th class="text-center align-middle">Tanggal UJ</th>
                            <th class="text-center align-middle">Kode</th>
                            <th class="text-center align-middle">Nomor Lambung</th>
                            <th class="text-center align-middle">Vendor</th>
                            <th class="text-center align-middle">Rute</th>
                            <th class="text-center align-middle">Jarak</th>
                            <th class="text-center align-middle">Harga</th>
                            <th class="text-center align-middle">Tanggal Muat</th>
                            <th class="text-center align-middle">Nota Muat</th>
                            <th class="text-center align-middle">Tonase Muat</th>
                            <th class="text-center align-middle">Tanggal Bongkar</th>
                            <th class="text-center align-middle">Nota Bongkar</th>
                            <th class="text-center align-middle">Tonase Bongkar</th>
                            <th class="text-center align-middle">Selisih (Ton)</th>
                            <th class="text-center align-middle">Selisih (%)</th>
                            <th class="text-center align-middle">Bruto</th>
                            <th class="text-center align-middle">Uang Jalan</th>
                            <th class="text-center align-middle">Netto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dataTersedia as $t)
                        @php
                        $bruto = $t->nominal_bayar;
                        $uj = $t->kas_uang_jalan->nominal_transaksi;
                        $netto = $bruto - $uj;
                        @endphp
                        <tr>
                            @if(auth()->user()->role !== 'vendor')
                            <td class="text-center align-middle">
                                <input type="checkbox" name="transaksi_ids[]" value="{{ $t->id }}"
                                    data-bruto="{{ $bruto }}" data-uj="{{ $uj }}" class="form-check-input check-item">
                            </td>
                            @endif
                            <td class="text-center align-middle">{{$loop->iteration}}</td>
                            <td class="text-center align-middle">{{$t->kas_uang_jalan->tanggal}}</td>
                            <td class="text-center align-middle">UJ{{sprintf("%02d",
                                $t->kas_uang_jalan->nomor_uang_jalan)}}</td>
                            <td class="text-center align-middle">{{$t->kas_uang_jalan->vehicle->nomor_lambung}}</td>
                            <td class="text-center align-middle">{{$t->kas_uang_jalan->vendor->nickname}}</td>
                            <td class="text-center align-middle">{{$t->kas_uang_jalan->rute->nama}}</td>
                            <td class="text-center align-middle">{{$t->kas_uang_jalan->rute->jarak}}</td>
                            <td class="text-center align-middle">{{number_format($t->harga_vendor, 0, ',', '.')}}</td>
                            <td class="text-center align-middle">{{$t->id_tanggal_muat}}</td>
                            <td class="text-center align-middle">{{$t->nota_muat}}</td>
                            <td class="text-center align-middle">{{$t->tonase}}</td>
                            <td class="text-center align-middle">{{$t->id_tanggal_bongkar}}</td>
                            <td class="text-center align-middle">{{$t->nota_bongkar}}</td>
                            <td class="text-center align-middle">{{$t->timbangan_bongkar}}</td>
                            <td class="text-center align-middle">{{number_format($t->tonase - $t->timbangan_bongkar, 2,
                                ',','.')}}</td>
                            <td class="text-center align-middle">{{number_format(($t->tonase -
                                $t->timbangan_bongkar)*0.1, 2, ',','.')}}</td>
                            <td class="text-center align-middle">{{number_format($bruto, 0, ',', '.')}}</td>
                            <td class="text-center align-middle">{{number_format($uj, 0, ',', '.')}}</td>
                            <td class="text-center align-middle">{{number_format($netto, 0, ',', '.')}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    @if($dataTersedia->count() > 0)
                    <tfoot>
                        <tr>
                            <td class="text-center align-middle"
                                colspan="{{ auth()->user()->role !== 'vendor' ? 16 : 15 }}"></td>
                            <td class="text-center align-middle"><strong>Total</strong></td>
                            <td class="align-middle text-center">{{number_format($total_tagihan, 0, ',', '.')}}</td>
                            <td class="align-middle text-center">{{number_format($total_uang_jalan, 0, ',', '.')}}</td>
                            <td class="align-middle text-end">{{number_format($total_netto, 0, ',', '.')}}</td>
                        </tr>
                        <tr>
                            <td class="text-center align-middle"
                                colspan="{{ auth()->user()->role !== 'vendor' ? 16 : 15 }}"></td>
                            <td class="text-center align-middle"><strong>PPN</strong></td>
                            <td class="align-middle"></td>
                            <td></td>
                            <td class="text-end align-middle">{{number_format($ppn, 0, ',', '.')}}</td>
                        </tr>
                        <tr>
                            <td class="align-middle" colspan="{{ auth()->user()->role !== 'vendor' ? 16 : 15 }}"></td>
                            <td class="text-center align-middle"><strong>PPh</strong></td>
                            <td class="align-middle"></td>
                            <td></td>
                            <td class="align-middle text-end">{{number_format($pph, 0, ',', '.')}}</td>
                        </tr>
                        <tr>
                            <td class="align-middle" colspan="{{ auth()->user()->role !== 'vendor' ? 16 : 15 }}"></td>
                            <td class="text-center align-middle"><strong>Grand Total</strong></td>
                            <td class="align-middle"></td>
                            <td></td>
                            <td class="align-middle text-end"><strong>{{number_format($grant_total, 0, ',',
                                    '.')}}</strong></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </form>
        </div>
    </div>
</div>
@endsection

@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush

@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    $(document).ready(function() {


    var vendorPpn = {{ $vendor->ppn == 1 ? 1 : 0 }};
    var vendorPph = {{ $vendor->pph == 1 ? 1 : 0 }};
    var vendorPphVal = {{ $vendor->pph_val ?? 0 }};

    // DataTable tanpa Pagination
    var table = $('#tersediaTable').DataTable({
        "paging": false,
        "ordering": true,
        "searching": true,
        "scrollCollapse": true,
        "scrollY": "500px",
        "scrollX": true,
        "language": {
            "emptyTable": "Tidak ada transaksi yang tersedia",
            "zeroRecords": "Tidak ada transaksi yang sesuai pencarian"
        }
    });

    // Toggle Checkbox All
    $('#checkAll').on('click', function() {
        var rows = table.rows({ 'search': 'applied' }).nodes();
        $('input[type="checkbox"].check-item', rows).prop('checked', this.checked);
        updateSelectedTotals();
    });

    // Event listener per checkbox item
    $('#tersediaTable tbody').on('change', 'input[type="checkbox"].check-item', function() {
        if (!this.checked) {
            var el = $('#checkAll').get(0);
            if (el && el.checked && ('indeterminate' in el)) {
                el.indeterminate = true;
            }
        }
        updateSelectedTotals();
    });

    // Fungsi Perhitungan Live Totals
    function updateSelectedTotals() {
        var checkedBoxes = table.$('input[type="checkbox"].check-item:checked');
        var count = checkedBoxes.length;

        var totalBruto = 0;
        var totalUJ = 0;

        checkedBoxes.each(function() {
            totalBruto += parseFloat($(this).data('bruto')) || 0;
            totalUJ += parseFloat($(this).data('uj')) || 0;
        });

        var totalNetto = totalBruto - totalUJ;
        var ppn = vendorPpn === 1 ? Math.floor(totalBruto * 0.11) : 0;
        var pph = vendorPph === 1 ? Math.floor(totalBruto * (vendorPphVal / 100)) : 0;
        var grandTotal = totalNetto - pph + ppn;

        $('#selectedCount').text(count);
        $('#selBruto').text(numberFormat(totalBruto));
        $('#selUangJalan').text(numberFormat(totalUJ));
        $('#selNetto').text(numberFormat(totalNetto));
        $('#selPpn').text(numberFormat(ppn));
        $('#selPph').text(numberFormat(pph));
        $('#selGrandTotal').text(numberFormat(grandTotal));

        $('#btnSubmitSelected').prop('disabled', count === 0);

        // Sinkronkan status checkbox "checkAll"
        var totalItems = table.$('input[type="checkbox"].check-item').length;
        $('#checkAll').prop('checked', totalItems > 0 && count === totalItems);
    }

    function numberFormat(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    // ============================================================
    // FIX BUG REFRESH: Jalankan perhitungan langsung saat page load
    // ============================================================
    updateSelectedTotals();

    // Submit form handler
    $('#formMasukKeranjang').on('submit', function(e) {
        var form = this;
        table.$('input[type="checkbox"].check-item:checked').each(function() {
            if (!$.contains(document, this)) {
                $(form).append(
                    $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', this.name)
                        .val(this.value)
                );
            }
        });
    });
});
</script>
@endpush
