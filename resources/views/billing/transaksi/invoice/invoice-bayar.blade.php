@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>INVOICE BAYAR</u></h1>
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
                    <td><a href="{{route('billing.index')}}"><img src="{{asset('images/billing.svg')}}"
                                alt="dokumen" width="30"> Billing</a></td>
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
                <th class="text-center align-middle">Vendor</th>
                <th class="text-center align-middle">Invoice</th>
                <th class="text-center align-middle">Total Bayar</th>
                <th class="text-center align-middle">Balance</th>
                <th class="text-center align-middle">Sisa Bayar</th>
                <th class="text-center align-middle">Pembayaran ke Vendor</th>
                @if (Auth::user()->role == 'su')
                <th class="text-center align-middle">Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-center align-middle">{{$d->tanggal}}</td>
                <td class="text-center align-middle">{{$d->vendor->nama}}</td>
                <td class="text-center align-middle">
                    <a href="{{route('invoice.bayar.detail', $d)}}">{{$d->periode}}</a>
                </td>
                <td class="text-center align-middle">
                    {{number_format($d->total_bayar, 0, ',', '.')}}
                </td>
                <td class="text-center align-middle">
                    {{number_format($d->bayar, 0, ',', '.')}}
                </td>
                <td class="text-center align-middle">
                    {{number_format($d->sisa_bayar, 0, ',', '.')}}
                </td>
                <td class="text-center align-middle">
                    <form action="{{route('invoice.bayar.lunas', $d)}}" method="post" id="lunasForm-{{$d->id}}">
                    @csrf
                        <button type="submit" class="btn btn-success">Bayar </button>
                    </form>
                </td>
                 @if (Auth::user()->role == 'su')
                <td class="text-center align-middle">
                    <form action="{{route('invoice.bayar-back.execute', ['invoice' => $d->id])}}" method="post" id="backForm{{$d->id}}" class="back-form" data-id="{{ $d->id }}">
                        @csrf
                        <button type="submit" class="btn btn-danger">Back</button>
                    </form>
                </td>
                @endif
            </tr>
            <script>
                 $('#lunasForm-{{$d->id}}').submit(function(e){
                    e.preventDefault();
                    Swal.fire({
                        title: 'Apakah anda yakin?',
                        text: "Pembayaran sebesar Rp. {{number_format($d->sisa_bayar, 0, ',', '.')}}",
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

    });

    function toggleInputTambah() {
        var value = document.getElementById('vendor_id').value;
        if (value == '') {
            document.getElementById('row-input').hidden = true;
        } else {
            document.getElementById('row-input').hidden = false;
        }
    }

     $('.back-form').submit(function(e){
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
                $(`#backForm${formId}`).unbind('submit').submit();
                $('#spinner').show();
            }
        });
    });


</script>
@endpush
