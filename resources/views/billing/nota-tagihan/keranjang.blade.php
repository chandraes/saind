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
            <h1><u>{{$customer->nama}} ({{$customer->singkatan}})</u></h1>
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
                            href="{{route('billing.nota-tagihan.detail-jenis', ['customer'=>$customer->id, 'jenis' => $jenis])}}"><img
                                src="{{asset('images/back.svg')}}" alt="dokumen" width="30"> Kembali</a></td>
                    <td>
                </tr>
            </table>
        </div>
    </div>
</div>
@include('billing.transaksi.tagihan.keranjang.show-new')

<div class="container-fluid mt-3 table-responsive ">
    <div class="dropdown open mb-3">
        <button class="btn btn-success dropdown-toggle" type="button" id="triggerId" data-bs-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false">
            Tampilkan/Sembunyikan Kolom
        </button>
        <div class="dropdown-menu" aria-labelledby="triggerId" id="columnFilter">

        </div>
    </div>
    <table class="table table-bordered table-hover" id="notaTable">
        <thead class="table-success">
            <tr>

                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center
                    align-middle">No</th>
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center
                    align-middle">Tanggal UJ</th>
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center
                    align-middle">Kode</th>
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center
                    align-middle">NOLAM</th>
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center
                    align-middle">Vendor</th>
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center
                    align-middle">Rute</th>
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center
                    align-middle">Jarak (Km)</th>
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center
                    align-middle">Harga</th>
                @if ($customer->tanggal_muat == 1)
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center
                    align-middle" id="tanggal_muat_column">Tanggal Muat</th>
                @endif
                @if ($customer->nota_muat == 1)
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center
                    align-middle">Nota Muat</th>
                @endif
                @if ($customer->tonase == 1)
                <th @if($customer->gt_muat == 1) colspan="3" @elseif($customer->gt_bongkar == 1) rowspan="2" @endif
                    class="text-center align-middle">Tonase Muat</th>
                @endif
                @if ($customer->tanggal_bongkar == 1)
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center
                    align-middle" id="tanggal_bongkar_column">Tanggal Bongkar</th>
                @endif
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center
                    align-middle">Nota Bongkar</th>
                <th @if($customer->gt_bongkar == 1) colspan="3" @elseif($customer->gt_muat == 1) rowspan="2" @endif
                    class="text-center align-middle">Tonase Bongkar</th>
                @if ($customer->selisih == 1)
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center
                    align-middle">Selisih (Ton)</th>
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center
                    align-middle">Selisih (%)</th>
                @endif
            </tr>
            @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1)
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
            @foreach ($data as $d)
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
                <td class="text-center align-middle">
                    {{$invoice->dpp}}
                </td>
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
                <td class="text-center align-middle">{{number_format($d->tonase - $d->timbangan_bongkar, 2, ',','.')}}
                </td>
                <td class="text-center align-middle">{{number_format(($d->tonase - $d->timbangan_bongkar)*0.1, 2,
                    ',','.')}}</td>
                @endif

            </tr>
            @endforeach
        </tbody>

    </table>


