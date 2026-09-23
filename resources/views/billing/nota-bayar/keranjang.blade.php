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
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30"> Dashboard</a></td>
                    @if (auth()->user()->role != 'asisten-user' && auth()->user()->role != 'vendor')
                    <td><a href="{{route('billing.index')}}"><img src="{{asset('images/billing.svg')}}" alt="dokumen" width="30"> Billing</a></td>
                    @endif
                    <td><a href="{{route('billing.nota-bayar.detail-jenis', ['vendor'=>$vendor->id, 'jenis' => $jenis])}}"><img src="{{asset('images/back.svg')}}" alt="dokumen" width="30"> Kembali</a></td>
                    <td></td>
                </tr>
            </table>
        </div>
    </div>
</div>

@include('billing.nota-bayar.table-keranjang')

@if (in_array(auth()->user()->role, ['admin', 'su']))

<div class="container mt-4 mb-5">
    <div class="card shadow-sm border-0 bg-white rounded-3">
        <div class="card-body p-4">

            <form id="lanjutForm" action="{{route('billing.nota-bayar.detail-jenis.keranjang.lanjut', ['vendor' => $vendor->id, 'jenis' => $jenis, 'invoice' => $invoice->id])}}" method="post">
                @csrf

                <div class="row g-4 align-items-center">
                    <!-- Kolom Kiri: Input Jatuh Tempo -->
                    <div class="col-md-6 col-lg-5">
                        <label for="tempo" class="form-label fw-bold text-secondary small text-uppercase mb-2">
                            <i class="fa fa-calendar me-1"></i> Tanggal Jatuh Tempo
                            <span class="badge bg-info text-dark ms-1">{{ $vendor->jatuh_tempo_hari ?? 0 }} Hari</span>
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light text-secondary border-end-0">
                                <i class="fa fa-calendar"></i>
                            </span>
                            <input type="text"
                                   name="tempo"
                                   id="tempo"
                                   class="form-control form-control-lg bg-white fw-bold text-primary flatpickr border-start-0"
                                   value="{{ old('tempo', $defaultTempo) }}"
                                   required>
                        </div>
                        <div class="form-text small text-muted mt-2">
                            <i class="fa fa-info-circle me-1"></i> Otomatis dari tanggal hari ini. Dapat disesuaikan jika perlu.
                        </div>
                    </div>

                    <!-- Kolom Kanan: Ringkasan Total Tagihan & Pajak -->
                    <div class="col-md-6 col-lg-7">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-muted text-uppercase small" style="letter-spacing: 0.5px;">
                                    Total Tagihan {{ $stringJenis }}
                                </span>
                                <span class="fs-3 fw-bold text-primary">
                                    Rp {{ number_format($totalAkhir, 0, ',', '.') }}
                                </span>
                            </div>
                            <hr class="my-2 border-secondary-subtle">
                            <div class="d-flex flex-wrap justify-content-between gap-2 small">
                                <span class="text-muted">DPP: <strong>Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}</strong></span>
                                @if($ppn > 0)
                                <span class="text-primary">+ PPN (11%): <strong>Rp {{ number_format($ppn, 0, ',', '.') }}</strong></span>
                                @endif
                                @if($pph > 0)
                                <span class="text-danger">- PPH ({{ $vendor->pph_val }}%): <strong>Rp {{ number_format($pph, 0, ',', '.') }}</strong></span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4 border-light-subtle">

                <!-- Tombol Aksi -->
                <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-danger btn-lg px-4 fw-semibold shadow-sm" onclick="$('#backForm').submit();">
                        <i class="fa fa-undo me-2"></i> Kembalikan
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg px-4 fw-semibold shadow-sm">
                        Lanjutkan <i class="fa fa-arrow-right ms-2"></i>
                    </button>
                </div>

            </form>

            <!-- Form Tersembunyi untuk Tombol Kembalikan -->
            <form action="{{route('billing.nota-bayar.detail-jenis.keranjang.back', ['vendor' => $vendor->id, 'jenis' => $jenis, 'invoice' => $invoice->id])}}" method="post" id="backForm" class="d-none">
                @csrf
            </form>

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

        // Inisialisasi Flatpickr
        $(".flatpickr").flatpickr({
            dateFormat: "Y-m-d",
            defaultDate: "{{ $defaultTempo }}"
        });

    });

    $('#lanjutForm').submit(function(e){
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Transaksi akan diselesaikan dengan tanggal jatuh tempo " + $('#tempo').val(),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, simpan!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#spinner').show();
                form.submit();
            }
        });
    });

    $('#backForm').submit(function(e){
        e.preventDefault();
        var form = this;
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
                form.submit();
            }
        });
    });
</script>
@endpush
