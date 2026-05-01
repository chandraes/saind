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

    <!-- Tabel Data -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0 table-responsive">
            <table class="table align-middle table-hover mb-0" id="data-table" style="font-size: 0.9rem;">
                <thead class="table-success border-bottom">
                    <tr>
                        <th class="text-center" style="width: 120px;">Tanggal</th>
                        <th class="text-center" style="width: 200px;">Vendor</th>
                        <th class="text-center" style="width: 180px;">Invoice</th>
                        <th class="text-end" style="width: 220px;">Total Bayar</th>
                        <th class="text-center" style="width: 120px;">Pembayaran</th>
                        @if (Auth::user()->role == 'su')
                        <th class="text-center" style="width: 100px;">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $d)
                    <tr>
                        <td class="text-center text-muted">{{ $d->tanggal }}</td>
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
                        @if (Auth::user()->role == 'su')
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
                    <tr>
                        <td class="text-center text-muted">{{ $item->tanggal }}</td>
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
                        @if (Auth::user()->role == 'su')
                        <td class="text-center">
                            <span class="badge bg-light text-secondary border px-3 py-2">
                                <i class="fa fa-cog me-1"></i> N/A
                            </span>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<style>

</style>
@endpush

@push('js')
<script src="{{asset('assets/plugins/date-picker/date-picker.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    $(document).ready(function() {
        // Inisialisasi DataTable
        $('#data-table').DataTable({
            pageLength: 10,
            order: [[0, 'desc']], // Urutkan dari tanggal terbaru

        });

        // --- EVENT DELEGATION UNTUK TOMBOL BAYAR ---
        // (Mengganti banyak script di dalam foreach yang lama)
        $('#data-table').on('submit', '.form-bayar', function(e) {
            e.preventDefault();
            var nominal = $(this).data('nominal');

            Swal.fire({
                title: 'Konfirmasi Pembayaran',
                text: "Anda akan memproses pembayaran sebesar Rp. " + nominal,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754', // Warna hijau sukses
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Bayar Sekarang',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });

        // --- EVENT DELEGATION UNTUK TOMBOL BACK ---
        $('#data-table').on('submit', '.form-back', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Kembalikan Transaksi?',
                text: "Data invoice ini akan dikembalikan ke tahap sebelumnya.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545', // Warna merah bahaya
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
