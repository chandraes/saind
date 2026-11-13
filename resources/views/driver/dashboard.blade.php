@extends('layouts.app')

@section('content')
@php
    $groups = [
        'documents' => ['Nota & Kas UJ Ditahan', [
            ['Nota Muat Masuk', 'nota-muat-masuk.svg', 'muat'],
            ['Nota Bongkar', 'bongkar.svg', 'bongkar'],
            ['Kas UJ Ditahan', 'uj-ditahan.svg', 'held-cash'],
        ]],
        'statistics' => ['Performa & Upah Gendong', [
            ['Perform Unit', 'perform-unit.svg', 'perform-unit'],
            ['Upah Gendong', 'statistik-ug.svg', 'upah-gendong'],
        ]],
        'maintenance' => ['Maintenance', [
            ['Ban Luar', 'db-ban.svg', 'ban-luar'],
            ['Aki', 'aki.svg', 'aki'],
            ['Filter & Oli Mesin', 'filter-oli-mesin.svg', 'filter-oli'],
        ]],
    ];
    $sections = [];
    foreach ($groups as $key => [$title, $entries]) {
        $menus = [];
        foreach ($entries as [$label, $icon, $kind]) {
            $menus[] = [
                'title' => $label,
                'icon' => $icon,
                'url' => match ($kind) {
                    'muat', 'bongkar' => route('driver.notes', $kind),
                    'held-cash' => '#ujDitahanMenu',
                    default => route('statistik.'.$kind),
                },
                'modal' => $kind === 'held-cash' ? 'ujDitahanMenu' : null,
                'description' => match ($kind) {
                    'muat' => 'Pantau pengajuan dan hasil pemeriksaan nota.',
                    'bongkar' => 'Lihat perjalanan dan progres pembongkaran.',
                    'held-cash' => 'Lihat saldo dan mutasi dana sparepart atau ban.',
                    'perform-unit' => 'Performa dan aktivitas nolam Anda per bulan.',
                    'upah-gendong' => 'Riwayat upah gendong nolam Anda.',
                    'ban-luar' => 'Pemakaian dan histori ban per posisi.',
                    'aki' => 'Kondisi dan histori aki nolam Anda.',
                    'filter-oli' => 'Pemakaian dan histori penggantian.',
                    default => '',
                },
            ];
        }
        $sections[] = ['key' => $key, 'title' => $title, 'menus' => $menus];
    }
@endphp
<div class="container">
    @include('swal')
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>DASHBOARD</u></h1>
        </div>
    </div>
    @include('per-vendor-operational.index', [
        'sections' => $sections,
        'dashboardLabel' => 'Dashboard Driver',
        'dashboardDescription' => auth()->user()->name.' · Nolam '.$vehicle->nomor_lambung,
    ])
</div>
@include('layouts.uj-ditahan-menu', ['menuArea' => 'driver'])
@endsection
