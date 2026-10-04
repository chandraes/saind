@extends('layouts.app')
@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h1 class="h3 fw-bold mb-1">Statistik Filter & Oli Mesin</h1><p class="text-muted mb-0">Pantau kondisi dan ritase penggantian terakhir pada setiap kategori.</p></div>@if (in_array(auth()->user()->role, ['admin', 'su', 'user']))<a class="btn btn-outline-secondary" href="{{ route('statisik.index') }}"><i class="fa fa-arrow-left me-2"></i>Statistik</a>@endif</div>
    @include('billing.form-maintenance.filter-oli.feedback')
    <form method="get" class="card border-0 shadow-sm mb-4"><div class="card-body"><div class="row g-3 align-items-end"><div class="col-md-8"><label class="form-label" for="filterVehicle">Kendaraan</label><select id="filterVehicle" class="form-select maintenance-select" name="vehicle_id" required><option value="">Cari nomor lambung kendaraan</option>@foreach ($vehicles as $unit)<option value="{{ $unit->id }}" @selected($vehicle?->id === $unit->id)>{{ $unit->nomor_lambung }}</option>@endforeach</select></div><div class="col-md-4"><button class="btn btn-primary" type="submit">Tampilkan statistik</button></div></div></div></form>
    @if ($vehicle)
        <div class="row g-3 mb-4">
            @foreach (['Kendaraan' => $vehicle->nomor_lambung, 'Kategori tercatat' => $logs->count().' / '.$categories->count(), 'Mencapai limit ritase' => $dueCount] as $label => $value)<div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><span class="small text-muted">{{ $label }}</span><div class="fs-3 fw-bold {{ $label === 'Mencapai limit ritase' && $dueCount > 0 ? 'text-danger' : 'text-primary' }}">{{ $value }}</div></div></div></div>@endforeach
        </div>
        <div class="card border-0 shadow-sm"><div class="card-header bg-white p-3"><h2 class="h6 fw-bold mb-1">Filter & oli saat ini</h2><p class="small text-muted mb-0">Limit mengikuti acuan saat penggantian disetujui. Klik histori untuk melihat penggantian sebelumnya.</p></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-success"><tr><th>No</th><th>Kategori</th><th>Merek</th><th>Kondisi</th><th>Tanggal ganti</th><th>Ritase saat ini</th><th>Limit ritase</th><th>Status</th><th>Histori</th></tr></thead><tbody>
        @foreach ($categories as $category)
            @php $log = $logs->get($category->id); @endphp
            <tr><td>{{ $loop->iteration }}</td><td class="fw-semibold">{{ $category->nama }}</td><td>{{ $log?->merk ?? '—' }}</td><td>@if ($log)<span class="badge {{ $log->kondisi > 70 ? 'bg-success' : ($log->kondisi > 40 ? 'bg-warning text-dark' : 'bg-danger') }}">{{ $log->kondisi }}%</span>@else — @endif</td><td class="text-nowrap">{{ $log?->created_at->format('d-m-Y') ?? '—' }}</td><td>@if ($log)<button type="button" class="btn btn-link btn-sm fw-bold text-nowrap" data-bs-toggle="modal" data-bs-target="#filterRitaseModal" data-ritase-url="{{ route('statistik.filter-oli.transaksi-ritase', $log->id) }}" aria-label="Lihat transaksi ritase {{ $category->nama }}">{{ number_format((float) $log->ritase, 1, ',', '.') }} rit <i class="fa fa-list-ul ms-1" aria-hidden="true"></i></button>@else — @endif</td><td>{{ $log ? $log->limit_ritase.' rit' : '—' }}</td><td>@if ($log)<span class="badge {{ (float) $log->ritase >= $log->limit_ritase ? 'bg-danger' : 'bg-success' }}">{{ (float) $log->ritase >= $log->limit_ritase ? 'Mencapai limit' : 'Di bawah limit' }}</span>@else <span class="text-muted small">Belum tercatat</span>@endif</td><td><a class="btn btn-sm btn-outline-primary text-nowrap" href="{{ route('statistik.filter-oli.histori', [$vehicle->id, $category->id]) }}"><i class="fa fa-history me-1"></i>Histori</a></td></tr>
        @endforeach
        </tbody></table></div></div>
    @else
        <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5"><img src="{{ asset('images/filter-oli-mesin.svg') }}" width="64" height="64" alt="" class="mb-3"><p class="mb-0">Pilih kendaraan untuk melihat statistik filter & oli mesin.</p></div></div>
    @endif
</div>
@include('rekap.statistik.filter-oli.transactions-modal')
@endsection
