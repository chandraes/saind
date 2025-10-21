@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Keranjang Tagihan</u></h1>
        </div>
    </div>
    @php
    // $selectedData = [];

    $total_tagihan = $data ? $data->sum('nominal_tagihan') : 0;
    $ppn = $customer->ppn == 1 && $data ? $data->sum('nominal_tagihan') * 0.11 : 0;
    $pph = $customer->pph == 1 && $data ? $data->sum('nominal_tagihan') * 0.02 : 0;

    $profit = $data->sum('profit');
    $profit_persen = count($data) > 0 ? ($data->sum('profit') / $data->sum('nominal_bayar')) * 100 : 0;
    @endphp
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>{{$customer->nama}} ({{$customer->singkatan}})</u></h1>
        </div>
    </div>
    @include('swal')
    {{-- if errors has any --}}
    @if ($errors->any())
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Whoops!</strong> Ada kesalahan saat input data, yaitu:
                <ul>
                    @foreach ($errors->all() as $error)
                    <li><strong>{{$error}}</strong></li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @endif

    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('billing.index')}}"><img src="{{asset('images/billing.svg')}}" alt="dokumen"
                                width="30"> Billing</a></td>
                    <td><a href="{{route('transaksi.nota-tagihan', ['customer'=>$customer->id])}}"><img
                                src="{{asset('images/back.svg')}}" alt="dokumen" width="30"> Kembali</a></td>
                    <td>
                        <form target="_blank" action="{{route('transaksi.nota-tagihan.keranjang.export', $customer)}}"
                            method="get">
                            <input type="hidden" name="rute_id" value="{{$rute_id}}">
                            <input type="hidden" name="tanggal_filter" value="{{$tanggal_filter}}">
                            <input type="hidden" name="filter_date" value="{{$filter_date}}">
                            <div class="row">
                                <button class="btn btn-success" type="submit">Export</button>
                            </div>

                        </form>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
@include('billing.transaksi.tagihan.keranjang.filter')
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
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center
                    align-middle">Tagihan</th>
                <th @if($customer->gt_muat == 1 | $customer->gt_bongkar == 1) rowspan="2" @endif class="text-center
                    align-middle">Action</th>
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
                    {{number_format($d->harga_customer, 0, ',', '.')}}
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
                <td class="text-end align-middle">
                    @if ($d->kas_uang_jalan->customer->tagihan_dari == 1)
                    {{number_format(($d->nominal_tagihan), 0, ',', '.')}}
                    @elseif ($d->kas_uang_jalan->customer->tagihan_dari == 2)
                    {{number_format(($d->nominal_tagihan), 0, ',', '.')}}
                    @endif
                </td>
                <td class="text-center align-middle">
                    <form
                        action="{{route('transaksi.nota-tagihan.keranjang.delete', ['customer' => $customer->id, 'transaksi' => $d->id])}}"
                        method="post" id="masukForm{{$d->id}}">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <script>
                $('#masukForm{{$d->id}}').submit(function(e){
                  e.preventDefault();

                  Swal.fire({
                      title: 'Apakah anda yakin?',
                      text: "Data akan kembali ke nota tagihan!!",
                      icon: 'warning',
                      showCancelButton: true,
                      confirmButtonColor: '#3085d6',
                      cancelButtonColor: '#6c757d',
                      confirmButtonText: 'Ya, simpan!'
                      }).then((result) => {
                      if (result.isConfirmed) {
                          this.submit();
                      }
                  })
              });
            </script>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class=""
                    colspan="{{9 + ($customer->tanggal_muat == 1 ? 1 : 0) + ($customer->nota_muat == 1 ? 1 : 0) + ($customer->tonase == 1 ? 1 : 0) +
                                                                ($customer->tanggal_bongkar == 1 ? 1 : 0) + ($customer->selisih == 1 ? 2 : 0) + ($customer->gt_bongkar == 1 ? 2 : 0) + ($customer->gt_muat == 1 ? 2 : 0)}}">

                </td>
                <td class="text-center align-middle"><strong>Total Dpp</strong></td>
                <td class="text-end align-middle">{{number_format($total_tagihan, 0, ',', '.')}}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
