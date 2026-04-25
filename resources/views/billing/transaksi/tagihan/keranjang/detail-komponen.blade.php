@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center mb-4 mt-3">
        <div class="col-md-12 text-center">
            <h2 class="fw-bold text-dark mb-1">Detail {{$stringJenis}}</h2>
            <h4 class="text-secondary">{{$customer->nama}} ({{$customer->singkatan}})</h4>
            <hr class="w-25 mx-auto border-secondary">
        </div>
    </div>

    @include('swal')

    {{-- Notifikasi Error --}}
    @if ($errors->any())
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <strong>Whoops!</strong> Ada kesalahan saat input data, yaitu:
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                    <li><strong>{{$error}}</strong></li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @endif

    <div class="d-flex justify-content-start align-items-center gap-4 mt-3 mb-4 border-bottom pb-3">
        <a href="{{route('home')}}" class="text-decoration-none text-dark fw-semibold hover-primary">
            <img src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="28" class="me-1"> Dashboard
        </a>
        @if (auth()->user()->role != 'asisten-user')
        <a href="{{route('billing.index')}}" class="text-decoration-none text-dark fw-semibold hover-primary">
            <img src="{{asset('images/billing.svg')}}" alt="dokumen" width="28" class="me-1"> Billing
        </a>
        @endif
        <a href="{{url()->previous()}}" class="text-decoration-none text-dark fw-semibold hover-primary">
            <img src="{{asset('images/back.svg')}}" alt="kembali" width="28" class="me-1"> Kembali
        </a>
    </div>
</div>

@include('billing.transaksi.tagihan.keranjang.show-new')

