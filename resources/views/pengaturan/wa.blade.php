@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <h2 class="fw-bold text-uppercase border-bottom pb-2 d-inline-block">Pengaturan WA</h2>
        </div>
    </div>

    @include('swal')

    <div class="d-flex justify-content-start gap-2 mb-4">
        <a href="{{ route('home') }}" class="btn btn-outline-primary">
            <i class="fa fa-tachometer me-1"></i> Dashboard
        </a>
        <a href="{{ route('pengaturan') }}" class="btn btn-outline-secondary">
            <i class="fa fa-cog me-1"></i> Pengaturan
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-success">
                        <tr>
                            <th class="text-center align-middle py-3" width="5%">No</th>
                            <th class="text-center align-middle py-3">Untuk</th>
                            <th class="text-center align-middle py-3">Nama Group</th>
                            <th class="text-center align-middle py-3" width="15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $d)
                            <tr>
                                <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                <td class="align-middle px-3">{{ $d->untuk }}</td>
                                <td class="align-middle px-3">{{ $d->group_id }}</td>
                                <td class="text-center align-middle">
                                    <a href="{{ route('pengaturan.wa.edit', $d->id) }}"
                                       class="btn btn-warning btn-sm shadow-sm"
                                       onclick="showSpinner({{ $d->id }})"
                                       id="editBtn{{ $d->id }}">
                                        <i class="fa fa-edit me-1"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="fa fa-folder-open fa-2x mb-2"></i><br>
                                    Belum ada data grup WhatsApp.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    // Fungsi untuk menampilkan spinner saat tombol ditekan
    function showSpinner(id) {
        if ($('#spinner').length) {
            $('#spinner').show();
        }

        // Disable tombol untuk mencegah double click
        let btn = $('#editBtn' + id);
        btn.prop('disabled', true);
        btn.html('<i class="fa fa-spinner fa-spin me-1"></i> Loading...');

        // Fallback jika tidak langsung berpindah halaman
        setTimeout(() => {
            window.location.href = btn.attr('href');
        }, 100);
    }
</script>
@endpush
