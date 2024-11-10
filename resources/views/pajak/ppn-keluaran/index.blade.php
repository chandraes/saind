@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>PPN KELUARAN</u></h1>
            {{-- <h1>{{$stringBulanNow}} {{$tahun}}</h1> --}}
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('pajak.index')}}"><img src="{{asset('images/pajak.svg')}}" alt="dokumen"
                                width="30">
                            PAJAK</a></td>
                    <td><a href="@if($keranjang > 0) {{route('pajak.ppn-keluaran.keranjang')}} @else # @endif"><i
                                class="fa fa-shopping-cart h3 me-2"></i>
                            Keranjang {!! $keranjang > 0 ? "<span class='text-danger'>($keranjang)</span>" : '' !!}</a>
                    </td>
                </tr>
            </table>
        </div>

    </div>
</div>
@include('pajak.ppn-keluaran.faktur-modal')
@include('pajak.ppn-keluaran.show-faktur')

<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">
        <form action="{{route('pajak.ppn-keluaran.keranjang-store')}}" method="post" id="keranjangForm">
            @csrf
            <div class="row">
                <div class="col-md-2 text-end">
                    <div class="mb-3 pt-2">
                        <label for="total_tagihan">Nominal Dipilih :</label>
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
                    <th class="text-center align-middle">Customer</th>
                    <th class="text-center align-middle">Uraian</th>
                    {{-- <th class="text-center align-middle">Tanggal Bayar</th> --}}
                    <th class="text-center align-middle">Sebelum Terbit Faktur</th>
                    <th class="text-center align-middle">Setelah<br>Terbit<br>Faktur</th>
                    <th class="text-center align-middle">ACT</th>
                </tr>
            </thead>
            <tbody>
                @php
                $totalNonNpwp = 0;
                $totalNpwp = 0;
                $totalFaktur = 0;
                @endphp
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">
                        {{-- checklist on check push $d->id to $selectedData --}}
                        <input style="height: 25px; width:25px" type="checkbox" value="{{$d->id}}"
                            data-tagihan="{{$d->nominal}}" onclick="check(this, {{$d->id}})" id="idSelect-{{$d->id}}"
                            {{$d->is_faktur == 0 ? 'disabled' : ''}}>
                    </td>
                    <td class="text-center align-middle">{{$d->invoiceTagihan ? $d->invoiceTagihan->tanggal : '-'}}</td>
                    <td class="text-center align-middle">
                        @if ($d->invoiceTagihan)
                        <a href="{{route('invoice.tagihan.detail', ['invoice' => $d->invoice_tagihan_id])}}">
                            {{$d->invoiceTagihan->periode}}
                        </a>
                        @endif

                    </td>
                    <td class="text-center align-middle">{{$d->invoiceTagihan->customer ? $d->invoiceTagihan->customer->singkatan : ""}}</td>
                    <td class="text-start align-middle">
                        {{$d->uraian}}
                    </td>
                    <td
                        class="text-end align-middle @if ($d->dipungut == 0 && $d->is_faktur == 0) table-danger @endif ">
                        @if ($d->is_faktur == 0)
                        {{$d->nf_nominal}}
                        @php
                        $totalNpwp += $d->nominal;
                        @endphp
                        @else
                        0
                        @endif
                    </td>
                    <td
                        class="text-end align-middle  @if ($d->dipungut == 0 && $d->is_faktur == 1) table-danger @endif ">
                        @if ($d->is_faktur == 1)
                        <a href="#" onclick="showFaktur({{$d->id}})" data-bs-toggle="modal"
                            data-bs-target="#showModal">{{$d->nf_nominal}}</a>
                        @php
                        $totalFaktur += $d->nominal;
                        @endphp
                        @else
                        0
                        @endif
                    </td>
                    <td class="text-center align-middle">
                        {{-- @if ($d->is_faktur == 0 && (strlen($d->invoiceTagihan->customer ? $d->invoiceTagihan->customer->npwp : $d->invoiceTagihan->customer_temp->npwp) < 10))
                        <form
                            action="{{route('pajak.ppn-keluaran.expired', ['ppnKeluaran' => $d->id])}}" method="post" class="d-inline expired-form" id="expiredForm{{ $d->id }}" data-id="{{ $d->id }}">
                            @csrf

                            <button type="submit" class="btn btn-danger btn-sm">
                                Expired
                            </button>
                            </form>
                            @endif --}}
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
                    <th class="text-end align-middle">{{number_format($totalNpwp, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($totalFaktur, 0, ',','.')}}</th>
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
        form.action = `/pajak/ppn-keluaran/store-faktur/${id}`;
        form.reset();

        const d = getDataById(id);

        $('#nota').val(d.invoice_tagihan_id != null ? d.invoice_tagihan.periode : 'Nota Belum Terisi');
        $('#nominal').val(d.nf_nominal);
        $('#no_faktur').val(d.is_faktur == 1 ? d.no_faktur : '');
    }

    $(document).ready(function() {

    $('.expired-form').submit(function(e){
        e.preventDefault();
        var formId = $(this).data('id');
        Swal.fire({
            title: 'Apakah Anda Yakin?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, simpan!'
        }).then((result) => {
            if (result.isConfirmed) {
                $(`#expiredForm${formId}`).unbind('submit').submit();
                $('#spinner').show();
            }
        });
    });
        $('#rekapTable').DataTable({
            "paging": false,
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
