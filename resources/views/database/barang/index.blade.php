@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Kategori Barang Umum</u></h1>
        </div>
    </div>
    @include('swal')
    @include('database.barang.create-kategori')
    @include('database.barang.create-barang')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('database')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen"
                                width="30"> Database</a></td>
                    <td><a href="#" data-bs-toggle="modal" data-bs-target="#create-category"><img src="{{asset('images/stock.svg')}}" alt="dokumen"
                                    width="30"> Tambah Kategori</a></td>
                    <td>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#create-barang">
                            <img src="{{asset('images/barang.svg')}}" alt="dokumen" width="30"> Tambah Barang</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container mt-5 table-responsive ">
    <table class="table table-bordered" id="dataTable">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">Kategori</th>
                <th class="text-center align-middle">Barang</th>
                {{-- <th class="text-center align-middle">Barang</th> --}}
                {{-- <th class="text-center align-middle">Harga Jual</th>
                <th class="text-center align-middle">Action</th> --}}
            </tr>
        </thead>
        <tbody>
            @foreach ($kategori as $k)
            @if ($k->barang->count() > 0)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-center align-middle">{{$k->nama}}</td>
                <td>
                    <table class="table table-hover table-bordered">
                        <thead class="table-success">
                            <tr>
                                <th class="text-center align-middle">Barang</th>
                                <th class="text-center align-middle">Stok</th>
                                <th class="text-center align-middle">Harga Jual</th>
                                <th class="text-center align-middle">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($k->barang as $b)
                            <tr>
                                <td class="text-center align-middle" style="width: 40%">{{$b->nama}}</td>
                                <td class="text-center align-middle" style="width: 10%">{{$b->stok}}</td>
                                <td class="text-center align-middle" style="width: 30%">Rp. {{number_format($b->harga_jual, 0, ',', '.')}}</td>
                                <td class="text-center align-middle" style="width: 20%">
                                    <a href="#" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editBarang-{{$b->id}}">Edit</a>

                                    @include('database.barang.harga-jual')

                                    <form action="{{route('barang.destroy', $b->id)}}" method="post" class="d-inline" id="deleteBarang-{{$b->id}}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            <script>
                                 $(document).ready(function(){
                                    $('#harga_jual-{{$b->id}}').maskMoney({
                                        thousands: '.',
                                        decimal: ',',
                                        precision: 0
                                    });
                                    $('#deleteBarang-{{$b->id}}').submit(function(e){
                                        e.preventDefault();
                                        Swal.fire({
                                            title: 'Apakah anda yakin menghapus data ini?',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#3085d6',
                                            cancelButtonColor: '#6c757d',
                                            confirmButtonText: 'Ya, Hapus!'
                                            }).then((result) => {
                                            if (result.isConfirmed) {
                                                this.submit();
                                            }
                                        })
                                    });
                                });
                            </script>
                            @endforeach
                        </tbody>
                    </table>
                </td>
            @else
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-center align-middle">{{$k->nama}}</td>
                <td class="text-center align-middle">-</td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" href="extensions/sticky-header/bootstrap-table-sticky-header.css">
@endpush
@push('js')
<script src="{{asset('assets/plugins/date-picker/date-picker.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/datatables.min.js')}}"></script>
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>

<script src="extensions/sticky-header/bootstrap-table-sticky-header.js"></script>
<script>

    // hide alert after 5 seconds
    setTimeout(function() {
        $('#alert').fadeOut('slow');
    }, 5000);

    $(document).ready(function() {
        $('#dataTable').DataTable({
            "paging": false,
            "scrollCollapse": true,
            "scrollY": "550px",
        });

        $('#kategori_barang_id').select2({
            theme: 'bootstrap-5'
        });
    } );

    $('#masukBarangForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah Data yang anda masukan sudah benar?',
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

    function toggleNamaJabatan(id) {

        // check if input is readonly
        if ($('#nama_jabatan-'+id).attr('readonly')) {
            // remove readonly
            $('#nama_jabatan-'+id).removeAttr('readonly');
            // show button
            $('#buttonJabatan-'+id).removeAttr('hidden');
        } else {
            // add readonly
            $('#nama_jabatan-'+id).attr('readonly', true);
            // hide button
            $('#buttonJabatan-'+id).attr('hidden', true);
        }
    }

</script>
@endpush
