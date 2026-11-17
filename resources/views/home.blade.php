@extends('layouts.app')
@section('content')
<div class="container">
    @include('swal')
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>DASHBOARD</u></h1>
        </div>
    </div>
    <div class="row justify-content-left {{ auth()->user()->usesVendorMenus() ? 'd-none' : 'mt-5' }}">
        {{-- Dashboard Pengingat (admin, su, user); akun dengan menu vendor memakai seksi Pengingat di VendorDashboardMenu. --}}
        @if (! auth()->user()->usesVendorMenus() && ($reminders = \App\Support\VendorDashboardMenu::reminderSection(auth()->user())))
        @php($reminderCount = $reminders['menus'][0]['badge'])
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{ route('reminders.index') }}" class="text-decoration-none">
                <span class="position-relative d-inline-block">
                    <img src="{{ asset('images/pengingat.svg') }}" alt="" width="70">
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill {{ $reminderCount > 0 ? 'bg-danger' : 'bg-secondary' }}" data-reminder-badge>{{ $reminderCount }}<span class="visually-hidden"> pengingat</span></span>
                </span>
                <h4 class="mt-3">PENGINGAT</h4>
            </a>
        </div>
        @endif
        @if (auth()->user()->role === 'admin' || auth()->user()->role === 'su')
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('database')}}" class="text-decoration-none">
                <img src="{{asset('images/database.svg')}}" alt="" width="70">
                <h4 class="mt-3">DATABASE</h4>
            </a>
        </div>
        @endif
        @if (auth()->user()->role === 'su' || auth()->user()->role === 'admin' || auth()->user()->role === 'user')
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('billing.index')}}" class="text-decoration-none">
                <img src="{{asset('images/billing.svg')}}" alt="" width="70">
                <h4 class="mt-3">BILLING</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('rekap.index')}}" class="text-decoration-none">
                <img src="{{asset('images/rekap.svg')}}" alt="" width="70">
                <h4 class="mt-3">REKAP</h4>
            </a>
        </div>

        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h4 class="mt-3">PURCHASE<br>ORDER</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h4 class="mt-3">INVENTARIS</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('pajak.index')}}" class="text-decoration-none">
                <img src="{{asset('images/pajak.svg')}}" alt="" width="70">
                <h4 class="mt-3">PAJAK</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h4 class="mt-3">LAPORAN<br>KEUANGAN</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('statisik.index')}}" class="text-decoration-none">
                <img src="{{asset('images/statistik.svg')}}" alt="" width="70">
                <h4 class="mt-3">STATISTIK</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('legalitas')}}" class="text-decoration-none">
                <img src="{{asset('images/legalitas.svg')}}" alt="" width="70">
                <h4 class="mt-3">LEGALITAS</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h4 class="mt-3">STRUKTUR<br>ORGANISASI</h4>
            </a>
        </div>
        @endif
        @if (auth()->user()->role === 'admin' || auth()->user()->role === 'su')
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('dokumen')}}" class="text-decoration-none">
                <img src="{{asset('images/document.svg')}}" alt="" width="70">
                <h4 class="mt-3">DOKUMEN</h4>
            </a>
        </div>
        @if (auth()->user()->role === 'su')
        {{-- <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('bypass.index')}}" class="text-decoration-none">
                <img src="{{asset('images/admin.svg')}}" alt="" width="70">
                <h4 class="mt-3">BYPASS</h4>
            </a>
        </div> --}}
        @endif
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('company-profile')}}" class="text-decoration-none">
                <img src="{{asset('images/company-profile.svg')}}" alt="" width="70">
                <h4 class="mt-3">COMPANY PROFILE</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('pengaturan')}}" class="text-decoration-none">
                <img src="{{asset('images/pengaturan.svg')}}" alt="" width="70">
                <h4 class="mt-3">PENGATURAN</h4>
            </a>
        </div>
        @endif
        {{-- Pengaturan akun (semua role dashboard tile); dashboard kartu memakai seksi "Akun". --}}
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{ route(\App\Http\Controllers\AccountController::EDIT_ROUTE) }}" class="text-decoration-none">
                <img src="{{ asset('images/akun.svg') }}" alt="" width="70">
                <h4 class="mt-3">AKUN</h4>
            </a>
        </div>
    </div>
    @if (auth()->user()->usesVendorMenus())
    @include('per-vendor-operational.index')
    @endif
    @if (auth()->user()->role === 'customer')
    @include('per-customer.index')
    @endif
    @if (auth()->user()->role === 'customer-admin')
    @include('per-customer-admin.index')
    @endif
     @if (auth()->user()->role === 'investor')
    @include('per-investor.index')
    @endif
     @if (auth()->user()->role === 'asisten-user')
    @include('asisten-user.index')
    @endif
     @if (auth()->user()->role === 'su')
    @include('admin.index')
    @endif
</div>
@endsection
