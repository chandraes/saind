<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-3 p-md-4">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
        <div><h5 class="fw-bold mb-1">Potongan Absensi <span class="badge {{ $attendanceEnabled ? 'bg-success' : 'bg-secondary' }}">{{ $attendanceEnabled ? 'Aktif' : 'Nonaktif' }}</span></h5>
        <p class="small text-muted mb-1">Periode {{ \Carbon\Carbon::parse($attendanceDates['from'])->format('d/m/Y') }}–{{ \Carbon\Carbon::parse($attendanceDates['to'])->format('d/m/Y') }} · pembagi 25 · batas 25% · nilai rupiah bulat.</p>
        <p class="small text-muted mb-1">Istirahat dihitung proporsional tanpa jam mulai. Data disimpan di SAIND; membuka rincian tidak menghubungi server absensi.</p>
        <p class="small mb-0">{{ $attendancePeriod?->fetched_at ? 'Terakhir berhasil: '.\Carbon\Carbon::parse($attendancePeriod->fetched_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB' : 'Data absensi belum berhasil ditarik.' }} @if(($attendancePeriod?->remote_locked_at || $attendancePeriod?->lock_attempted_at)) · <strong>Periode terkunci. Lanjutkan penyimpanan transaksi yang sama.</strong> @endif</p></div>
        @if($attendanceEnabled && !($attendancePeriod?->remote_locked_at || $attendancePeriod?->lock_attempted_at))
        <form method="post" action="{{ route('billing.gaji.attendance.refresh') }}" data-payroll-operation>@csrf<input type="hidden" name="bulan" value="{{ $bulan }}"><input type="hidden" name="tahun" value="{{ $tahun }}"><button class="btn btn-outline-success"><i class="fa fa-refresh me-1"></i>{{ $attendancePeriod ? 'Perbarui' : 'Tarik' }} Data Absensi</button></form>
        @endif
    </div>
    @if($attendanceEnabled)
    <div class="rounded-3 bg-light p-3 mt-3 small" role="status">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-1"><i class="fa fa-refresh text-success" aria-hidden="true"></i><strong>Pembaruan snapshot {{ config('attendance.payroll_auto_sync') ? 'otomatis aktif' : 'manual' }}</strong>@if(config('attendance.payroll_auto_sync'))<span class="badge bg-success-subtle text-success-emphasis">Setiap 15 menit</span>@endif</div>
        <div class="text-muted">{{ config('attendance.payroll_auto_sync') ? 'Sistem menarik data periode gaji terbuka secara berkala. Tombol perbarui tetap dapat digunakan kapan saja.' : 'Gunakan tombol perbarui untuk mengambil koreksi absensi terbaru.' }} Pembaruan tidak memproses pembayaran gaji.</div>
        @if($attendancePeriod?->sync_last_error)
        <div class="text-danger mt-2"><strong>Pembaruan terakhir gagal.</strong> {{ $attendancePeriod->sync_last_error }} Data terakhir yang berhasil tetap dipertahankan.</div>
        @endif
        @if($attendancePeriod?->sync_attempted_at)
        <div class="text-muted mt-1">Percobaan terakhir: {{ \Carbon\Carbon::parse($attendancePeriod->sync_attempted_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB · {{ $attendancePeriod->sync_source === 'automatic' ? 'Otomatis' : 'Manual' }}
            @if($attendancePeriod->sync_duration_ms !== null)
                · {{ number_format($attendancePeriod->sync_duration_ms / 1000, 2, ',', '.') }} detik
            @endif
        </div>
        @endif
        @if(config('attendance.payroll_auto_sync') && $attendancePeriod?->next_sync_at && !$attendancePeriod->sealed_at && !$attendancePeriod->remote_locked_at && !$attendancePeriod->lock_attempted_at)
        <div class="text-muted mt-1">Jadwal berikutnya: {{ \Carbon\Carbon::parse($attendancePeriod->next_sync_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB. Jika gagal, sistem mencoba lagi bertahap setelah 5, 10, lalu maksimal 15 menit.</div>
        @endif
    </div>
    @endif
    @if($attendanceEnabled && $attendanceSnapshot)
    <div class="row g-2 mt-3">
        <div class="col-md-7"><div class="p-3 rounded-3 bg-light"><strong>{{ ($attendanceSnapshot['ready'] ?? false) ? 'Potongan periode siap ditinjau' : 'Pantauan potongan sementara' }}</strong><div class="small text-muted mt-1">Data sampai {{ isset($attendanceSnapshot['as_of']) ? \Carbon\Carbon::parse($attendanceSnapshot['as_of'])->timezone($attendanceSnapshot['timezone'] ?? config('app.timezone'))->format('d/m/Y H:i') : 'waktu penarikan terakhir' }}. Perbarui data untuk membaca koreksi dan shift terbaru.</div><div class="small text-muted">Tanggal mendatang dan shift yang belum selesai tidak dihitung sebagai tidak masuk. Cuti/sakit yang menunggu HR belum dikenai potongan.</div></div></div>
        <div class="col-md-5"><div class="p-3 rounded-3 bg-light"><strong>{{ $deductedCount }} pegawai dengan potongan · Rp {{ number_format($gt['potongan_absensi'],0,',','.') }}</strong><div class="small text-muted mt-1">{{ $attendanceSnapshot['unfinished_shifts'] ?? 0 }} shift belum dihitung · {{ $attendanceSnapshot['waiting_hr_days'] ?? 0 }} hari menunggu keputusan HR.</div></div></div>
    </div>
    @endif
    @if($attendanceEnabled && !($attendanceSnapshot['ready'] ?? false))
    <div class="alert alert-warning mt-3 mb-0"><strong>Pengesahan gaji belum tersedia; pantauan tetap dapat dilihat.</strong>
        @if(!$attendanceSnapshot) Tarik data absensi terlebih dahulu. @else
        <ul class="mb-0 mt-2">@foreach($attendanceSnapshot['issues'] as $issue)<li>{{ $issue }}</li>@endforeach</ul>
        @endif
    </div>
    @endif
    @if(auth()->user()->role === 'su')
    <details class="mt-3"><summary class="fw-semibold">Pengaturan & penyesuaian Super User</summary>
        <p class="small text-muted mt-2">Aktivasi fitur tidak menggantikan pengesahan kebijakan perusahaan. Setiap perubahan dicatat. Jika data sumber berubah saat diperbarui, penyesuaian lama direset untuk ditinjau kembali. Menonaktifkan hanya memengaruhi gaji yang belum disahkan.</p>
        <form method="post" action="{{ route('billing.gaji.attendance.setting') }}" class="row g-2 align-items-end" data-payroll-operation>@csrf
            <div class="col-md-3"><label class="form-label" for="attendance-enabled">Potongan otomatis</label><select name="enabled" id="attendance-enabled" class="form-select"><option value="1" @selected($attendanceEnabled)>Aktif</option><option value="0" @selected(!$attendanceEnabled)>Nonaktif</option></select></div>
            <div class="col-md-6"><label class="form-label" for="attendance-setting-reason">Alasan perubahan</label><input id="attendance-setting-reason" name="reason" class="form-control" required minlength="10" maxlength="1000"></div><div class="col-md-3"><button class="btn btn-outline-success">Simpan Pengaturan</button></div>
        </form>
        @if($attendanceEnabled && $attendancePeriod && !$attendancePeriod->remote_locked_at && !$attendancePeriod->lock_attempted_at)
        <form method="post" action="{{ route('billing.gaji.attendance.adjustment') }}" class="row g-2 align-items-end mt-3" data-payroll-operation>@csrf<input type="hidden" name="bulan" value="{{ $bulan }}"><input type="hidden" name="tahun" value="{{ $tahun }}">
            <div class="col-md-4 attendance-select-field"><label for="adjust-employee" class="form-label">Pegawai</label><select id="adjust-employee" name="employee_id" class="form-select" required>@foreach($data as $person)<option value="{{ $person->id }}">{{ $person->nama }}</option>@endforeach</select></div>
            <div class="col-md-4"><label for="adjust-date" class="form-label">Tanggal kerja</label><input id="adjust-date" name="date" type="date" min="{{ $attendanceDates['from'] }}" max="{{ $attendanceDates['to'] }}" class="form-control" required></div>
            <div class="col-md-4"><label for="adjust-amount" class="form-label">Potongan pengganti hari itu · Rp</label><input id="adjust-amount" name="amount" type="number" min="0" step="1" class="form-control" required></div>
            <div class="col-md-8"><label for="adjust-reason" class="form-label">Alasan penyesuaian</label><input id="adjust-reason" name="reason" class="form-control" required minlength="10" maxlength="1000"></div>
            <div class="col-md-4"><label class="d-block small mb-2"><input type="checkbox" name="reset" value="1"> Hapus penyesuaian, gunakan hitungan otomatis</label><button class="btn btn-outline-success">Simpan Penyesuaian</button></div>
        </form>
        @endif
    </details>
    @endif
</div></div>