<div class="container-fluid mt-3 table-responsive">
    <div class="dropdown open mb-3">
        <button class="btn btn-success dropdown-toggle shadow-sm" type="button" id="triggerId" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-columns me-1"></i> Tampilkan/Sembunyikan Kolom
        </button>
        <div class="dropdown-menu shadow" aria-labelledby="triggerId" id="columnFilter">
            </div>
    </div>

    <table class="table table-bordered table-hover" id="notaTable">
        <thead class="table-success">
            @php
                // OPTIMASI: Deklarasi kondisi sekali saja agar tidak mengulang operator logika di setiap kolom
                $hasGtRowspan = $customer->gt_muat == 1 || $customer->gt_bongkar == 1;
            @endphp
            <tr>
                <th @if($hasGtRowspan) rowspan="2" @endif class="text-center align-middle">No</th>
                <th @if($hasGtRowspan) rowspan="2" @endif class="text-center align-middle">Tanggal UJ</th>
                <th @if($hasGtRowspan) rowspan="2" @endif class="text-center align-middle">Kode</th>
                <th @if($hasGtRowspan) rowspan="2" @endif class="text-center align-middle">NOLAM</th>
                <th @if($hasGtRowspan) rowspan="2" @endif class="text-center align-middle">Vendor</th>
                <th @if($hasGtRowspan) rowspan="2" @endif class="text-center align-middle">Rute</th>
                <th @if($hasGtRowspan) rowspan="2" @endif class="text-center align-middle">Jarak (Km)</th>
                <th @if($hasGtRowspan) rowspan="2" @endif class="text-center align-middle">Harga</th>

                @if ($customer->tanggal_muat == 1)
                <th @if($hasGtRowspan) rowspan="2" @endif class="text-center align-middle" id="tanggal_muat_column">Tanggal Muat</th>
                @endif

                @if ($customer->nota_muat == 1)
                <th @if($hasGtRowspan) rowspan="2" @endif class="text-center align-middle">Nota Muat</th>
                @endif

                @if ($customer->tonase == 1)
                <th @if($customer->gt_muat == 1) colspan="3" @elseif($customer->gt_bongkar == 1) rowspan="2" @endif class="text-center align-middle">Tonase Muat</th>
                @endif

                @if ($customer->tanggal_bongkar == 1)
                <th @if($hasGtRowspan) rowspan="2" @endif class="text-center align-middle" id="tanggal_bongkar_column">Tanggal Bongkar</th>
                @endif

                <th @if($hasGtRowspan) rowspan="2" @endif class="text-center align-middle">Nota Bongkar</th>
                <th @if($customer->gt_bongkar == 1) colspan="3" @elseif($customer->gt_muat == 1) rowspan="2" @endif class="text-center align-middle">Tonase Bongkar</th>

                @if ($customer->selisih == 1)
                <th @if($hasGtRowspan) rowspan="2" @endif class="text-center align-middle">Selisih (Ton)</th>
                <th @if($hasGtRowspan) rowspan="2" @endif class="text-center align-middle">Selisih (%)</th>
                @endif
            </tr>
            @if($hasGtRowspan)
            <tr>
                @if ($customer->gt_muat == 1)
                <th class="text-center align-middle">Gross</th>
                <th class="text-center align-middle">Tarra</th>
                <th class="text-center align-middle">Netto</th>
                @endif
                @if ($customer->gt_bongkar == 1)
                <th class="text-center align-middle">Gross</th>
                <th class="text-center align-middle">Tarra</th>
                <th class="text-center align-middle">Netto</th>
                @endif
            </tr>
            @endif
        </thead>
        <tbody>
            @foreach ($data->details as $d)
            @php
                $d = $d->transaksi;
            @endphp
            <tr>
                <td class="text-center align-middle"></td>
                <td class="text-center align-middle">
                    {{$d->kas_uang_jalan->tanggal}} <br>
                    ({{$d->kas_uang_jalan->created_at->format('H:i:s')}})
                </td>
                <td class="align-middle">
                    UJ{{sprintf("%02d", $d->kas_uang_jalan->nomor_uang_jalan)}}
                </td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->vehicle->nomor_lambung}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->vendor->nickname}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->rute->nama}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->rute->jarak}}</td>
                <td class="text-center align-middle">{{$data->dpp}}</td>

                @if ($customer->tanggal_muat == 1)
                <td class="text-center align-middle">{{$d->id_tanggal_muat}}</td>
                @endif

                @if ($customer->nota_muat == 1)
                <td class="text-center align-middle">{{$d->nota_muat}}</td>
                @endif

                @if ($customer->tonase == 1)
                    @if ($customer->gt_muat == 1)
                    <td class="text-center align-middle">{{$d->gross_muat}}</td>
                    <td class="text-center align-middle">{{$d->tarra_muat}}</td>
                    @endif
                <td class="text-center align-middle">{{$d->tonase}}</td>
                @endif

                @if ($customer->tanggal_bongkar == 1)
                <td class="text-center align-middle">{{$d->id_tanggal_bongkar}}</td>
                @endif

                <td class="text-center align-middle">{{$d->nota_bongkar}}</td>

                @if ($customer->gt_bongkar == 1)
                <td class="text-center align-middle">{{$d->gross_bongkar}}</td>
                <td class="text-center align-middle">{{$d->tarra_bongkar}}</td>
                @endif

                <td class="text-center align-middle">{{$d->timbangan_bongkar}}</td>

                @if ($customer->selisih == 1)
                <td class="text-center align-middle">{{number_format($d->tonase - $d->timbangan_bongkar, 2, ',','.')}}</td>
                <td class="text-center align-middle">{{number_format(($d->tonase - $d->timbangan_bongkar)*0.1, 2, ',','.')}}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="container mt-4 mb-5">
    {{-- Sesuaikan Route di bawah ini dengan Route aslinya untuk fungsi kembalikan --}}
    <form action="{{ route('home') }}" method="POST" id="lanjutForm">
        @csrf
        <div class="row g-3 align-items-end p-4 bg-light rounded border border-secondary-subtle shadow-sm">
            <div class="col-md-8">
                <label for="nominal" class="form-label fw-bold text-secondary">
                    Total {{ $stringJenis }}
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-secondary-subtle">Rp</span>
                    <input type="text" id="nominal" class="form-control bg-white border-secondary-subtle" value="{{ $data->nf_nominal }}" readonly>
                </div>
            </div>
            <div class="col-md-4 d-grid">
                <button type="submit" class="btn btn-warning fw-bold shadow-sm">
                    <i class="fa fa-undo me-2"></i> Kembalikan ke Keranjang {{$stringJenis}}
                </button>
            </div>
        </div>
    </form>
