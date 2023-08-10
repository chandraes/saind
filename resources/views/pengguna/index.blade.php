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
    @if (session('success'))
    <div class="row">
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <strong>
                {{session('success')}}
            </strong>
        </div>
    </div>
    @endif

    <div class="modal fade" id="modalId" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
        aria-labelledby="modalTitleId" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-l" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleId">Tambah Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('pengguna.store')}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nama</label>
                                <input type="text" class="form-control" name="name" id="name" aria-describedby="name"
                                    placeholder="">
                            </div>
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" id="username" required
                                    aria-describedby="username" placeholder="">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" name="role" id="role">
                                    <option selected>Pilih Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="vendor">Vendor</option>
                                    <option value="user">User</option>
                                </select>
                            </div>
                            <div class="col-md-12 mt-2">
                                <label for="email" class="form-label">E-Mail</label>
                                <input type="email" class="form-control" name="email" id="email"
                                    aria-describedby="email" placeholder="boleh kosong">
                                <div class="form-group mt-2">
                                    <label for="passwordInput">Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password" class="form-control" id="passwordInput" required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="row justify-content-center mt-3">
        <table class="table table-bordered table-hover table-striped" id="user">
            <thead class="table-success">
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Username</th>
                    <th class="text-center">Nama</th>
                    <th class="text-center">Role</th>
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
                    <td class="text-center align-middle">
                        {{-- <a href="{{route('pengguna.edit', $d->id)}}" class="btn btn-warning btn-sm">Edit</a> --}}
                        <!-- Modal trigger button -->
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-edit-{{$d->id}}">
                          Edit
                        </button>

                        <!-- Modal Body -->
                        <!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
                        <div class="modal fade" id="modal-edit-{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered " role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalTitleId">Ubah Pengguna</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                        <form action="{{route('pengguna.update', $d->id)}}" method="post">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label for="name" class="form-label">Nama</label>
                                                        <input type="text" class="form-control" name="name" id="name" aria-describedby="name"
                                                            placeholder="" value="{{$d->name}}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="username" class="form-label">Username</label>
                                                        <input type="text" class="form-control" name="username" id="username" required
                                                            aria-describedby="username" placeholder="" value="{{$d->username}}">
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-md-12">
                                                        <label for="role" class="form-label">Role</label>
                                                        <select class="form-select" name="role" id="role">
                                                            <option value="admin" {{$d->role == 'admin' ? 'selected' : ''}}>Admin</option>
                                                            <option value="vendor" {{$d->role == 'vendor' ? 'selected' : ''}}>Vendor</option>
                                                            <option value="user" {{$d->role == 'user' ? 'selected' : ''}}>User</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-12 mt-2">
                                                        <label for="email" class="form-label">E-Mail</label>
                                                        <input type="email" class="form-control" name="email" id="email"
                                                            aria-describedby="email" placeholder="boleh kosong">
                                                        <div class="form-group mt-2">
                                                            <label for="passwordInput">Password</label>
                                                            <div class="input-group">
                                                                <input type="password" name="password" class="form-control" id="passwordInput" placeholder="Kosongkan Jika Tidak Mengganti">
                                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                                    <i class="fa fa-eye"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save</button>
                                            </div>
                                        </form>
                                </div>
                            </div>
                        </div>

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
<link href="https://cdn.datatables.net/v/bs5/dt-1.13.5/datatables.min.css" rel="stylesheet">
@endpush
@push('js')
<script src="https://cdn.datatables.net/v/bs5/dt-1.13.5/datatables.min.js"></script>
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
