@extends('layouts.app')
@section('content')
@php($user = auth()->user())
<div class="container py-4 account-page">
    <header class="account-hero">
        <div class="account-hero-main">
            <span class="account-hero-icon"><img src="{{ asset('images/akun.svg') }}" width="40" height="40" alt=""></span>
            <div>
                <p class="account-eyebrow">PENGATURAN AKUN</p>
                <h1>{{ $user->name }}</h1>
                <p>Ganti username atau password yang Anda pakai untuk masuk ke web dan aplikasi mobile.</p>
            </div>
        </div>
        <a class="btn btn-outline-light" href="{{ route('home') }}"><i class="fa fa-arrow-left me-2" aria-hidden="true"></i>Dashboard</a>
    </header>

    @if (session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2" role="status" data-account-success>
        <i class="fa fa-check-circle" aria-hidden="true"></i><span>{{ session('success') }}</span>
    </div>
    @endif

    <form method="POST" action="{{ route(\App\Http\Controllers\AccountController::UPDATE_ROUTE) }}" novalidate data-account-form>
        @csrf
        @method('PUT')
        <section class="account-card" aria-labelledby="account-username-title">
            <div class="account-card-head">
                <i class="fa fa-user" aria-hidden="true"></i>
                <div>
                    <h2 id="account-username-title">Username</h2>
                    <p>Dipakai untuk login di web dan aplikasi mobile.</p>
                </div>
            </div>
            <div class="account-card-body">
                <div class="account-meta">
                    <span>Nama: <strong>{{ $user->name }}</strong></span>
                    <span>Role: <strong>{{ $user->role }}</strong></span>
                </div>
                <label for="username" class="form-label">Username</label>
                <input id="username" name="username" type="text" class="form-control @error('username') is-invalid @enderror"
                    value="{{ old('username', $user->username) }}" autocomplete="username" autocapitalize="off" spellcheck="false" required maxlength="255">
                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </section>

        <section class="account-card" aria-labelledby="account-security-title">
            <div class="account-card-head">
                <i class="fa fa-lock" aria-hidden="true"></i>
                <div>
                    <h2 id="account-security-title">Keamanan</h2>
                    <p>Password saat ini wajib diisi untuk menyimpan perubahan apa pun.</p>
                </div>
            </div>
            <div class="account-card-body">
                @foreach ([
                    ['current_password', 'Password saat ini', 'current-password', null],
                    ['password', 'Password baru (opsional)', 'new-password', 'Kosongkan jika hanya ingin mengubah username. Minimal 8 karakter.'],
                    ['password_confirmation', 'Konfirmasi password baru', 'new-password', null],
                ] as [$field, $label, $autocomplete, $hint])
                <div class="{{ $loop->last ? '' : 'mb-3' }}">
                    <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                    <div class="input-group has-validation">
                        <input id="{{ $field }}" name="{{ $field }}" type="password" class="form-control @error($field) is-invalid @enderror"
                            autocomplete="{{ $autocomplete }}" maxlength="{{ $field === 'current_password' ? 1024 : 72 }}" @if ($field === 'current_password') required @endif>
                        <button type="button" class="btn btn-outline-secondary btn-toggle-secret" data-toggle-secret="{{ $field }}" aria-label="Tampilkan {{ strtolower($label) }}" aria-pressed="false">
                            <i class="fa fa-eye" aria-hidden="true"></i>
                        </button>
                        @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    @if ($hint)<div class="form-text">{{ $hint }}</div>@endif
                </div>
                @endforeach
                <div class="form-text mt-3"><i class="fa fa-info-circle me-1" aria-hidden="true"></i>Setelah password diganti, akun ini dikeluarkan dari browser lain dan dari aplikasi mobile; browser ini tetap masuk.</div>
            </div>
        </section>

        <div class="account-actions">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-success" data-account-submit><i class="fa fa-check me-2" aria-hidden="true"></i>Simpan</button>
        </div>
    </form>
</div>
@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/account.css') }}?v={{ filemtime(public_path('assets/css/account.css')) }}">
@endpush
@push('js')
<script>
    document.querySelectorAll('[data-toggle-secret]').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.getElementById(button.dataset.toggleSecret);
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-pressed', show ? 'true' : 'false');
            button.querySelector('i').className = show ? 'fa fa-eye-slash' : 'fa fa-eye';
        });
    });
    document.querySelector('[data-account-form]').addEventListener('submit', function () {
        var submit = this.querySelector('[data-account-submit]');
        submit.disabled = true;
        submit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Menyimpan…';
    });
</script>
@endpush
@endsection
