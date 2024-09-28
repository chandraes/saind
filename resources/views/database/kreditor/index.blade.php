@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>BIODATA KREDITOR</u></h1>
        </div>
    </div>
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table" id="data-table">
                <tr>
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('database')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen" width="30">
                            Database</a></td>
                    <td><a href="#" data-bs-toggle="modal" data-bs-target="#createInvestor"><img
                                src="{{asset('images/kreditor.svg')}}" width="30"> Tambah Biodata Kreditor</a>

                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
@include('swal')
@include('database.kreditor.create')
@include('database.kreditor.edit')
<div class="container mt-5 table-responsive">
    <table class="table table-bordered table-hover" id="data">
        <thead class="table-warning bg-gradient">
            <tr>
                <th class="text-center align-middle" style="width: 5%">NO</th>
                <th class="text-center align-middle">NAMA</th>
                <th class="text-center align-middle">PERSENTASE</th>
                <th class="text-center align-middle">INFO<br>REKENING</th>
                <th class="text-center align-middle">NPWP</th>
                <th class="text-center align-middle">PPH</th>
                <th class="text-center align-middle">ACT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-start align-middle">{{$d->nama}}</td>
                <td class="text-center align-middle">{{$d->persen}}%</td>
                <td class="text-start align-middle">
                    <ul>
                        <li>Nama Rek : {{$d->nama_rek}}</li>
                        <li>No Rek :{{$d->no_rek}}</li>
                        <li>Bank : {{$d->bank}}</li>
                    </ul>
                </td>
                <td class="text-center align-middle">{{$d->npwp}}</td>
                <td class="text-center align-middle">
                    @if ($d->apa_pph == 1)
                    {{-- checklist --}}
                    <i class="fa fa-check-circle h3 text-success"></i>
                    @endif
                </td>
                <td class="text-center align-middle">
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-primary m-2" data-bs-toggle="modal"
                            data-bs-target="#editInvestor" onclick="editInvestor({{$d}}, {{$d->id}})"><i
                                class="fa fa-edit"></i></button>
                        <form action="{{route('database.kreditor.destroy', $d)}}" method="post" id="deleteForm-{{$d->id}}">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger m-2"><i class="fa fa-trash"></i></button>
                        </form>
                    </div>

                </td>
            </tr>
            <script>
                $('#deleteForm-{{$d->id}}').submit(function(e){
                       e.preventDefault();
                       Swal.fire({
                           title: 'Apakah data yakin untuk menghapus data ini?',
                           icon: 'warning',
                           showCancelButton: true,
                           confirmButtonColor: '#3085d6',
                           cancelButtonColor: '#6c757d',
                           confirmButtonText: 'Ya, hapus!'
                           }).then((result) => {
                           if (result.isConfirmed) {
                            $('#spinner').show();
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



<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    function editInvestor(data, id) {
        document.getElementById('edit_nama').value = data.nama;
        document.getElementById('edit_persen').value = data.persen;
        document.getElementById('edit_npwp').value = data.npwp;
        document.getElementById('edit_no_rek').value = data.no_rek;
        document.getElementById('edit_nama_rek').value = data.nama_rek;
        document.getElementById('edit_bank').value = data.bank;
        document.getElementById('edit_apa_pph').value = data.apa_pph;
        // Populate other fields...
        document.getElementById('editForm').action = '/database/kreditor/update/' + id;
    }

    $('#data').DataTable({
        paging: false,
        scrollCollapse: true,
        scrollY: "550px",
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


    $('#editForm').submit(function(e){
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

</script>
@endpush
