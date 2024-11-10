@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>DETAIL PPN</u></h1>
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
                    <td><a href="{{route('pajak.rekap-ppn')}}"><img src="{{asset('images/back.svg')}}" alt="dokumen"
                                width="30"> Back</a></td>
                </tr>
            </table>
        </div>

    </div>
</div>

<div class="container table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-hover table-bordered" id="rekapTable">
            <thead class=" table-success">
                <tr>
                    <th class="text-center align-middle">Tanggal Input</th>
                    <th class="text-center align-middle">Nota</th>
                    <th class="text-center align-middle">Customer</th>
                    <th class="text-center align-middle">Uraian</th>
                    <th class="text-center align-middle">Faktur</th>
                    <th class="text-center align-middle">Disetor<br>Sendiri</th>
                    <th class="text-center align-middle">Disetor<br>Konsumen</th>

                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>

                    <td class="text-center align-middle">{{$d->invoiceTagihan ? $d->invoiceTagihan->id_tanggal : '-'}}</td>
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
                    <td class="text-center align-middle">{{$d->no_faktur}}</td>

                    <td class="text-end align-middle">
                        @if ($d->dipungut == 1)
                        {{$d->nf_nominal}}
                        @else
                        0
                        @endif

                    </td>
                    <td class="text-end align-middle">
                        @if ($d->dipungut == 0)
                        {{$d->nf_nominal}}
                        @else
                        0
                        @endif

                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="5">Grand Total</th>
                    <th class="text-end align-middle">{{number_format($data->where('dipungut', 1)->sum('nominal'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->where('dipungut', 0)->sum('nominal'), 0, ',', '.')}}</th>

                </tr>
            </tfoot>
        </table>
    </div>
    <hr>
    <div class="row justify-content-end">
        {{-- <div class="col-md-6">
            <div class="row">
                <div class="col-md-7 pt-2 text-end">
                    <label for="ppn_keluaran" class="form-label">Saldo PPN Masukan :</label>
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control text-end" name="ppn_keluaran" id="ppn_keluaran" value="{{number_format($saldoMasukan, 0, ',','.')}}" disabled/>
                </div>
            </div>
            <div class="row">
                <div class="col-md-7 pt-2 text-end">
                    <label for="ppn_keluaran" class="form-label">PPN Keluaran : </label>
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control text-end" name="ppn_keluaran" id="ppn_keluaran" value="{{number_format($data->sum('nominal'), 0, ',', '.')}}" disabled/>
                </div>
            </div>
            <div class="row">
                <div class="col-md-7 pt-2 text-end">
                    <label for="ppn_keluaran" class="form-label">Saldo Setelah Pengurangan : </label>
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control text-end" name="ppn_keluaran" id="ppn_keluaran" value="{{number_format($saldoMasukan-$data->sum('nominal'), 0, ',', '.')}}" disabled/>
                </div>
            </div>
            <div class="row">
                <div class="col-md-7 pt-2 text-end">
                    <label for="ambil_dari_kas" class="form-label">Ambil dari Kas Besar : </label>
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control text-end" name="ambil_dari_kas" id="ambil_dari_kas" value="{{ $dariKas }}" disabled/>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-7 pt-2 text-end">

                </div>
                <div class="col-md-5">
                    <form action="{{route('pajak.ppn-keluaran.keranjang-lanjut')}}" method="post" id="lanjutForm">
                        @csrf
                    <div class="row px-3">
                        <button type="submit" class="btn btn-primary">Lanjutkan</button>
                    </div>
                </form>
                </div>
            </div>
        </div> --}}

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


        $('#rekapTable').DataTable({
            "paging": false,
            "info": false,
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




</script>
@endpush
