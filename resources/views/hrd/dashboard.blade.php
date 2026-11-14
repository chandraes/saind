@extends('layouts.app')

@section('content')
<div class="container">
    @include('swal')
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>DASHBOARD</u></h1>
        </div>
    </div>
    @include('per-vendor-operational.index', [
        'sections' => [[
            'key' => 'hrd',
            'title' => 'Kepegawaian',
            'menus' => [[
                'title' => 'Potongan Absensi',
                'icon' => 'karyawan.svg',
                'url' => route('hrd.attendance-deductions'),
                'description' => 'Potongan absensi pegawai per periode gaji beserta rinciannya.',
            ]],
        ]],
        'dashboardLabel' => 'HRD',
        'dashboardDescription' => auth()->user()->name.' · Pantau potongan absensi pegawai.',
    ])
</div>
@endsection
