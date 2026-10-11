@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3"><h1>Integrasi absensi</h1><a href="{{ route('karyawan.index') }}" class="btn btn-secondary">Kembali ke pegawai</a></div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <div class="alert {{ $ready && $configured ? 'alert-info' : 'alert-warning' }}">
        <strong>{{ $ready && $configured ? 'Penghubung aktif' : 'Penghubung belum siap' }}</strong>
        <p class="mb-0">{{ $synced }} pegawai tersinkron · {{ $pending }} perubahan menunggu. Data identitas dikelola di SAIND; akun mobile, jadwal, lokasi, dan cuti dikelola di aplikasi absensi.</p>
    </div>
    <form method="post" action="{{ route('attendance-integration.sync') }}" class="mb-3">@csrf<button class="btn btn-primary" @disabled(!$ready || !$configured)>Sinkronkan pegawai sekarang</button></form>
    <p>Perubahan pegawai dikirim setelah disimpan. Pengiriman yang gagal disimpan untuk dicoba ulang oleh scheduler. Pegawai yang dihapus dikirim sebagai nonaktif agar riwayat absensinya tetap tersimpan. Password dan data keuangan tidak disinkronkan.</p>
    <div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Pegawai</th><th>Status pegawai</th><th>Pengiriman</th><th>Terakhir berhasil</th><th>Keterangan</th></tr></thead><tbody>
        @forelse($syncs ?? [] as $sync)
        <tr><td>{{ $sync->payload['employee_number'] ?? $sync->source_employee_id }} · {{ $sync->payload['name'] ?? '—' }}</td><td>{{ ($sync->payload['status'] ?? '') === 'active' ? 'Aktif' : 'Nonaktif' }}</td><td>{{ $sync->source_version === $sync->delivered_version ? 'Tersinkron' : 'Menunggu' }}</td><td>{{ $sync->last_synced_at?->format('d/m/Y H:i') ?? '—' }}</td><td>{{ $sync->last_error ?? '—' }}</td></tr>
        @empty<tr><td colspan="5">Belum ada catatan sinkronisasi. Jalankan sinkronisasi awal.</td></tr>@endforelse
    </tbody></table></div>
    {{ $syncs?->links() }}
    <h2 class="mt-4">Baca hasil dari aplikasi absensi</h2>
    <form method="get" action="{{ route('attendance-integration.index') }}" class="row g-2 mb-3">
        <div class="col-md-3"><label>Jenis data<select name="resource" class="form-select"><option value="attendances" @selected($resource === 'attendances')>Absensi</option><option value="leaves" @selected($resource === 'leaves')>Cuti / izin</option><option value="leave-accounts" @selected($resource === 'leave-accounts')>Hak dan pemakaian cuti</option></select></label></div>
        <div class="col-md-2"><label>ID pegawai SAIND<input name="source_employee_id" class="form-control" value="{{ request('source_employee_id') }}" placeholder="Semua pegawai"></label></div>
        <div class="col-md-2"><label>Dari (absensi)<input type="date" name="from" class="form-control" value="{{ request('from') }}"></label></div>
        <div class="col-md-2"><label>Sampai (absensi)<input type="date" name="to" class="form-control" value="{{ request('to') }}"></label></div>
        <div class="col-md-2"><label>Halaman hasil<input type="number" name="result_page" min="1" max="100000" class="form-control" value="{{ request('result_page', 1) }}"></label></div>
        <div class="col-md-1 align-self-end"><button class="btn btn-primary">Baca</button></div>
    </form>
    @if($resultError)<div class="alert alert-warning">{{ $resultError }}</div>@endif
    @if($results)
        <p>{{ $results['total'] }} catatan · Halaman {{ $results['current_page'] }} dari {{ $results['last_page'] }}. Pengolahan gaji tetap mengikuti aturan SAIND.</p>
        <div class="table-responsive"><table class="table table-bordered"><thead><tr><th>ID pegawai SAIND</th><th>Nama</th><th>Tanggal / tahun</th><th>Status / pemakaian</th><th>Rincian</th></tr></thead><tbody>
        @forelse($results['data'] as $row)
            <tr><td>{{ $row['employee']['source_employee_id'] ?? '—' }}</td><td>{{ $row['employee']['name'] ?? '—' }}</td>
                <td>{{ $row['work_date'] ?? $row['start_date'] ?? $row['year'] ?? '—' }}</td>
                <td>{{ $row['status'] ?? ('Terpakai '.(($row['used_grant'] ?? 0) + ($row['used_carry'] ?? 0)).' hari') }}</td>
                <td>@if($resource === 'attendances')Masuk: {{ $row['check_in_at'] ?? '—' }} · Pulang: {{ $row['check_out_at'] ?? '—' }} · Terlambat {{ $row['late_minutes'] ?? 0 }} menit
                    @elseif($resource === 'leaves'){{ $row['type'] ?? '—' }} · {{ $row['days'] ?? 0 }} hari · sampai {{ $row['end_date'] ?? '—' }}
                    @elseHak {{ $row['granted_days'] ?? 0 }} hari · carry-over {{ $row['carried_days'] ?? 0 }} hari @endif</td></tr>
        @empty<tr><td colspan="5">Belum ada hasil untuk filter ini.</td></tr>@endforelse
        </tbody></table></div>
    @endif
</div>
@endsection
