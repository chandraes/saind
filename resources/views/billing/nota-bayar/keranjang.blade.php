@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Keranjang {{$stringJenis}}</u></h1>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>{{$vendor->nama}} </u></h1>
        </div>
    </div>
    @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    @if (auth()->user()->role != 'asisten-user')
                    <td><a href="{{route('billing.index')}}"><img src="{{asset('images/billing.svg')}}" alt="dokumen"
                                width="30"> Billing</a></td>
                    @endif
                    <td><a
                            href="{{route('billing.nota-bayar.detail-jenis', ['vendor'=>$vendor->id, 'jenis' => $jenis])}}"><img
                                src="{{asset('images/back.svg')}}" alt="dokumen" width="30"> Kembali</a></td>
                    <td>
                </tr>
            </table>
        </div>
    </div>
</div>
@include('billing.nota-bayar.table-keranjang')

@if (in_array(auth()->user()->role, ['admin', 'su']))

<div class="container mt-4 mb-5">
    <div class="card shadow-sm border-0 bg-white">
        <div class="card-body p-4">
            <div class="row align-items-center">

                <!-- Kolom Kiri: Total dan Rincian -->
                <div class="col-lg-7 mb-3 mb-lg-0">
                    <label class="form-label fw-bold text-muted mb-2 text-uppercase" style="letter-spacing: 0.5px; font-size: 0.85rem;">
                        Total Tagihan {{ $stringJenis }}
                    </label>
                    <div class="input-group input-group-lg mb-2">
                        <span class="input-group-text bg-white border-end-0 text-secondary fw-bold">Rp</span>
                        <input type="text" id="nominal" class="form-control bg-white border-start-0 ps-0 fw-bold text-dark" value="{{ number_format($totalAkhir, 0, ',', '.') }}" readonly style="font-size: 1.3rem; letter-spacing: 0.5px;">
                    </div>

                    <!-- Rincian Pajak yang Lebih Rapi -->
                    <div class="d-flex flex-wrap gap-3 mt-1" style="font-size: 0.85rem;">
                        <span class="text-muted">
                            DPP: <strong>{{ number_format($totalKeseluruhan, 0, ',', '.') }}</strong>
                        </span>
                        @if($ppn > 0)
                        <span class="text-primary">
                            + PPN 11%: <strong>{{ number_format($ppn, 0, ',', '.') }}</strong>
                        </span>
                        @endif
                        @if($pph > 0)
                        <span class="text-danger">
                            - PPH {{ $vendor->pph_val }}%: <strong>{{ number_format($pph, 0, ',', '.') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Kolom Kanan: Tombol Aksi -->
                <div class="col-lg-5">
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-lg-end">
                        <form action="{{route('billing.nota-bayar.detail-jenis.keranjang.back', ['vendor' => $vendor->id, 'jenis' => $jenis, 'invoice' => $invoice->id])}}" method="post" id="backForm" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-lg w-100 fw-semibold shadow-sm">
                                <i class="fa fa-undo me-2"></i> Kembalikan
                            </button>
                        </form>
                        <form id="lanjutForm" action="{{route('billing.nota-bayar.detail-jenis.keranjang.lanjut', ['vendor' => $vendor->id, 'jenis' => $jenis, 'invoice' => $invoice->id])}}" method="post" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold shadow-sm">
                                Lanjutkan <i class="fa fa-arrow-right ms-2"></i>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endif


@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="{{asset('assets/js/flatpickr/flatpickr.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/js/dt/dt-button.css')}}">
@endpush
@push('js')
<script src="{{asset('assets/js/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/plugins/date-picker/date-picker.js')}}"></script>
<script src="{{asset('assets/js/dt-font.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.10.22/sorting/datetime-moment.js"></script>

<script>
    $(document).ready(function() {

        var table = $('#notaTable').DataTable({
            "paging": false,
            "ordering": true,
            "scrollCollapse": true,
            "scrollY": "550px",
            "scrollX": true,

        });



    });

    $('#lanjutForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah anda yakin?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
                }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    this.submit();
                }
            })
        });

        $('#backForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah anda yakin untuk mengembalikan semua transaksi ini ke tahap sebelumnya?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, lanjutkan!'
                }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    this.submit();
                }
            })
        });
</script>
@endpush
