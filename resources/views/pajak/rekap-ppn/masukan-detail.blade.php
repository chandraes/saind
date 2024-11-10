@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>DETAIL PPN MASUKAN</u></h1>
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
                            <td><a href="{{ url()->previous() }}"><img src="{{asset('images/back.svg')}}" alt="dokumen"
                                width="30">
                            BACK</a></td>
                </tr>
            </table>
        </div>

    </div>
</div>


<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">

        <table class="table table-hover table-bordered" id="rekapTable">
            <thead class=" table-success">
                <tr>

                    <th class="text-center align-middle">Tanggal Input</th>
                    <th class="text-center align-middle">Nota</th>
                    <th class="text-center align-middle">Supplier</th>
                    <th class="text-center align-middle">Uraian</th>
                    <th class="text-center align-middle">No Faktur</th>
                    <th class="text-center align-middle">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>

                    <td class="text-center align-middle">
                        {{$d->invoiceBelanja->tanggal}}
                    </td>
                    <td class="text-center align-middle">
                        @if ($d->invoiceBelanja)
                        <a href="{{route('billing.invoice-supplier.detail', ['invoice' => $d->invoice_belanja_id])}}">
                            {{$d->invoiceBelanja->kode}}
                        </a>
                        @endif
                    </td>
                    <td class="text-center align-middle">
                        {{$d->invoiceBelanja->supplier->nama}}
                    </td>
                    <td class="text-start align-middle">
                        {{$d->uraian}}
                    </td>
                    <td class="text-center align-middle">{{$d->no_faktur}}</td>
                    <td class="text-end align-middle">
                        {{$d->nf_nominal}}

                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="5">Grand Total</th>
                    <th class="text-end align-middle">{{number_format($data->sum('nominal'), 0, ',','.')}}</th>
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


    $(document).ready(function() {
        // reset selectedData
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



</script>
@endpush
