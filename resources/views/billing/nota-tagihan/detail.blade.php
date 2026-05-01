@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>{{$stringJenis}}</u></h1>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>{{$customer->nama}} ({{$customer->singkatan}})</u></h1>
        </div>
    </div>
    @include('swal')
    {{-- if errors has any --}}

    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-8">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                                 @if (auth()->user()->role != 'asisten-user')
                    <td><a href="{{route('billing.index')}}"><img src="{{asset('images/billing.svg')}}"
                                alt="dokumen" width="30"> Billing</a></td>
                                @endif
                                  <td><a href="{{route('billing.nota-tagihan', $customer->id)}}"><img src="{{asset('images/back.svg')}}"
                                alt="dokumen" width="30"> Kembali</a>
                            </td>
                    <td class="align-middle"><a href="{{route('billing.nota-tagihan.detail-jenis.keranjang', ['customer' => $customer->id, 'jenis' => $jenis])}}"><i class="fa fa-cart-arrow-down me-2" style="font-size: 30px"></i> Keranjang @if ($keranjang > 0) <span
                            class="badge bg-danger">{{$keranjang}}</span> @endif</a></td>

                </tr>
            </table>
        </div>
    </div>
</div>
@include('billing.transaksi.tagihan.filter')
@include('billing.transaksi.tagihan.show-new')

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
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center align-middle">
                    Select
                </th>
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif  class="text-center align-middle">No</th>
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center align-middle">Tanggal UJ</th>
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center align-middle">Kode</th>
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center align-middle">NOLAM</th>
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center align-middle">Vendor</th>
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center align-middle">Rute</th>
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center align-middle">Jarak (Km)</th>
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center align-middle">Harga</th>
                @if ($customer->tanggal_muat == 1)
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center align-middle" id="tanggal_muat_column">Tanggal Muat</th>
                @endif
                @if ($customer->nota_muat == 1)
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center align-middle">Nota Muat</th>
                @endif
                @if ($customer->tonase == 1)
                <th @if($customer->gt_muat == 1) colspan="3" @elseif($customer->gt_bongkar == 1) rowspan="2" @endif class="text-center align-middle">Tonase Muat</th>
                @endif
                @if ($customer->tanggal_bongkar == 1)
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center align-middle" id="tanggal_bongkar_column">Tanggal Bongkar</th>
                @endif
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center align-middle">Nota Bongkar</th>
                <th @if($customer->gt_bongkar == 1) colspan="3" @elseif($customer->gt_muat == 1) rowspan="2" @endif class="text-center align-middle">Tonase Bongkar</th>
                @if ($customer->selisih == 1)
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center align-middle">Selisih (Ton)</th>
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center align-middle">Selisih (%)</th>
                @endif
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center align-middle">DO Fisik</th>
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
            <tr>
                {{-- check list --}}
                <td class="text-center align-middle">
                    {{-- @if (in_array($d->id, $pendingTransaksiIds))
                        {{-- icon checked --}}
                        <i class="fa fa-check text-success" style="font-size: 20px"></i>
                    {{-- @endif --}}
                </td>
                <td class="text-center align-middle"></td>
                <td class="text-center align-middle">
                    {{$d->kas_uang_jalan->tanggal}} <br>
                    ({{$d->kas_uang_jalan->created_at->format('H:i:s')}})
                </td>
                <td class="align-middle">
                    <div class="text-center">
                        {{-- <a href="#" data-bs-toggle="modal" data-bs-target="#uj{{$d->id}}">
                            <strong>UJ{{sprintf("%02d",
                                $d->kas_uang_jalan->nomor_uang_jalan)}}</strong></a> --}}
                        <a href="#" data-bs-toggle="modal" data-bs-target="#showModal" onclick="updateShow({{$d}})">
                            <strong>UJ{{sprintf("%02d",
                                $d->kas_uang_jalan->nomor_uang_jalan)}}</strong></a>
                    </div>
                    {{-- @include('billing.transaksi.tagihan.show') --}}
                </td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->vehicle->nomor_lambung}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->vendor->nickname}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->rute->nama}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->rute->jarak}}</td>
                <td class="text-center align-middle">
                    0
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

                <td class="text-center align-middle">
                    @if ($d->nota_fisik == 1)
                     <i class="fa fa-check text-success" style="font-size: 20px"></i>
                    @endif
                    @if ($d->nota_fisik == 1 && $d->do_checker_id != null)
                    <br>
                    Checker: <strong>{{$d->do_checker->name}}</strong>
                    @endif
                </td>

            </tr>

            @endforeach
        </tbody>
        {{-- <tfoot>
            <tr>
                <td class=""
                    colspan="{{10 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 1 : 0) +
                                                                ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0) + ($customer->gt_bongkar == 1 ? 2 : 0) + ($customer->gt_muat == 1 ? 2 : 0)}}">
                    <div class="row text-center">
                        <div class="col-md-4 mt-2">
                            <label for="" class="form-label">Total Tagihan Dipilih : </label>
                        </div>
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon1">Rp</span>
                                <input type="text" class="form-control text-bold" name="total_tagih_diplay"
                                    id="total_tagihan_display">
                            </div>
                        </div>
                    </div>
                </td>
                <td class="text-center align-middle"><strong>Total</strong></td>
                <td class="text-end align-middle">{{number_format($total_tagihan, 0, ',', '.')}}
                </td>
                <td class="text-end align-middle">{{number_format($profit, 0, ',', '.')}}</td>
                <td class="text-end align-middle">{{number_format($profit_persen, 2, ',', '.')}}%</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center align-middle"
                    colspan="{{10 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 1 : 0) +
                                                                ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0) + ($customer->gt_bongkar == 1 ? 2 : 0) + ($customer->gt_muat == 1 ? 2 : 0)}}"></td>
                <td class="text-center align-middle"><strong>PPN</strong></td>
                <td class="text-end align-middle">

                    {{number_format($ppn, 0, ',', '.')}}

                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="align-middle"
                    colspan="{{10 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 1 : 0) +
                                                                ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0) + ($customer->gt_bongkar == 1 ? 2 : 0) + ($customer->gt_muat == 1 ? 2 : 0)}}">
                </td>
                <td class="text-center align-middle"><strong>PPh</strong></td>
                <td class="text-end align-middle">

                    {{number_format($pph, 0, ',', '.')}}

                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="align-middle"
                    colspan="{{10 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 1 : 0) +
                                                                ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0) + ($customer->gt_bongkar == 1 ? 2 : 0) + ($customer->gt_muat == 1 ? 2 : 0)}}">
                </td>
                <td class="text-center align-middle"><strong>Tagihan</strong></td>
                <td class="text-end align-middle"> <strong>
                        {{number_format($total_tagihan-$pph+$ppn, 0, ',', '.')}}</strong>
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tfoot> --}}
    </table>
