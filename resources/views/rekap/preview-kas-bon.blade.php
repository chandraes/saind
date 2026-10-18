@extends('layouts.doc-nologo-1')
@section('content')
<h2 style="text-align:center">REKAP KASBON POTONG GAJI</h2>
<p style="text-align:center">{{ $periodLabel }} · Sesuai filter pegawai dan pelunasan</p>
<p>Status dan sisa utang mengikuti kondisi terkini.</p>
<table class="table table-bordered" style="width:100%;font-size:11px">
    <thead class="table-success"><tr><th>No</th><th>Tanggal</th><th>Nama Pegawai</th><th>Nominal Pinjaman</th><th>Total Dibayar</th><th>Sisa Kasbon</th><th>Pelunasan</th></tr></thead>
    <tbody>@forelse($data as $loan)<tr><td>{{ $loop->iteration }}</td><td>{{ $loan->tanggal }}</td><td>{{ $loan->karyawan?->nama ?? 'Pegawai tidak tersedia' }}</td><td style="text-align:right">{{ number_format($loan->nominal, 0, ',', '.') }}</td><td style="text-align:right">{{ number_format($loan->total_bayar, 0, ',', '.') }}</td><td style="text-align:right">{{ number_format($loan->sisa_kas, 0, ',', '.') }}</td><td>{{ $loan->lunas ? 'Lunas' : 'Belum lunas' }}</td></tr>@empty<tr><td colspan="7" style="text-align:center">Tidak ada pinjaman sesuai filter.</td></tr>@endforelse</tbody>
    <tfoot><tr><th colspan="3">Total</th><th style="text-align:right">{{ number_format($summary['nominal'], 0, ',', '.') }}</th><th style="text-align:right">{{ number_format($summary['paid'], 0, ',', '.') }}</th><th style="text-align:right">{{ number_format($summary['remaining'], 0, ',', '.') }}</th><th></th></tr></tfoot>
</table>
@endsection
