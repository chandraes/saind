@extends('layouts.app')
@section('content')
@php $canManage = in_array(auth()->user()->role, ['su', 'admin']); @endphp
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h1 class="h3 fw-bold mb-1">Histori {{ $category->nama }}</h1><p class="text-muted mb-0">Kendaraan {{ $vehicle->nomor_lambung }} · Riwayat penggantian yang disetujui.</p></div><a class="btn btn-outline-secondary" href="{{ route('statistik.filter-oli', ['vehicle_id' => $vehicle->id]) }}"><i class="fa fa-arrow-left me-2"></i>Kembali</a></div>
    @include('billing.form-maintenance.filter-oli.feedback')
    @if ($canManage)<div class="card border-0 bg-light mb-4"><div class="card-body small text-muted"><i class="fa fa-info-circle me-1"></i> Perubahan tanggal ganti dan penghapusan histori akan menghitung ulang ritase serta transaksi pada periode penggantian terkait. Invoice dan catatan pembayaran tetap tersimpan.</div></div>@endif
    <div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-success"><tr><th>No</th><th>Tanggal ganti</th><th>Merek</th><th>Kondisi</th><th>Ritase</th><th>Limit saat penggantian</th>@if ($canManage)<th>Action</th>@endif</tr></thead><tbody>
        @forelse ($logs as $log)
            <tr><td>{{ $logs->firstItem() + $loop->index }}</td><td class="text-nowrap">{{ $log->created_at->format('d-m-Y') }}</td><td>{{ $log->merk }}</td><td>{{ $log->kondisi }}%</td><td><button class="btn btn-link btn-sm fw-semibold text-nowrap" type="button" data-bs-toggle="modal" data-bs-target="#filterRitaseModal" data-ritase-url="{{ route('statistik.filter-oli.transaksi-ritase', $log->id) }}">{{ number_format((float) $log->ritase, 1, ',', '.') }} rit <i class="fa fa-list-ul ms-1" aria-hidden="true"></i></button></td><td>{{ $log->limit_ritase }} rit</td>
            @if ($canManage)<td><div class="d-flex gap-2"><button class="btn btn-sm btn-outline-primary text-nowrap" type="button" data-bs-toggle="modal" data-bs-target="#editHistory{{ $log->id }}"><i class="fa fa-edit me-1"></i>Edit tanggal</button><form method="post" action="{{ route('statistik.filter-oli.histori.destroy', $log->id) }}" data-maintenance-form data-confirm="Hapus histori {{ $category->nama }} tanggal {{ $log->created_at->format('d-m-Y') }}? Ritase akan dihitung ulang. Invoice dan pembayaran tetap tersimpan.">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa fa-trash me-1"></i>Hapus</button></form></div></td>@endif</tr>
        @empty
            <tr><td colspan="{{ $canManage ? 7 : 6 }}" class="text-center text-muted py-5">Belum ada histori penggantian untuk kategori ini.</td></tr>
        @endforelse
    </tbody></table></div><div class="card-body">{{ $logs->links('pagination::bootstrap-5') }}</div></div>
</div>
@if ($canManage)
    @foreach ($logs as $log)
        <div class="modal fade" id="editHistory{{ $log->id }}" tabindex="-1" data-bs-focus="false" aria-labelledby="editHistoryTitle{{ $log->id }}" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header"><h2 class="modal-title fs-5 fw-bold" id="editHistoryTitle{{ $log->id }}">Edit Tanggal Ganti</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <form method="post" action="{{ route('statistik.filter-oli.histori.update', $log->id) }}" novalidate data-maintenance-form data-confirm="Simpan tanggal ganti yang baru? Ritase dan daftar transaksi seluruh periode terkait akan dihitung ulang.">
                @csrf @method('PATCH')
                <div class="modal-body"><p class="text-muted small">{{ $category->nama }} · Kendaraan {{ $vehicle->nomor_lambung }} · {{ $log->merk }}</p><label class="form-label fw-semibold" for="historyDate{{ $log->id }}">Tanggal ganti</label><input class="form-control" id="historyDate{{ $log->id }}" name="tanggal_ganti" type="date" value="{{ $log->created_at->format('Y-m-d') }}" max="{{ today()->toDateString() }}" required><p class="small text-muted mt-2 mb-0">Transaksi akan dialokasikan ulang berdasarkan tanggal ganti yang baru.</p></div>
                <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary" type="submit">Simpan perubahan</button></div>
            </form>
        </div></div></div>
    @endforeach
@endif
@include('rekap.statistik.filter-oli.transactions-modal')
@endsection
