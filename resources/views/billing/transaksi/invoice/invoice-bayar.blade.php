@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <!-- Header & Navigasi -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">
            <i class="fa fa-file-invoice-dollar me-2 text-secondary"></i>DAFTAR INVOICE BAYAR VENDOR
        </h4>
        <div class="d-flex gap-4">
            <a href="{{route('home')}}" class="text-secondary text-decoration-none fw-medium">
                <i class="fa fa-tachometer me-1"></i> Dashboard
            </a>
            <a href="{{route('billing.index')}}" class="text-secondary text-decoration-none fw-medium">
                <i class="fa fa-folder-open me-1"></i> Billing
            </a>
        </div>
    </div>

    @include('swal')

    <!-- CARD FILTER DATA -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-light rounded">
            <form action="{{ route('invoice.bayar.index') }}" method="GET" id="filterForm">
                <div class="row g-3 align-items-end">
                    <!-- Filter Vendor -->
                   <div class="col-md-4 col-sm-12">
                        <label for="vendor_id" class="form-label fw-bold text-secondary">
                            <i class="fa fa-filter me-1"></i> Vendor
                        </label>
                        <select name="vendor_id" id="vendor_id" class="form-select form-select-sm select2">
                            <option value="">-- Semua Vendor --</option>
                            @foreach ($vendors as $v)
                                <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>
                                    {{ $v->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Range Jatuh Tempo (Mulai) -->
                    <div class="col-md-3 col-sm-6">
                        <label for="start_date" class="form-label fw-bold text-secondary">
                            <i class="fa fa-calendar me-1"></i> Tempo Dari
                        </label>
                        <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                    </div>

                    <!-- Filter Range Jatuh Tempo (Sampai) -->
                    <div class="col-md-3 col-sm-6">
                        <label for="end_date" class="form-label fw-bold text-secondary">
                            <i class="fa fa-calendar me-1"></i> Tempo Sampai
                        </label>
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="col-md-2 col-sm-12 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="fa fa-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('invoice.bayar.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                            <i class="fa fa-refresh"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0 table-responsive">
            <table class="table align-middle table-hover mb-0" id="data-table" style="font-size: 0.9rem;">
                <thead class="table-success border-bottom">
                    <tr>
                        <th class="text-center" style="width: 110px;">Tanggal</th>
                        <th class="text-center" style="width: 130px;">Jatuh Tempo</th>
                        <th class="text-center" style="width: 180px;">Vendor</th>
                        <th class="text-center" style="width: 160px;">Invoice</th>
                        <th class="text-end" style="width: 180px;">Total Bayar</th>
                        <th class="text-center" style="width: 120px;">Pembayaran</th>
                         @if (Auth::user()->role == 'su' || Auth::user()->role == 'admin')
                        <th class="text-center" style="width: 100px;">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php $today = date('Y-m-d'); @endphp
                    @foreach ($data as $d)
                    @php
                        $isOverdue = $d->tempo && $d->tempo < $today;
                    @endphp
                    <tr>
                        <td class="text-center text-muted">{{ $d->tanggal }}</td>
                        <td class="text-center">
                            @if ($d->tempo)
                                <span class="badge {{ $isOverdue ? 'bg-danger' : 'bg-info text-dark' }} px-2 py-1">
                                    <i class="fa {{ $isOverdue ? 'fa-exclamation-triangle' : 'fa-clock' }} me-1"></i>
                                    {{ $d->tempo }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center fw-semibold">{{ $d->vendor->nama }}</td>
                        <td class="text-center">
                            <a href="{{route('invoice.bayar.detail', $d)}}" class="text-primary fw-bold text-decoration-none">{{ $d->periode }}</a>
                        </td>
                        <td class="text-end fw-semibold">Rp {{ number_format($d->total_bayar, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <form action="{{route('invoice.bayar.lunas', $d)}}" method="post" class="d-inline form-bayar" data-nominal="{{ number_format($d->sisa_bayar, 0, ',', '.') }}">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm px-3 shadow-sm">
                                    <i class="fa fa-check-circle me-1"></i> Bayar
                                </button>
                            </form>
                        </td>
                        @if (Auth::user()->role == 'su' || Auth::user()->role == 'admin')
                        <td class="text-center">
                            <form action="{{route('invoice.bayar-back.execute', ['invoice' => $d->id])}}" method="post" class="d-inline form-back">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm px-3">
                                    <i class="fa fa-undo me-1"></i> Back
                                </button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @endforeach

                    @foreach ($addInvoice as $item)
                    @php
                        $isOverdue = $item->tempo && $item->tempo < $today;
                    @endphp
                    <tr>
                        <td class="text-center text-muted">{{ $item->tanggal }}</td>
                        <   <td class="text-center">
                            @if ($item->tempo)
                                <span class="badge {{ $isOverdue ? 'bg-danger' : 'bg-info text-dark' }} px-2 py-1">
                                    <i class="fa {{ $isOverdue ? 'fa-exclamation-triangle' : 'fa-clock' }} me-1"></i>
                                    {{ $item->tempo }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center fw-semibold">{{ $item->vendor->nama }}</td>
                        <td class="text-center">
                            <a href="{{route('invoice.bayar.detail-jenis', $item->id)}}" class="text-primary fw-bold text-decoration-none">{{ $item->periode_invoice }}</a>
                        </td>
                        <td class="text-end fw-semibold">Rp {{ $item->nf_total }}</td>
                        <td class="text-center">
                            <form action="{{route('invoice.bayar.jenis-lunas', $item->id)}}" method="post" class="d-inline form-bayar" data-nominal="{{ $item->nf_total }}">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm px-3 shadow-sm">
                                    <i class="fa fa-check-circle me-1"></i> Bayar
                                </button>
                            </form>
                        </td>
                        @if (Auth::user()->role == 'su' || Auth::user()->role == 'admin')
                        <td class="text-center">
                            <span class="badge bg-light text-secondary border px-3 py-2">
                                <i class="fa fa-cog me-1"></i> N/A
                            </span>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light border-top">
                <tr>
                    <td colspan="4" class="text-end fw-bold align-middle">Grand Total:</td>
                    <td class="text-end fw-bold text-success fs-6 align-middle" id="tableGrandTotal">Rp 0</td>
                    <td colspan="{{ Auth::user()->role == 'su' || Auth::user()->role == 'admin' ? 2 : 1 }}"></td>
                </tr>
            </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush

@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
   $(document).ready(function() {
    // Inisialisasi Select2
    $('#vendor_id').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Semua Vendor --',
        allowClear: true,
        width: '100%'
    });

    // Inisialisasi DataTable dengan Live Total Calculation
    $('#data-table').DataTable({
        pageLength: 10,
        order: [[0, 'desc']],
        footerCallback: function (row, data, start, end, display) {
            var api = this.api();

            // Helper untuk mengubah format rupiah string ("Rp 1.500.000") menjadi angka float
            var intVal = function (i) {
                if (typeof i === 'string') {
                    return parseFloat(i.replace(/[^\d]/g, '')) || 0;
                }
                return typeof i === 'number' ? i : 0;
            };

            // Hitung Total Bayar dari seluruh data yang terfilter di tabel
            var grandTotal = api
                .column(4, { search: 'applied' }) // Index 4 = Kolom Total Bayar
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            // Format ke Rupiah
            var formattedTotal = 'Rp ' + new Intl.NumberFormat('id-ID').format(grandTotal);

            // Render ke Footer Tabel dan Card Summary
            $('#tableGrandTotal').text(formattedTotal);
            $('#cardGrandTotal').text(formattedTotal);
        }
    });

    // Event Handler Tombol Bayar & Back (Tetap Sama)
    $('#data-table').on('submit', '.form-bayar', function(e) {
        e.preventDefault();
        var nominal = $(this).data('nominal');

        Swal.fire({
            title: 'Konfirmasi Pembayaran',
            text: "Anda akan memproses pembayaran sebesar Rp. " + nominal,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Bayar Sekarang',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    $('#data-table').on('submit', '.form-back', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Kembalikan Transaksi?',
            text: "Data invoice ini akan dikembalikan ke tahap sebelumnya.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Kembalikan',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });
});
</script>
@endpush
