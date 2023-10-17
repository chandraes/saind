@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Kategori Barang</u></h1>
        </div>
    </div>
    @if (session('success'))
    <script>
        Swal.fire(
                'Berhasil!',
                '{{session('success')}}',
                'success'
            )
    </script>
    @endif
    @if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '{{session('error')}}',
        })
    </script>
    @endif
    @include('database.barang.create-kategori')
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
                                    <td><a href="#"><img src="{{asset('images/barang.svg')}}" alt="dokumen"
                                        width="30"> Tambah Barang</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container mt-5 table-responsive ">
    <table class="table table-hover table-bordered" id="karyawan-data">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">Kategori</th>
                <th class="text-center align-middle">Nama Barang</th>
                <th class="text-center align-middle">Stok</th>
                <th class="text-center align-middle">Harga Jual</th>
                <th class="text-center align-middle">Action</th>
            </tr>
        </thead>
        <tbody>
        @if ($barang->count() == 0)
            @foreach ($kategori as $k)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-center align-middle">{{$k->nama}}</td>
                <td class="text-center align-middle">-</td>
                <td class="text-center align-middle">-</td>
                <td class="text-center align-middle">-</td>
                <td class="text-center align-middle">
                    {{-- <a href="{{route('database.barang.create', $k->id)}}" class="btn btn-primary"><i class="fa fa-plus"></i></a> --}}
                </td>
            </tr>
            @endforeach
        @else
            @foreach ($barang as $b)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-center align-middle">{{$b->kategori->nama}}</td>
                <td class="text-center align-middle">{{$b->nama}}</td>
                <td class="text-center align-middle">{{$b->stok}}</td>
                <td class="text-center align-middle">{{$b->harga_jual}}</td>
                <td class="text-center align-middle">
                    {{-- <a href="{{route('database.barang.edit', $b->id)}}" class="btn btn-warning"><i class="fa fa-edit"></i></a> --}}
                    {{-- form delete with confirmation --}}
                    {{-- <form action="{{route('database.barang.destroy', $b)}}" method="post" class="d-inline">
                        @csrf
                        @method('delete')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah anda yakin untuk menghapus data ini?')"><i class="fa fa-trash"></i></button>
                    </form> --}}
                </td>
            </tr>
            @endforeach
        @endif
        </tbody>
    </table>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/plugins/date-picker/date-picker.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/datatables.min.js')}}"></script>
<script>

    // hide alert after 5 seconds
    setTimeout(function() {
        $('#alert').fadeOut('slow');
    }, 5000);

    $(document).ready(function() {
        $('#karyawan-data').DataTable();

    } );

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