</div>
<div class="container mt-4">
    <div class="card shadow-sm border-secondary-subtle">
        <div class="card-header bg-dark text-white fw-bold">
            <i class="fa fa-calculator me-2"></i> Ringkasan Perhitungan Per Rute
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">No</th>
                            <th>Nama Rute</th>
                            <th class="text-center">Jarak (Km)</th>
                            <th class="text-center">Total Muatan</th>
                            <th class="text-center">Harga (DPP)</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach ($ruteGrouped as $namaRute => $item)
                        <tr>
                            <td class="text-center">{{ $no++ }}</td>
                            <td class="fw-bold">{{ $namaRute }}</td>
                            <td class="text-center">{{ $item['jarak'] }}</td>
                            <td class="text-center">{{ number_format($item['total_muatan'], 2, ',', '.') }}</td>
                            <td class="text-center">Rp {{ number_format($item['dpp'], 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="5" class="text-end">Total  :</td>
                            <td class="text-end text-primary">Rp {{ number_format($ruteGrouped->sum('subtotal'), 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@if (in_array(auth()->user()->role, ['admin', 'su']))

<div class="container mt-4 mb-5">
    <div class="p-4 bg-light rounded border border-secondary-subtle shadow-sm">
        <div class="row g-3 align-items-end">

            <div class="col-md-6">
                <label for="nominal" class="form-label fw-bold text-secondary mb-1">
                    Total {{ $stringJenis }}
                </label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white border-secondary-subtle text-muted">Rp</span>
                    <input type="text" id="nominal" class="form-control bg-white border-secondary-subtle fw-bold text-dark" value="{{ $invoice->nf_nominal }}" readonly>
                </div>
            </div>

            <div class="col-md-3">
                <form action="{{route('billing.nota-tagihan.detail-jenis.keranjang.back', ['customer' => $customer->id, 'jenis' => $jenis, 'invoice' => $invoice->id])}}" method="post" id="backForm">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-lg w-100 shadow-sm fw-semibold">
                        <i class="fa fa-undo me-2"></i> Kembalikan
                    </button>
                </form>
            </div>

            <div class="col-md-3">
                <form id="lanjutForm" action="{{route('billing.nota-tagihan.detail-jenis.keranjang.lanjut', ['customer' => $customer->id, 'jenis' => $jenis, 'invoice' => $invoice->id])}}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm fw-semibold">
                        Lanjutkan <i class="fa fa-arrow-right ms-2"></i>
                    </button>
                </form>
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

        $('#spinner').show();


        $.fn.dataTable.moment('DD-MM-YYYY');

        var table = $('#notaTable').DataTable({
            // ... your DataTable options ...
        });

        var tanggalMuatColumnIndex = -1;
        var tanggalBongkarColumnIndex = -1;

        // Check if tanggal_muat_column is visible
        var tanggalMuatColumn = table.column('#tanggal_muat_column');
        if (tanggalMuatColumn.visible()) {
            tanggalMuatColumnIndex = tanggalMuatColumn.index();
        }

        // Check if tanggal_bongkar_column is visible
        var tanggalBongkarColumn = table.column('#tanggal_bongkar_column');
        if (tanggalBongkarColumn.visible()) {
            tanggalBongkarColumnIndex = tanggalBongkarColumn.index();
        }

        table.destroy(); // Destroy the initial DataTable

        $.extend($.fn.dataTableExt.oSort, {
            "date-eu-pre": function(date) {
                date = date.replace(" ", "");
                if (!date) {
                    return 0;
                }
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

        table = $('#notaTable').DataTable({
            "paging": false,
            "ordering": true,
            "scrollCollapse": true,
            "scrollY": "550px",
            "scrollX": true,
            "fixedColumns": {
                "leftColumns": 4, // Increase this by 1 because we're adding a column
                "rightColumns": 1
            },
            "columnDefs": [
                { "type": "date-eu", "targets": [tanggalMuatColumnIndex + 1, tanggalBongkarColumnIndex + 1] }, // Increase these by 1 because we're adding a column
                { "orderable": false, "targets": [0,1,-1] } // Make the numbering column unsortable
            ],
            "order": [[ 2, "asc" ]],
            "drawCallback": function (settings) {
                var api = this.api();
                var startIndex = api.context[0]._iDisplayStart; // Get the start index for the current page
                api.column(0, {page: 'current'}).nodes().each(function (cell, i) {
                    cell.innerHTML = startIndex + i + 1; // Update the numbering column
                });
            }
        });

        var dropdownMenu = $('#columnFilter');

        // Clear the existing dropdown menu
        dropdownMenu.empty();

        table.columns().every(function(index) {
            // Exclude the first column and the last column
            if (index === 0 || index === table.columns().count() - 1) {
                return;
            }

            var column = this;
            var columnName = $(column.header()).text();

            // Create a dropdown item for each column
            var dropdownItem = $('<a class="dropdown-item d-flex justify-content-between" href="#">' + columnName + '<span class="checkmark"><i class="fa fa-check"></i></span></a>');

            // Initially hide the checkmark if the column is not visible
            if (!column.visible()) {
                dropdownItem.find('.checkmark').hide();
            }

            // If the column visibility is saved in the session, restore it
            if (sessionStorage.getItem('columnVisibility' + index) !== null) {
                var isVisible = sessionStorage.getItem('columnVisibility' + index) === 'true';
                column.visible(isVisible);
                if (isVisible) {
                    dropdownItem.find('.checkmark').show();
                } else {
                    dropdownItem.find('.checkmark').hide();
                }
            }

            dropdownItem.on('click', function(e) {
                e.preventDefault();

                // Toggle the column visibility
                var isVisible = column.visible();
                column.visible(!isVisible);

                // Toggle the checkmark
                if (isVisible) {
                    $(this).find('.checkmark').hide();
                } else {
                    $(this).find('.checkmark').show();
                }

                // Save the column visibility in the session
                sessionStorage.setItem('columnVisibility' + index, !isVisible);
            });

            dropdownMenu.append(dropdownItem);
        });

        $('#spinner').hide();

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
