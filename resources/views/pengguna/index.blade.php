@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Pengguna Aplikasi</u></h1>
        </div>

    </div>
    <div class="flex-row justify-content-between">
        <div class="col-md-3">
            <table class="table">
                <tr></tr>
                <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30">
                        Dashboard</a></td>
                <td><a type="button" data-bs-toggle="modal" class="text-primary" data-bs-target="#modalId">
                        <img src="{{asset('images/user-add.svg')}}" alt="dashboard" width="30"> Tambah Pengguna
                    </a></td>
                </tr>
            </table>
        </div>
    </div>
    {{-- if has message --}}
    @include('swal')
    @include('pengguna.create')

    <div class="row justify-content-center mt-3">
        <table class="table table-bordered table-hover table-striped table-responsive" id="user">
            <thead class="table-success">
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Username</th>
                    <th class="text-center">Nama</th>
                    <th class="text-center">Role</th>
                    <th class="text-center">Vendor</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$loop->iteration}}</td>
                    <td class="text-center align-middle">{{$d->username}}</td>
                    <td class="text-center align-middle">{{$d->name}}</td>
                    <td class="text-center align-middle">{{$d->role}}</td>
                    <td class="text-center align-middle">{{$d->vendor ? $d->vendor : ''}}</td>
                    <td class="text-center align-middle">
                        {{-- <a href="{{route('pengguna.edit', $d->id)}}" class="btn btn-warning btn-sm">Edit</a> --}}
                        <!-- Modal trigger button -->
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-edit-{{$d->id}}">
                          Edit
                        </button>
                        @include('pengguna.edit')

                        <form action="{{route('pengguna.destroy', $d->id)}}" method="post" class="d-inline">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Yakin ingin menghapus data?')">Delete</button>
                        </form>
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
{{-- import sweet alert --}}
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#user').DataTable();
    } );
</script>
<script>
    const passwordInput = document.getElementById("passwordInput");
    const togglePasswordButton = document.getElementById("togglePassword");

    togglePasswordButton.addEventListener("click", function () {
      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        togglePasswordButton.innerHTML = '<i class="fa fa-eye-slash"></i>';
      } else {
        passwordInput.type = "password";
        togglePasswordButton.innerHTML = '<i class="fa fa-eye"></i>';
      }
    });
</script>
@endpush
