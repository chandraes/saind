@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>INVOICE BONUS</u></h1>
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
                    <td><a href="{{route('billing.transaksi.invoice.index')}}"><img src="{{asset('images/invoice.svg')}}"
                                alt="dokumen" width="30"> Invoice</a></td>
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
                <th class="text-center align-middle">Sponsor</th>
                <th class="text-center align-middle">Invoice</th>
                <th class="text-center align-middle">Total Bonus</th>
                <th class="text-center align-middle">Balance</th>
                <th class="text-center align-middle">Sisa Bonus</th>
                <th class="text-center align-middle">Bayar</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-center align-middle">{{$d->tanggal}}</td>
                <td class="text-center align-middle">{{$d->sponsor->nama}}</td>
                <td class="text-center align-middle">
                    {{$d->periode}}
                </td>
                <td class="text-center align-middle">
                    {{number_format($d->total_bonus, 0, ',', '.')}}
                </td>
                <td class="text-center align-middle">
                    {{number_format($d->total_bayar, 0, ',', '.')}}
                </td>
                <td class="text-center align-middle">
                    {{number_format($d->sisa_bonus, 0, ',', '.')}}
                </td>
                <td class="text-center align-middle">
                    <form action="{{route('invoice.bonus.lunas', $d)}}" method="post" id="lunasForm-{{$d->id}}">
                    @csrf
                        <button type="submit" class="btn btn-success">Pembayaran </button>
                    </form>
                </td>
            </tr>
            <script>
                 $('#lunasForm-{{$d->id}}').submit(function(e){
                    e.preventDefault();
                    Swal.fire({
                        title: 'Apakah anda yakin?',
                        text: "Pelunasan Tagihan sebesar Rp. {{number_format($d->sisa_bonus, 0, ',', '.')}}",
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
