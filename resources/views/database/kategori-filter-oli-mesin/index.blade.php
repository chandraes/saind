@extends('layouts.app')

@section('content')
@php
    $canEdit = in_array(auth()->user()->role, ['su', 'admin'], true);
    $editingId = old('kategori_id');
@endphp
<div class="container py-4 filter-oil-page">
    <a href="{{ route('database') }}" class="text-decoration-none text-muted d-inline-block mb-3"><i class="fa fa-arrow-left me-2" aria-hidden="true"></i> Kembali ke Database</a>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <span class="category-icon"><i class="fa fa-tint" aria-hidden="true"></i></span>
            <div><h1 class="h3 fw-bold mb-1">Kategori Filter & Oli Mesin</h1><p class="text-muted mb-0">Kelola acuan ritase untuk perawatan filter dan oli mesin.</p></div>
        </div>
        @if (auth()->user()->role === 'su')
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createCategoryModal"><i class="fa fa-plus me-2" aria-hidden="true"></i>Tambah Kategori</button>
        @endif
    </div>
    @if ($errors->any() || session('error') || session('success'))
        <div hidden data-category-notification data-icon="{{ $errors->any() || session('error') ? 'error' : 'success' }}" data-message="{{ $errors->any() ? implode(' ', $errors->all()) : (session('error') ?: session('success')) }}"></div>
    @endif
    <div class="card category-card border-0">
        <div class="card-header bg-white border-0 p-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div><h2 class="h5 fw-bold mb-1">Daftar kategori</h2><span class="text-muted small">Limit dihitung dalam satuan ritase (rit).</span></div>
            <span class="badge rounded-pill category-count">{{ $kategori->count() }} kategori</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle category-table mb-0">
                <thead class="table-success"><tr><th scope="col" class="text-center">No</th><th scope="col">Nama</th><th scope="col">Limit Ritase</th><th scope="col" class="text-end">Action</th></tr></thead>
                <tbody>
                @forelse ($kategori as $item)
                    <tr>
                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $item->nama }}</td>
                        <td><span class="ritase-badge">{{ $item->limit_ritase }} rit</span></td>
                        <td class="text-end">
                            @if ($canEdit)
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $item->id }}" aria-label="Edit {{ $item->nama }}"><i class="fa fa-pencil me-1" aria-hidden="true"></i> Edit</button>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-5 text-muted"><i class="fa fa-folder-open-o fa-2x mb-3 d-block" aria-hidden="true"></i>Belum ada kategori filter & oli mesin.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@if (auth()->user()->role === 'su')
    @include('database.kategori-filter-oli-mesin.form-modal', ['modalId' => 'createCategoryModal', 'item' => null, 'isEditing' => false, 'showErrors' => $errors->any() && !$editingId])
@endif
@if ($canEdit)
    @foreach ($kategori as $item)
        @include('database.kategori-filter-oli-mesin.form-modal', ['modalId' => 'editCategoryModal'.$item->id, 'isEditing' => true, 'showErrors' => $errors->any() && (string) $editingId === (string) $item->id])
    @endforeach
@endif
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/kategori-filter-oli-mesin.css') }}">
@endpush
@push('js')
<script src="{{ asset('assets/js/kategori-filter-oli-mesin.js') }}" defer></script>
@endpush
