@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>PPN MASUKAN</u></h1>
            {{-- <h1>{{$stringBulanNow}} {{$tahun}}</h1> --}}
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-8">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('pajak.index')}}"><img src="{{asset('images/pajak.svg')}}" alt="dokumen"
                                width="30">
                            PAJAK</a></td>
                    <td><a href="#" data-bs-toggle="modal" data-bs-target="#keranjangModal"><i class="fa fa-shopping-cart h3 me-2"></i>
                        Keranjang {!! $keranjang > 0 ? "<span class='text-danger'>($keranjang)</span>" : '' !!}</a></td>
                </tr>
            </table>
        </div>

    </div>
</div>

@include('pajak.ppn-masukan.faktur-modal')
@include('pajak.ppn-masukan.show-faktur')
@include('pajak.ppn-masukan.keranjang')
<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">
        <form action="{{route('pajak.ppn-masukan.keranjang-store')}}" method="post" id="keranjangForm">
            @csrf
            <div class="row">
                <div class="col-md-2 text-end">
                    <div class="mb-3 pt-2">
                        <label for="total_tagihan" >Nominal Dipilih :</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <input type="text" class="form-control" name="total_tagihan" id="total_tagihan" value="0"
                            hidden />
                        <input type="hidden" name="selectedData" required>
                        <input type="text" class="form-control" name="total_tagihan_display" id="total_tagihan_display"
                            value="0" disabled />

                    </div>
                </div>
                <div class="col-md-2">
                    <div class="row">
                        <button class="btn btn-success">Masukan Keranjang</button>
                    </div>
                </div>
            </div>

        </form>
        <table class="table table-hover table-bordered" id="rekapTable">
            <thead class=" table-success">
                <tr>
                    <th class="text-center align-middle">

                        {{-- select all --}}
                        <input style="height: 25px; width:25px" type="checkbox" onclick="checkAll(this)" id="checkAll">
                    </th>
                    <th class="text-center align-middle">Tanggal Input</th>
                    <th class="text-center align-middle">Nota</th>
                    <th class="text-center align-middle">Vendor</th>
                    <th class="text-center align-middle">Uraian</th>
                    {{-- <th class="text-center align-middle">Tanggal Bayar</th> --}}
                    <th class="text-center align-middle">Sebelum<br>Terbit<br>Faktur</th>
                    <th class="text-center align-middle">Setelah<br>Terbit<br>Faktur</th>
                    <th class="text-center align-middle">ACT</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">
                        {{-- checklist on check push $d->id to $selectedData --}}
                        <input style="height: 25px; width:25px" type="checkbox" value="{{$d->id}}"
                            data-tagihan="{{$d->nominal}}" onclick="check(this, {{$d->id}})" id="idSelect-{{$d->id}}"
                            {{$d->is_faktur == 0 ? 'disabled' : ''}}>
                    </td>
                    <td class="text-center align-middle">
                        {{$d->invoiceBayar ? $d->invoiceBayar->id_tanggal : $d->tanggal}}
                    </td>
                    <td class="text-center align-middle">
                        @if ($d->invoiceBayar)
                        <a href="{{route('invoice.bayar.detail', ['invoiceBayar' => $d->invoice_bayar_id])}}">
                            {{$d->invoiceBayar->periode}}
                        </a>
                        @endif
                    </td>
                    <td class="text-center align-middle">
                        {{$d->invoiceBayar ? $d->invoiceBayar->vendor->nama : '-'}}
                    </td>
                    <td class="text-start align-middle">
                        {{$d->uraian}}
                    </td>
                    {{-- <td class="text-center align-middle">{{$d->tanggal}}</td> --}}
                    <td class="text-end align-middle">
                        @if ($d->is_faktur == 0)
                        {{$d->nf_nominal}}
                        @else
                        0
                        @endif

                    </td>
                    <td class="text-end align-middle">
                        @if ($d->is_faktur == 1)
                        <a href="#" onclick="showFaktur({{$d->id}})" data-bs-toggle="modal"
                            data-bs-target="#showModal">{{$d->nf_nominal}}</a>

                        @else
                        0
                        @endif
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-{{$d->is_faktur == 1 ? 'warning' : 'primary'}} btn-sm"
                            data-bs-toggle="modal" data-bs-target="#modalFaktur" onclick="faktur({{$d->id}})">
                            {{$d->is_faktur == 1 ? 'Ubah' : ''}} Faktur
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="5">Grand Total</th>
                    <th class="text-end align-middle">{{number_format($total_blm_faktur, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($total_faktur, 0, ',','.')}}</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    function getDataById(id) {
        const data = @json($data);
        return data.find(x => x.id == id);
    }

    function showFaktur(id) {
        const d = getDataById(id);

        $('#no_faktur_show').val(d.is_faktur == 1 ? d.no_faktur : 'Faktur Belum Terisi');
    }

    function faktur(id) {
        const form = document.getElementById('fakturForm');
        form.action = `/pajak/ppn-masukan/store-faktur/${id}`;
        form.reset();

        const d = getDataById(id);

        $('#nota').val(d.invoice_bayar_id != null ? d.invoice_bayar.periode : 'Nota Belum Terisi');
        $('#nominal').val(d.nf_nominal);
        $('#no_faktur').val(d.is_faktur == 1 ? d.no_faktur : '');
    }

    $(document).ready(function() {
        // reset selectedData
        $('input[name="selectedData"]').val('');
        $('#rekapTable').DataTable({
            "paging": false,
            "ordering": true,
            "searching": false,
            "scrollCollapse": true,
            "scrollY": "400px",
            // default order column 1
            "order": [
                [2, 'asc']
            ],
            // "rowCallback": function(row, data, index) {
            //     // Update the row number
            //     $('td:eq(0)', row).html(index + 1);
            // }

        });

        $('#keranjangTable').DataTable({
            "paging": false,
            'info': false,
            "ordering": true,
            "searching": false,
            "scrollCollapse": true,
            "scrollY": "400px",
            // default order column 1
            "order": [
                [1, 'asc']
            ],
            // "rowCallback": function(row, data, index) {
            //     // Update the row number
            //     $('td:eq(0)', row).html(index + 1);
            // }

        });
        $('#keranjangModal').on('shown.bs.modal', function() {
            $('#keranjangTable').DataTable().columns.adjust().draw();
        });

        $('#keranjangForm').submit(function(e){
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



    });

    function check(checkbox, id) {
        var totalTagihan = parseFloat($('#total_tagihan').val()) || 0;
        var tagihan = parseFloat($(checkbox).data('tagihan'));

        if (checkbox.checked) {
            totalTagihan += tagihan;
            $('input[name="selectedData"]').val(function(i, v) {
                // if end of string, remove comma
                return v + id + ',';

            });
        } else {
            totalTagihan -= tagihan;

            $('input[name="selectedData"]').val(function(i, v) {
                // remove id from string
                return v.replace(id + ',', '');
            });
        }

        $('#total_tagihan').val(totalTagihan);
        $('#total_tagihan_display').val(totalTagihan.toLocaleString('id-ID'));

        value = $('input[name="selectedData"]').val();

        if(value.slice(-1) == ','){
            // remove comma from last number
            value = value.slice(0, -1);
        }
        console.log(value);
    }

    // check all checkbox and push all id to $selectedData and check all checkbox
    function checkAll(checkbox, id) {
        $('#total_tagihan').val(0);
        $('#total_tagihan_display').val(0);
        var totalTagihan = parseFloat($('#total_tagihan').val()) || 0;

        if (checkbox.checked) {
            $('input[name="selectedData"]').val(function(i, v) {
                // if end of string, remove comma
                @foreach ($data as $d)
                if({{$d->is_faktur}} == 1) {
                var tagihan = parseFloat($('#idSelect-{{$d->id}}').data('tagihan'));
                totalTagihan += tagihan;

                    v = v + {{$d->id}} + ',';
                    $('#idSelect-{{$d->id}}').prop('checked', true);
                }
                @endforeach
                return v;
            });
        } else {
            $('input[name="selectedData"]').val(function(i, v) {
                // remove id from string
                @foreach ($data as $d)
                    v = v.replace({{$d->id}} + ',', '');
                    $('#idSelect-{{$d->id}}').prop('checked', false);
                @endforeach
                return v;
            });
            totalTagihan = 0;
        }

        $('#total_tagihan').val(totalTagihan);
        $('#total_tagihan_display').val(totalTagihan.toLocaleString('id-ID'));

        value = $('input[name="selectedData"]').val();

        if(value.slice(-1) == ','){
            // remove comma from last number
            value = value.slice(0, -1);
        }
        console.log(value);
    }


</script>
@endpush