<input type="hidden" name="total_tagihan" id="total_tagihan" value="0">
<hr>
<div class="container mt-3 mb-3">
    @if (auth()->user()->role == 'admin' || auth()->user()->role == 'su')
    <div class="card border-primary">
        <div class="card-body">
            <form action="{{route('transaksi.nota-tagihan.keranjang.lanjut', $customer)}}" method="post"
                id="lanjutForm">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="total" class="form-label">Total Dpp</label>
                            <input type="text" class="form-control" name="total" id="total"
                                value="{{number_format($total_tagihan, 0, ',', '.')}}" disabled />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="penyesuaian" class="form-label">Penyesuaian BBM</label>
                            <input type="text" class="form-control" name="penyesuaian" id="penyesuaian" required
                                value="0" onkeyup="calculateTotal()" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="penalty" class="form-label">Penalti</label>
                            <input type="text" class="form-control" name="penalty" id="penalty" required
                                value="0" onkeyup="calculateTotal()" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="gt_dpp" class="form-label">Grand Total DPP</label>
                            <input type="text" class="form-control" name="gt_dpp" id="gt_dpp"
                                value="{{number_format($total_tagihan, 0, ',', '.')}}" disabled />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="ppn" class="form-label">Ppn</label>
                            <input type="text" class="form-control" name="ppn" id="ppn"
                                value="{{number_format($ppn, 0, ',', '.')}}" disabled />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="Pph" class="form-label">Pph</label>
                            <input type="text" class="form-control" name="pph" id="pph"
                                value="{{number_format($pph, 0, ',', '.')}}" disabled />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="tagi" class="form-label"><strong>Total Tagihan</strong></label>
                            <input type="text" class="form-control" name="tagi" id="tagi"
                                value="{{number_format($total_tagihan-$pph+$ppn, 0, ',', '.')}}" disabled />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="ppn" class="form-label">Ppn Disetor Oleh</label>
                            <select name="ppn_dipungut" id="ppn_dipungut" class="form-select" required onchange="calculateTotal()">
                                <option value="">-- Pilih Salah Satu --</option>
                                <option value="1">Sendiri</option>
                                <option value="0">Customer</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="penalty" class="form-label">Charges</label>
                            <input type="text" class="form-control" name="penalty_akhir" id="penalty_akhir" required
                                value="0" onkeyup="calculateTotal()" />
                        </div>
                    </div>
                     <div class="col-md-3">
                        <div class="mb-3">
                            <label for="tagi" class="form-label"><strong>Grand Total Tagihan</strong></label>
                            <input type="text" class="form-control" name="tagi_akhir" id="tagi_akhir"
                                value="{{number_format($total_tagihan-$pph+$ppn, 0, ',', '.')}}" disabled />
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="tanggal_hardcopy" class="form-label">Tanggal Submit Hardcopy</label>
                            <input type="text" class="form-control" name="tanggal_hardcopy" id="tanggal_hardcopy" value="{{old('tanggal_hardcopy')}}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="estimasi_pembayaran" class="form-label">Estimasi Pembayaran</label>
                            <input type="text" class="form-control" name="estimasi_pembayaran" id="estimasi_pembayaran" value="{{old('estimasi_pembayaran')}}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="no_resi" class="form-label">Nomor Resi</label>
                            <input type="text" class="form-control" name="no_resi" id="no_resi" value="{{old('no_resi')}}" required/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="no_validasi" class="form-label">Nomor Validasi</label>
                            <input type="text" class="form-control" name="no_validasi" id="no_validasi" value="{{old('no_validasi')}}" required/>
                        </div>
                    </div>
                </div>
                <div class="row px-5 mt-3">
                    <button class="btn btn-primary me-md-3" type="submit">Lanjutkan Pilihan</button>
                </div>

            </form>
        </div>
    </div>
    @endif
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
    function calculateTotal()
    {
        var settingCustomerPpn = {{$customer->ppn}};
        var settingCustomerPph = {{$customer->pph}};

        var total = parseFloat($('#total').val().replace(/\./g, '').replace(',', '.')) || 0;
        var penyesuaian = parseFloat($('#penyesuaian').val().replace(/\./g, '').replace(',', '.')) || 0;
        var penalty = parseFloat($('#penalty').val().replace(/\./g, '').replace(',', '.')) || 0;
        var penalty_akhir = parseFloat($('#penalty_akhir').val().replace(/\./g, '').replace(',', '.')) || 0;

        var dipungut = $('#ppn_dipungut').val() || 1;

        var grandTotal = total + penyesuaian - penalty;

        $('#gt_dpp').val(grandTotal.toLocaleString('id-ID'));

        var ppn = settingCustomerPpn ? Math.round(grandTotal * 0.11) : 0;
        $('#ppn').val(ppn.toLocaleString('id-ID'));

        var pph = settingCustomerPph ? Math.round(grandTotal * 0.02) : 0;
        $('#pph').val(pph.toLocaleString('id-ID'));

        if(dipungut == 1){
            var totalTagihan = grandTotal + ppn - pph;
        } else {
            var totalTagihan = grandTotal - pph;
        }

        var tagihanAkhir = totalTagihan - penalty_akhir;

        $('#tagi').val(totalTagihan.toLocaleString('id-ID'));

        $('#tagi_akhir').val(tagihanAkhir.toLocaleString('id-ID'));

    }


    $(document).ready(function() {
         var role = "{{auth()->user()->role}}";
        if (role =='admin' || role =='su') {
           calculateTotal();

           var penyesuaian = new Cleave('#penyesuaian', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });

            var penalty = new Cleave('#penalty', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });

            flatpickr("#tanggal_hardcopy", {
                dateFormat: "d-m-Y",

            });

            flatpickr("#estimasi_pembayaran", {
                dateFormat: "d-m-Y",

            });

        }

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
