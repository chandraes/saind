@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>COMPANY PROFILE</u></h1>
        </div>
    </div>
    @include('swal')
    @include('company-profile.create')
    @include('company-profile.kirim-wa')
    <div class="row d-flex justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>

                    <td><a href="#" data-bs-toggle="modal" data-bs-target="#modalCreate"><img
                                src="{{asset('images/company-profile.svg')}}" alt="add-document" width="30"> Tambah
                            Company Profile</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>


<div class="container mt-5 table-responsive ">
    <table class="table table-hover table-bordered" id="karyawan-data">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle" style="width: 7%">No</th>
                <th class="text-center align-middle">Nama Dokumen</th>
                {{-- <th class="text-center align-middle">Tgl Kadaluarsa</th> --}}
                <th class="text-center align-middle" style="width: 40%">Action</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($data as $d)

                <tr>
                    <td class="text-center align-middle"><b>{{ $loop->iteration }}</b></td>
                    <td class="text-start align-middle">{{ $d->nama }}</td>
                    {{-- <td class="text-center align-middle">{{ $d->id_tanggal_expired }}</td> --}}
                    <td class="text-center align-middle">
                        <div class="d-flex justify-content-center flex-wrap gap-3">
                            <a class="btn btn-primary btn-sm" href="{{ asset($d->file) }}" target="_blank">View <i class="ms-2 fa fa-file"></i></a>
                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#kirimWaModal" onclick="kirimWa({{ $d }})">Kirim Whatsapp <i class="ms-2 fa fa-whatsapp"></i></button>
                            {{-- <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit" onclick="editFun({{ $d }})">Edit <i class="ms-2 fa fa-edit"></i></button> --}}
                            <form action="{{ route('company-profile.destroy', $d) }}" method="post" class="d-inline">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah anda yakin untuk menghapus data ini?')"> Hapus <i class="ms-2 fa fa-trash"></i></button>
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
<link href="{{asset('assets/js/flatpickr/flatpickr.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/js/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/plugins/date-picker/date-picker.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/datatables.min.js')}}"></script>
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script>
    function kirimWa(data) {
        document.getElementById('nama_dokumen_wa').value = data.nama;
       document.getElementById('waForm').action = '/company-profile/kirim-wa/'+data.id;
    }

    var tujuan = new Cleave('#tujuan', {
        delimiter: '-',
        blocks: [4, 4, 8]
    });

    $('#waForm').submit(function(e){
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

    $(document).ready(function() {
        $('#karyawan-data').DataTable();

    } );

</script>
@endpush