</div>

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
        $('#spinner').show();
        $.fn.dataTable.moment('DD-MM-YYYY');

        // OPTIMASI: Mencari index kolom TANPA harus menginisialisasi DataTable dua kali
        var tanggalMuatColumnIndex = -1;
        var tanggalBongkarColumnIndex = -1;

        var $thMuat = $('#notaTable thead th#tanggal_muat_column');
        if ($thMuat.length) {
            tanggalMuatColumnIndex = $thMuat.index();
        }

        var $thBongkar = $('#notaTable thead th#tanggal_bongkar_column');
        if ($thBongkar.length) {
            tanggalBongkarColumnIndex = $thBongkar.index();
        }

        $.extend($.fn.dataTableExt.oSort, {
            "date-eu-pre": function(date) {
                date = date.replace(" ", "");
                if (!date) { return 0; }
                var parts = date.split('-');
                return (parts[2] + parts[1] + parts[0]) * 1;
            },
            "date-eu-asc": function(a, b) {
                return ((a < b) ? -1 : ((a > b) ? 1 : 0));
            },
            "date-eu-desc": function(a, b) {
                return ((a < b) ? 1 : ((a > b) ? -1 : 0));
            }
        });

        // Generate Target Sort Date dinamis
        var dateTargets = [];
        if (tanggalMuatColumnIndex !== -1) dateTargets.push(tanggalMuatColumnIndex);
        if (tanggalBongkarColumnIndex !== -1) dateTargets.push(tanggalBongkarColumnIndex);

        var columnDefsArray = [
            { "orderable": false, "targets": [0, 1, -1] }
        ];

        if(dateTargets.length > 0) {
            columnDefsArray.push({ "type": "date-eu", "targets": dateTargets });
        }

        // Inisialisasi DataTable (Cukup 1 Kali Saja)
        var table = $('#notaTable').DataTable({
            "paging": false,
            "ordering": true,
            "scrollCollapse": true,
            "scrollY": "550px",
            "scrollX": true,
            "fixedColumns": {
                "leftColumns": 4,
                "rightColumns": 1
            },
            "columnDefs": columnDefsArray,
            "order": [[ 2, "asc" ]],
            "drawCallback": function (settings) {
                var api = this.api();
                var startIndex = api.context[0]._iDisplayStart;
                api.column(0, {page: 'current'}).nodes().each(function (cell, i) {
                    cell.innerHTML = startIndex + i + 1;
                });
            }
        });

        // Logic Kolom Filter
        var dropdownMenu = $('#columnFilter');
        dropdownMenu.empty();

        table.columns().every(function(index) {
            if (index === 0 || index === table.columns().count() - 1) {
                return;
            }

            var column = this;
            var columnName = $(column.header()).text().trim();
            if(!columnName) return;

            var isVisible = true;
            if (sessionStorage.getItem('columnVisibility' + index) !== null) {
                isVisible = sessionStorage.getItem('columnVisibility' + index) === 'true';
                column.visible(isVisible);
            } else {
                isVisible = column.visible();
            }

            var dropdownItem = $('<a class="dropdown-item d-flex justify-content-between align-items-center" href="#"><span>' + columnName + '</span><span class="checkmark text-success"><i class="fa fa-check"></i></span></a>');

            if (!isVisible) {
                dropdownItem.find('.checkmark').hide();
            }

            dropdownItem.on('click', function(e) {
                e.preventDefault();
                var colVisible = column.visible();
                column.visible(!colVisible);

                if (colVisible) {
                    $(this).find('.checkmark').hide();
                } else {
                    $(this).find('.checkmark').show();
                }
                sessionStorage.setItem('columnVisibility' + index, !colVisible);
            });

            dropdownMenu.append(dropdownItem);
        });

        $('#spinner').hide();
    });

    // Alert SweetAlert untuk Form
    $('#lanjutForm').submit(function(e){
        e.preventDefault();
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Data akan dikembalikan ke keranjang!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, kembalikan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#spinner').show();
                this.submit();
            }
        });
    });
</script>
@endpush
