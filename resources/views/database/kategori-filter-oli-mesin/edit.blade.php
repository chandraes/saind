@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center"><u>Edit Kategori Filter & Oli Mesin</u></h1>
    <form action="{{ route('database.kategori-filter-oli-mesin.update', $kategori) }}" method="post" class="card card-body mt-3">
        @csrf
        @method('patch')
        <div class="mb-3">
            <label for="nama" class="form-label">Nama</label>
            <input id="nama" name="nama" value="{{ old('nama', $kategori->nama) }}" class="form-control @error('nama') is-invalid @enderror" maxlength="255" required>
            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="limit_ritase" class="form-label">Limit Ritase (rit)</label>
            <input id="limit_ritase" name="limit_ritase" type="number" min="1" max="4294967295" step="1" value="{{ old('limit_ritase', $kategori->limit_ritase) }}" class="form-control @error('limit_ritase') is-invalid @enderror" required>
            @error('limit_ritase')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('database.kategori-filter-oli-mesin.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
