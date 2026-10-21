<!doctype html>
<html lang="id"><head><meta charset="utf-8"><title>Rincian Potongan Absensi</title>
<style>
body{font-family:DejaVu Sans,sans-serif;font-size:9px;color:#24352f}h1{font-size:18px;margin-bottom:5px}p{margin:5px 0}.muted{color:#63756d}.summary{background:#edf6f0;padding:12px;margin:14px 0}.summary span{margin-right:20px}table{width:100%;border-collapse:collapse;margin-top:12px}th{background:#d1e7dd;text-align:left}th,td{padding:7px;border:1px solid #dce5df;vertical-align:top}thead{display:table-header-group}tr{page-break-inside:avoid}.right{text-align:right}.note{margin-top:14px;font-size:8px}
</style></head><body>
<h1>Rincian Potongan Absensi</h1>
<p><strong>{{ $name }}</strong> · Gaji {{ $period->copy()->locale('id')->translatedFormat('F Y') }}</p>
<p class="muted">{{ $saved ? 'Rekap gaji tersimpan' : 'Pratinjau — belum disahkan' }} · Periode absensi {{ $detail['from'] ?? '—' }} sampai {{ $detail['to'] ?? '—' }}</p>
@php
    $money = fn ($value) => 'Rp '.number_format($value ?? 0, 0, ',', '.');
    $zone = $detail['timezone'] ?? 'Asia/Jakarta';
    $stamp = fn ($value) => $value ? \Carbon\Carbon::parse($value)->timezone($zone)->format('d/m/Y H:i') : '—';
@endphp
@if($detail['provisional'] ?? false)<p>Potongan sementara. Jadwal mendatang, shift berjalan dan pengajuan menunggu HR belum dikenai potongan.</p>@endif
@if($detail['as_of'] ?? null)<p class="muted">Data ditarik: {{ $stamp($detail['as_of']) }} ({{ $zone }})</p>@endif
<div class="summary"><span>Nilai harian: <strong>{{ $money($detail['daily'] ?? 0) }}</strong></span><span>Sebelum batas: <strong>{{ $money($detail['raw']) }}</strong></span><span>Batas 25%: <strong>{{ $money($detail['cap'] ?? 0) }}</strong></span><span>Potongan akhir: <strong>{{ $money($detail['amount']) }}</strong></span></div>
<p class="muted">Tanggal kerja mengikuti tanggal mulai jadwal, termasuk shift malam yang pulang keesokan hari. Semua jam dalam zona {{ $zone }}.</p>
<table><thead><tr><th>Hari / tanggal</th><th>Jadwal & kehadiran</th><th>Keterangan</th><th>Perhitungan</th><th>Potongan harian</th></tr></thead><tbody>
@forelse($detail['days'] as $day)
@php
    $status = $day['status'];
    $scheduleLabel = match($day['schedule_mode'] ?? null) {
        'shift' => 'Shift'.(!empty($day['schedule_name']) ? ' · '.$day['schedule_name'] : ''),
        'weekly' => 'Jadwal mingguan',
        default => 'Jenis jadwal belum tersedia',
    };
    $pending = in_array($status, ['pending_leave','pending','upcoming','in_progress'], true);
    $effective = $day['effective_minutes'] ?? max(0, $day['scheduled_minutes'] - $day['break_minutes']);
    $lost = min($effective, ($day['late_minutes'] ?? 0) + ($day['early_minutes'] ?? 0));
    $label = match($status) {
        'absent' => 'Tidak masuk', 'half_day' => 'Tidak absen pulang · setengah hari',
        'pending_leave' => 'Menunggu HR', 'upcoming' => 'Jadwal mendatang', 'in_progress','pending' => 'Shift/batas pulang belum selesai',
        'holiday' => 'Hari libur', 'off' => 'Di luar jadwal', 'excused' => 'Cuti/izin disetujui', 'exempt' => 'Tidak wajib absen', 'before_tracking' => 'Sebelum masa pencatatan',
        'present' => 'Hadir', default => 'Belum dapat dihitung',
    };
@endphp
<tr><td>{{ \Carbon\Carbon::parse($day['date'])->locale('id')->translatedFormat('l, d/m/Y') }}</td><td><strong>{{ $scheduleLabel }}</strong><br>Jadwal: {{ $stamp($day['scheduled_start_at'] ?? null) }} – {{ $stamp($day['scheduled_end_at'] ?? null) }}<br>Masuk: {{ $stamp($day['check_in_at'] ?? null) }}<br>Pulang: {{ $stamp($day['check_out_at'] ?? null) }}</td><td>{{ $label }}
@if(($day['late_minutes'] ?? 0) > 0)<br>Telat setelah toleransi: {{ $day['late_minutes'] }} menit
@endif
@if(($day['early_minutes'] ?? 0) > 0)<br>Pulang cepat: {{ $day['early_minutes'] }} menit
@endif
</td><td>
@if($pending)Belum dihitung
@elseif($status === 'absent')1 × {{ $money($detail['daily']) }}
@elseif($status === 'half_day')½ × {{ $money($detail['daily']) }}<br>Tanpa tambahan terlambat
@elseif($status === 'present'){{ $lost }} / {{ $effective }} menit × {{ $money($detail['daily']) }}
@else Tidak dikenai potongan
@endif
@if(in_array($status, ['present','absent','half_day'], true))<br>Kerja efektif: {{ $day['scheduled_minutes'] }} − {{ $day['break_minutes'] }} = {{ $effective }} menit
@endif
</td><td class="right"><strong>{{ $money($day['deduction'] ?? 0) }}</strong>
@if($day['override'] ?? null)<br>Otomatis: {{ $money($day['automatic']) }}<br>Penyesuaian: {{ $day['override']['reason'] }}@endif
</td></tr>
@empty<tr><td colspan="5">Tidak ada rincian.</td></tr>@endforelse
</tbody></table>
<p class="note">Pembagi upah harian 25. Istirahat dihitung proporsional tanpa jam mulai. Nilai harian dan potongan per hari dibulatkan ke rupiah. Potongan harian di atas sebelum batas 25%; batas diterapkan pada total periode. Jam dalam zona {{ $zone }}. {{ $saved ? 'Dokumen menggunakan rincian yang dibekukan saat gaji disahkan.' : 'Data dapat berubah sampai gaji disahkan.' }}</p>
</body></html>