</div>
<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">


                    <form action="{{route('billing.nota-tagihan.detail-jenis.lanjut', ['customer' => $customer->id, 'jenis' => $jenis])}}" method="post" id="lanjutForm">
                        @csrf
                        <div class="mb-4">
                            <label for="dpp" class="form-label fw-bold text-secondary">Nominal DPP</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white border-end-0 text-muted">Rp</span>
                                <input type="text"
                                       name="dpp"
                                       id="dpp"
                                       class="form-control border-start-0 fw-bold text-primary shadow-none"
                                       placeholder="xxx"
                                       autocomplete="off"
                                       required>
                            </div>
                            <div class="form-text mt-2 small italic text-muted">
                                <i class="fa fa-info-circle me-1"></i> Pastikan nominal sudah benar sebelum melanjutkan.
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-primary btn-lg rounded-3 fw-bold shadow-sm" type="submit">
                                Lanjutkan <i class="fa fa-arrow-right ms-2"></i>
                            </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
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

        var table = $('#notaTable').DataTable({
            // ... your DataTable options ...
        });

          var dpp = new Cleave('#dpp', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
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
            "stateSave": true,
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
                api.column(1, {page: 'current'}).nodes().each(function (cell, i) {
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

        document.getElementById('filter_date').onchange = function() {
                document.getElementById('tanggal_filter').required = this.value !== '';
            };
        document.getElementById('tanggal_filter').onchange = function() {
            document.getElementById('filter_date').required = this.value !== '';
        };

        flatpickr("#tanggal_filter", {
            mode: "range",
            dateFormat: "d-m-Y",
        });

        function updateShow(data) {
        // Update the content of the 'show.blade.php' with the data
            document.getElementById('modalTitleId').innerText = `Nota Tagihan NOLAM ${data.kas_uang_jalan.vehicle.nomor_lambung}`;
            document.getElementById('kode').value = `UJ${(data.kas_uang_jalan.nomor_uang_jalan)}`;
            document.getElementById('tanggal_uang_jalan').value = data.kas_uang_jalan.tanggal;
            document.getElementById('no_lambung').value = data.kas_uang_jalan.vehicle.nomor_lambung;
            document.getElementById('vendor').value = data.kas_uang_jalan.vendor.nickname;
            document.getElementById('tambang').value = data.kas_uang_jalan.customer.singkatan;
            document.getElementById('rute').value = data.kas_uang_jalan.rute.nama;
            document.getElementById('nota_muat').value = data.nota_muat;
            document.getElementById('tonase').value = data.tonase;
            document.getElementById('id_tanggal_muat').value = data.id_tanggal_muat;
            document.getElementById('nota_bongkar').value = data.nota_bongkar;
            document.getElementById('timbangan_bongkar').value = data.timbangan_bongkar;
            document.getElementById('id_tanggal_bongkar').value = data.id_tanggal_bongkar;

        }

      </script>
@endpush
