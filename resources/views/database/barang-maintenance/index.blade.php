@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Kategori Barang Maintenance</u></h1>
        </div>
    </div>
    @include('swal')
    @include('database.barang-maintenance.create')
    @include('database.barang-maintenance.create-kategori')
    @include('database.barang-maintenance.edit')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('database')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen"
                                width="30"> Database</a></td>
                    <td>
                        <td><a href="#" data-bs-toggle="modal" data-bs-target="#create-category"><img src="{{asset('images/kategori.svg')}}" alt="dokumen"
                            width="30"> Tambah Kategori</a></td>

                    </td>
                    <td>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#createModal"">
                            <img src=" {{asset('images/barang-maintenance.svg')}}" alt="dokumen" width="30"> Tambah
                            Barang Maintenance</a>
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
                <th class="text-center align-middle">Stok</th>
                <th class="text-center align-middle">Harga Jual</th>
                <th class="text-center align-middle">ACT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-center align-middle" style="width: 10px">{{$loop->iteration}}</td>
                <td class="text-start align-middle">
                    {{$d->kategori->nama}}
                </td>
                <td class="text-start align-middle">
                    {{$d->nama}}
                </td>
                <td class="text-center align-middle">
                    {{$d->stok}}
                </td>
                <td class="text-end align-middle">
                    {{$d->nf_harga_jual}}
                </td>
                <td class="text-center align-middle">
                    <div class="d-flex justify-content-center">
                        <a href="#" class="btn btn-warning mx-2" data-bs-toggle="modal" data-bs-target="#editModal" title="Edit Data" onclick="editFun({{$d}}, {{$d->id}})"><i class="fa fa-edit"></i></a>
                    <form action="{{route('database.barang-maintenance.destroy', $d->id)}}" method="post" class="delete-form" data-id="{{$d->id}}" id="deleteForm{{$d->id}}">
                        @csrf
                        @method('delete')
                        <button type="submit" class="btn btn-danger mx-2" title="Hapus Data"><i class="fa fa-trash"></i></button>
                    </form>
                    </div>

                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/plugins/datatable/datatables.min.js')}}"></script>
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script>
   function editFun(data, id) {

            document.getElementById('edit_nama').value = data.nama;
            document.getElementById('edit_harga_jual').value = data.nf_harga_jual;
            document.getElementById('edit_kategori_barang_maintenance_id').value = data.kategori_barang_maintenance_id;
            // Populate other fields...
            document.getElementById('editForm').action = '/database/barang-maintenance/update/' + id;
        }

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

    $(document).ready(function() {
        $('#dataTable').DataTable({
            "paging": false,
            "scrollCollapse": true,
            "scrollY": "550px",
        });
        var nominal = new Cleave('#harga_jual', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });
            var harga_jual = new Cleave('#edit_harga_jual', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });
    } );

    $('#masukForm').submit(function(e){
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

        $('#editForm').submit(function(e){
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

        $('.delete-form').submit(function(e){
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
                    $(`#deleteForm${formId}`).unbind('submit').submit();
                    $('#spinner').show();
                }
            });
        });
</script>
@endpush
