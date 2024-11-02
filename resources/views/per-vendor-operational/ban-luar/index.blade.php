@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>BAN LUAR</u></h1>
            {{-- <h1>{{$nama_bulan}} {{$tahun}}</h1> --}}
        </div>
    </div>
    @include('swal')
    {{-- @include('per-vendor-operational.ban-luar.tambah') --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Whoops!</strong> Terjadi kesalahan.<br><br>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </ul>
        </div>
    @endif
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-md-6 d-flex justify-content-start">
            <table>
                <tr>
                    <td>
                        <h5>Nomor Lambung</h5>
                    </td>
                    <td style="padding-left:10px;padding-right:10px">
                        <h5>:</h5>
                    </td>
                    <td>
                        <h5>SAI{{$vehicle->nomor_lambung}}</h5>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5>Nama Driver</h5>
                    </td>
                    <td style="padding-left:10px;padding-right:10px">
                        <h5>:</h5>
                    </td>
                    <td>
                        <h5>{{$vehicle->nama_driver}}</h5>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5>Pengurus</h5>
                    </td>
                    <td style="padding-left:10px;padding-right:10px">
                        <h5>:</h5>
                    </td>
                    <td>
                        <h5>{{$vehicle->pengurus}}</h5>
                    </td>
                </tr>
            </table>
        </div>
        {{-- <div class="col-md-6 d-flex justify-content-end">
            <table>
                <tr>
                    <td>
                        <h5>Nama Rekening</h5>
                    </td>
                    <td style="padding-left:10px;padding-right:10px">
                        <h5>:</h5>
                    </td>
                    <td>
                        <h5>{{$ug->nama_rek}}</h5>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5>Nomor Rekening</h5>
                    </td>
                    <td style="padding-left:10px;padding-right:10px">
                        <h5>:</h5>
                    </td>
                    <td>
                        <h5>{{$ug->no_rek}}</h5>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5>Bank</h5>
                    </td>
                    <td style="padding-left:10px;padding-right:10px">
                        <h5>:</h5>
                    </td>
                    <td>
                        <h5>{{$ug->bank}}</h5>
                    </td>
                </tr>
            </table>
        </div> --}}
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-responsive" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">No</th>
                    <th class="text-center align-middle">POSISI</th>
                    <th class="text-center align-middle">MEREK</th>
                    <th class="text-center align-middle">NO. SERI BAN</th>
                    <th class="text-center align-middle">JENIS BAN</th>
                    <th class="text-center align-middle">KONDISI BAN</th>
                    <th class="text-center align-middle">TGL GANTI BAN</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ban as $d)
                <tr>
                    <td class="text-center align-middle">{{$loop->iteration}}</td>
                    <td class="text-start align-middle">

                            {{$d->nama}}

                    </td>
                    <td class="text-center align-middle">{{$d->banLog ? $d->banLog['merk'] : ''}}</td>
                    <td class="text-center align-middle">{{$d->banLog ? $d->banLog['no_seri'] : ''}}</td>
                    <td class="text-center align-middle">{{$d->jenis}}</td>
                    <td class="text-center align-middle">{{$d->banLog ? $d->banLog['kondisi']."%" : ''}}</td>
                    <td class="text-center align-middle">
                        @if ($d->banLog)
                           <a href="{{route('vendor-operational.per-vendor.ban-luar.histori', ['vehicle' => $vehicle->id, 'posisi' => $d->id])}}">
                            {{$d->banLog['tanggal_ganti']}}
                           </a>
                        @endif

                    </td>
                </tr>
                @endforeach
            </tbody>
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
    function tambah(data) {
        document.getElementById('posisi_ban_id').value = data.id;
        document.getElementById('tambahTitle').innerHTML = "Ban "+data.nama;
    }

    $(document).ready(function(){

            $('#rekapTable').DataTable({
                "searching": true,
                "responsive": true,
                "paging": false,
                "ordering": false,
                "scrollCollapse": true,
                "scrollY": "550px",
            });

            $('#createForm').submit(function(e){
                e.preventDefault();
                Swal.fire({
                    title: 'Apakah data sudah benar?',
                    text: "Pastikan data sudah benar sebelum disimpan!",
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
