@extends('layouts.app')
@section('content')
@php
$password = \App\Models\PasswordKonfirmasi::first();
@endphp
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>DASHBOARD</u></h1>
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
    <div class="row justify-content-left mt-5">
        @if (auth()->user()->role === 'admin' || auth()->user()->role === 'su')
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('pengguna.index')}}" class="text-decoration-none">
                <img src="{{asset('images/worker.svg')}}" alt="" width="100">
                <h2>Pengguna</h2>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('pengaturan.wa')}}" class="text-decoration-none">
                <img src="{{asset('images/wa.svg')}}" alt="" width="100">
                <h2>Whatsapp</h2>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#passwordKonfirmasi">
                <img src="{{asset('images/password.svg')}}" alt="" width="100">
                <h2>Password Konfirmasi</h2>
            </a>
            <div class="modal fade" id="passwordKonfirmasi" tabindex="-1" data-bs-backdrop="static"
                data-bs-keyboard="false" role="dialog" aria-labelledby="pkTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="pkTitle">Password Konfirmasi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{route('password-konfirmasi.store')}}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Password" aria-label="Password" aria-describedby="password" value="{{$password ? $password->password : ''}}"
                                            required>
                                        <button class="btn btn-outline-secondary" type="button" id="button-addon2"
                                            onclick="togglePassword()">
                                            <i class="fa fa-eye" id="icon"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 text-center mb-5">
        <a href="{{route('pengaturan.nota-transaksi')}}" class="text-decoration-none">
            <img src="{{asset('images/konfigurasi.svg')}}" alt="" width="100">
            <h2>Konfigurasi Nota Transaksi</h2>
        </a>
    </div>
    @endif
    <div class="col-md-3 text-center">
        <a href="{{route('home')}}" class="text-decoration-none">
            <img src="{{asset('images/dashboard.svg')}}" alt="" width="100">
            <h2>Dashboard</h2>
        </a>
    </div>
</div>
</div>
@endsection
@push('js')
<script>
    function togglePassword() {
            var x = document.getElementById("password");
            var y = document.getElementById("icon");
            if (x.type === "password") {
                x.type = "text";
                y.className = "fa fa-eye-slash";
            } else {
                x.type = "password";
                y.className = "fa fa-eye";
            }
        }
</script>
@endpush
