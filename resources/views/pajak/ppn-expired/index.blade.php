@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>PPN KELUARAN EXPIRED</u></h1>
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
                    <th class="text-center align-middle">Konsumen</th>
                    <th class="text-center align-middle">Uraian</th>
                    {{-- <th class="text-center align-middle">Tanggal Bayar</th> --}}
                    <th class="text-center align-middle">Nominal</th>
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

                    <td class="text-center align-middle">{{$d->invoiceJual ? $d->invoiceJual->tanggal : '-'}}</td>
                    <td class="text-center align-middle">
                        @if ($d->invoiceJual)
                        <a href="{{route('billing.invoice-konsumen.detail', ['invoice' => $d->invoice_jual_id])}}">
                            {{$d->invoiceJual->kode}}
                        </a>
                        @endif

                    </td>
                    <td class="text-center align-middle">{{$d->invoiceJual->konsumen ? $d->invoiceJual->konsumen->nama :
                        $d->invoiceJual->konsumen_temp->nama}}</td>
                    <td class="text-start align-middle">
                        {{$d->uraian}}
                    </td>
                    <td
                        class="text-end align-middle @if ($d->dipungut == 0 && $d->is_faktur == 0) table-danger @endif ">
                       {{$d->nf_nominal}}
                    </td>

                    <td class="text-center align-middle">
                        @if ($d->is_faktur == 0 && (strlen($d->invoiceJual->konsumen ? $d->invoiceJual->konsumen->npwp : $d->invoiceJual->konsumen_temp->npwp) < 10)) {{-- form to expired button --}}
                        <form
                            action="{{route('pajak.ppn-expired.back', ['ppnKeluaran' => $d->id])}}" method="post" class="d-inline expired-form" id="expiredForm{{ $d->id }}" data-id="{{ $d->id }}">
                            @csrf

                            <button type="submit" class="btn btn-danger btn-sm">
                                Back
                            </button>
                            </form>
                            @endif

                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="4">Grand Total</th>
                    <th class="text-end align-middle">{{number_format($data->sum('nominal'), 0, ',','.')}}</th>
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

        $('#nota').val(d.invoice_jual_id != null ? d.invoice_jual.kode : 'Nota Belum Terisi');
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



</script>
@endpush
