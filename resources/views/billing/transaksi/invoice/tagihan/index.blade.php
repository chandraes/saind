@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>INVOICE CUSTOMER</u></h1>
        </div>
    </div>
    @include('swal')
    {{-- error validation show in swal --}}
    @if ($errors->any())
    <script>
        Swal.fire({
            title: 'Error!',
            text: '{{$errors->first()}}',
            icon: 'error',
            confirmButtonText: 'Ok'
        })
    </script>
    @endif
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('billing.transaksi.index')}}"><img src="{{asset('images/transaction.svg')}}"
                                alt="dokumen" width="30"> Transaksi</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container mt-5 table-responsive ">
    <table class="table table-bordered table-hover" id="data-table">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">Tanggal</th>
                <th class="text-center align-middle">Tambang</th>
                <th class="text-center align-middle">Invoice</th>
                <th class="text-center align-middle">Estimasi Nominal Invoice</th>
                <th class="text-center align-middle">Balance</th>
                <th class="text-center align-middle">Sisa Tagihan</th>
                <th class="text-center align-middle">Lunas</th>
                <th class="text-center align-middle">Cicil</th>
                @if (auth()->user()->role === 'su')
                <th class="text-center align-middle">Action</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-center align-middle">{{$d->tanggal}}</td>
                <td class="text-center align-middle">{{$d->customer->singkatan}}</td>
                <td class="text-center align-middle">
                    <a href="{{route('invoice.tagihan.detail', $d)}}"> {{$d->periode}}</a>
                </td>
                <td class="text-center align-middle">
                    {{number_format($d->total_tagihan, 0, ',', '.')}}
                </td>
                <td class="text-center align-middle">
                    {{number_format($d->total_bayar, 0, ',', '.')}}
                </td>
                <td class="text-center align-middle">
                    {{number_format($d->sisa_tagihan, 0, ',', '.')}}
                </td>
                <td class="text-center align-middle">
                    <form action="{{route('invoice.tagihan.lunas', $d)}}" method="post" id="lunasForm-{{$d->id}}">
                    @csrf
                        <button type="submit" class="btn btn-success">Pelunasan </button>
                    </form>
                </td>
                <td class="text-center align-middle">
                    <!-- Modal trigger button -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cicil-{{$d->id}}">
                      Cicilan
                    </button>

                    <!-- Modal Body -->
                    <!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
                    <div class="modal fade" id="cicil-{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTitleId">Jumlah Cicilan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{route('invoice.tagihan.cicil', $d)}}" method="post" id="cicilForm-{{$d->id}}">
                                    @csrf
                                <div class="modal-body">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon1">Rp</span>
                                        <input type="text" class="form-control @if ($errors->has('nominal_transaksi'))
                                        is-invalid
                                    @endif" name="cicilan" id="cicilanInput-{{$d->id}}" required data-thousands="." >
                                      </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                            </div>
                        </div>
                    </div>


                    <!-- Optional: Place to the bottom of scripts -->
                    <script>
                        $('#cicilanInput-{{$d->id}}').maskMoney({
                            thousands: '.',
                            decimal: ',',
                            precision: 0
                        });

                    </script>
                </td>
                @if (auth()->user()->role === 'su')
                <td class="text-center align-middle">
                    <a class="btn btn-danger" href="{{route('invoice.tagihan-back.execute', $d)}}">Kembalikan</a>
                </td>
                @endif
            </tr>
            {{-- <button class="btn btn-primary">Test</button> --}}
            <script>
                 $('#lunasForm-{{$d->id}}').submit(function(e){
                    e.preventDefault();
                    Swal.fire({
                        title: 'Apakah anda yakin?',
                        text: "Pelunasan Tagihan sebesar Rp. {{number_format($d->sisa_tagihan, 0, ',', '.')}}",
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

                $('#cicilForm-{{$d->id}}').submit(function(e){
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
                            this.submit();
                        }
                    })
                });
            </script>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/plugins/date-picker/date-picker.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    // hide alert after 5 seconds


    $(document).ready(function() {
        $('#data-table').DataTable();

    } );

    function toggleInputTambah() {
        var value = document.getElementById('vendor_id').value;
        if (value == '') {
            document.getElementById('row-input').hidden = true;
        } else {
            document.getElementById('row-input').hidden = false;
        }
    }
</script>
@endpush
