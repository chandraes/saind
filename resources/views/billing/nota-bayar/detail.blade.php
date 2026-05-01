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
            <h1><u>{{$vendor->nama}}</u></h1>
        </div>
    </div>
    @include('swal')

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
                                  <td><a href="{{route('billing.nota-bayar', $vendor->id)}}"><img src="{{asset('images/back.svg')}}"
                                alt="dokumen" width="30"> Kembali</a>
                            </td>
                    <td class="align-middle"><a href="{{route('billing.nota-bayar.detail-jenis.keranjang', ['vendor' => $vendor->id, 'jenis' => $jenis])}}"><i class="fa fa-cart-arrow-down me-2" style="font-size: 30px"></i> Keranjang @if ($keranjang > 0) <span
                            class="badge bg-danger">{{$keranjang}}</span> @endif</a></td>

                </tr>
            </table>
        </div>
    </div>
</div>


<div class="container-fluid mt-3 table-responsive ">
   <table class="table table-bordered table-hover" id="notaTable">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">Tanggal UJ</th>
                <th class="text-center align-middle">Kode</th>
                <th class="text-center align-middle">Nomor Lambung</th>
                <th class="text-center align-middle">Vendor</th>
                <th class="text-center align-middle">Rute</th>
                <th class="text-center align-middle">Jarak</th>
                <th class="text-center align-middle">Harga</th>
                <th class="text-center align-middle">Tanggal Muat</th>
                <th class="text-center align-middle">Nota Muat</th>
                <th class="text-center align-middle">Tonase Muat</th>
                <th class="text-center align-middle">Tanggal Bongkar</th>
                <th class="text-center align-middle">Nota Bongkar</th>
                <th class="text-center align-middle">Tonase Bongkar</th>
                <th class="text-center align-middle">Selisih (Ton)</th>
                <th class="text-center align-middle">Selisih (%)</th>

            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            @php
                $d = $d->transaksi;
            @endphp
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->tanggal}}</td>
                <td class="align-middle">
                       <strong>UJ{{sprintf("%02d",
                                $d->kas_uang_jalan->nomor_uang_jalan)}}</strong>
                </td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->vehicle->nomor_lambung}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->vendor->nickname}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->rute->nama}}</td>
                <td class="text-center align-middle">{{$d->kas_uang_jalan->rute->jarak}}</td>
                <td class="text-center align-middle">0</td>
                <td class="text-center align-middle">{{$d->id_tanggal_muat}}</td>
                <td class="text-center align-middle">{{$d->nota_muat}}</td>
                <td class="text-center align-middle">{{$d->tonase}}</td>
                <td class="text-center align-middle">{{$d->id_tanggal_bongkar}}</td>
                <td class="text-center align-middle">{{$d->nota_bongkar}}</td>
                <td class="text-center align-middle">{{$d->timbangan_bongkar}}</td>
                <td class="text-center align-middle">{{number_format($d->tonase - $d->timbangan_bongkar, 2, ',','.')}}
                </td>
                <td class="text-center align-middle">{{number_format(($d->tonase - $d->timbangan_bongkar)*0.1, 2,
                    ',','.')}}</td>

            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@if (in_array(auth()->user()->role, ['admin', 'su']))
<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{route('billing.nota-bayar.detail-jenis.lanjut', ['vendor' => $vendor->id, 'jenis' => $jenis])}}" method="post" id="lanjutForm">
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
@endif


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

        let role = "{{ auth()->user()->role }}";

        var table = $('#notaTable').DataTable({
             "paging": false,
            "ordering": true,
            "scrollCollapse": true,
            "scrollY": "550px",
            "scrollX": true,
            "stateSave": true,
            "order": [[ 2, "asc" ]],
        });

        if (role === 'admin' || role === 'su') {
            var dpp = new Cleave('#dpp', {
                    numeral: true,
                    numeralThousandsGroupStyle: 'thousand',
                    numeralDecimalMark: ',',
                    delimiter: '.'
                });
        }




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


      </script>
@endpush
