<?php

namespace App\Services;

use App\Models\GroupWa;
use App\Models\Karyawan;
use App\Models\KasBesar;
use App\Models\RekapGaji;
use App\Models\RekapGajiDetail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Simpan Form Gaji satu periode (rekap, detail, cicilan kasbon, Kas Besar
 * keluar) dan perbarui data absensinya. Dipakai bersama halaman Form Gaji web
 * dan API mobile (Admin & SU). Semua proses gaji memakai lock yang sama.
 */
class PayrollGeneration
{
    /** Kunci galat saat proses gaji lain masih memegang lock. */
    public const BUSY = 'payroll_busy';

    public function __construct(protected PayrollService $payroll) {}

    public function ensureAvailable(Carbon $period): void
    {
        if (RekapGaji::where('bulan', $period->month)->where('tahun', $period->year)->exists()) {
            throw ValidationException::withMessages(['bulan' => 'Form gaji periode '.$period->copy()->locale('id')->translatedFormat('F Y').' sudah dibuat.']);
        }
        $later = RekapGaji::where(function (Builder $query) use ($period): void {
            $query->where('tahun', '>', $period->year)->orWhere(function (Builder $query) use ($period): void {
                $query->where('tahun', $period->year)->where('bulan', '>', $period->month);
            });
        })->orderByDesc('tahun')->orderByDesc('bulan')->first();
        if ($later) {
            $laterPeriod = Carbon::create($later->tahun, $later->bulan, 1)->locale('id')->translatedFormat('F Y');
            throw ValidationException::withMessages(['bulan' => 'Gaji periode '.$laterPeriod.' sudah dibuat. Periode sebelumnya tidak dapat dibuat karena saldo kasbon telah berubah. Proses gaji sesuai urutan periode.']);
        }
    }

    /**
     * Simpan gaji [$period]. [$revision]: sidik pratinjau yang ditinjau; bila
     * absensi aktif atau revisi dikirim, pratinjau yang berubah ditolak.
     * Periode yang sudah disimpan ditolak, jadi ketukan ulang aman.
     */
    public function store(Carbon $period, ?string $revision): KasBesar
    {
        $this->payroll->attendance->clear();
        $this->ensureAvailable($period);
        $this->payroll->assertProcessable($period);
        $this->payroll->bpjs->requireConfigured($period->year);
        $cash = Cache::lock('payroll-generation', 300)->get(function () use ($period, $revision): KasBesar {
            $this->ensureAvailable($period);
            $attendance = $this->payroll->attendance;
            $checkRevision = function () use ($period, $revision, $attendance): void {
                if (($attendance->enabled() || filled($revision)) && (! is_string($revision) || ! hash_equals($this->payroll->revision($period->month, $period->year), $revision))) {
                    throw ValidationException::withMessages(['attendance' => 'Pratinjau gaji berubah atau kedaluwarsa. Muat ulang dan tinjau sebelum menyimpan.']);
                }
            };
            $checkRevision();
            $people = $this->payroll->employeesForPeriod($period->month, $period->year);
            $previews = $this->payroll->directorsForPeriod($period->month, $period->year)->concat($people)->map(fn ($person) => $this->payroll->previewRow($person, $period->month, $period->year));
            if ($previews->contains(fn ($row) => $row['total'] < 0)) {
                throw ValidationException::withMessages(['bulan' => 'Potongan melebihi pendapatan bersih. Periksa sebelum menyimpan.']);
            }
            if ((int) KasBesar::orderByDesc('id')->value('saldo') < $previews->sum('total')) {
                throw ValidationException::withMessages(['bulan' => 'Saldo Kas Besar Tidak Cukup']);
            }
            $lockedPeriod = $attendance->lock($period->month, $period->year, $people->modelKeys());

            return DB::transaction(function () use ($period, $lockedPeriod, $checkRevision): KasBesar {
                $this->payroll->assertProcessable($period);
                $this->payroll->bpjs->requireConfigured($period->year, true);
                $this->ensureAvailable($period);
                $kasBesar = KasBesar::orderByDesc('id')->lockForUpdate()->first();
                $data = $this->payroll->employeesForPeriod($period->month, $period->year, true);
                $direksi = $this->payroll->directorsForPeriod($period->month, $period->year, true);
                $checkRevision();
                if ($data->isEmpty() && $direksi->isEmpty()) {
                    throw ValidationException::withMessages(['bulan' => 'Tidak ada pegawai atau direksi untuk periode gaji ini.']);
                }
                $rekap = RekapGaji::create([
                    'uraian' => 'Gaji Bulan '.$period->copy()->locale('id')->monthName.' Tahun '.$period->year,
                    'bulan' => $period->month, 'tahun' => $period->year, 'total' => 0,
                ]);
                $total = 0;
                foreach ($direksi->concat($data) as $person) {
                    $calc = $this->payroll->calculateComponent($person, $period->year);
                    $payments = $person instanceof Karyawan ? $this->payroll->kasbonPayments($person, $period->month, $period->year) : [];
                    $kasbon = array_sum(array_column($payments, 'amount'));
                    $deduction = $person instanceof Karyawan ? $this->payroll->attendance->calculate($person, $period->month, $period->year) : ['amount' => 0, 'raw' => 0, 'days' => []];
                    $sisaGaji = $calc['pendapatan_bersih'] - $kasbon - $deduction['amount'];
                    if ($sisaGaji < 0) {
                        throw ValidationException::withMessages(['bulan' => 'Potongan kasbon '.$person->nama.' melebihi pendapatan bersih. Periksa kasbon sebelum menyimpan gaji.']);
                    }
                    $total += $sisaGaji;
                    RekapGajiDetail::create([
                        'rekap_gaji_id' => $rekap->id,
                        'nik' => $person instanceof Karyawan ? $person->kode.sprintf('%03d', $person->nomor) : 'Direksi',
                        'nama' => $person->nama, 'jabatan' => $person instanceof Karyawan ? ($person->jabatan?->nama ?? '—') : $person->jabatan,
                        'gaji_pokok' => $person->gaji_pokok, 'tunjangan_jabatan' => $person->tunjangan_jabatan, 'tunjangan_keluarga' => $person->tunjangan_keluarga,
                        'bpjs_tk' => $calc['bpjs_tk'], 'bpjs_k' => $calc['bpjs_k'], 'potongan_bpjs_tk' => $calc['pot_tk'], 'potongan_bpjs_kesehatan' => $calc['pot_k'],
                        'pendapatan_kotor' => $calc['pendapatan_kotor'], 'pendapatan_bersih' => $calc['pendapatan_bersih'], 'kasbon' => $kasbon, 'sisa_gaji_dibayar' => $sisaGaji,
                        'potongan_absensi' => $deduction['amount'], 'potongan_absensi_sebelum_batas' => $deduction['raw'], 'rincian_absensi' => json_encode($deduction, JSON_THROW_ON_ERROR),
                        'transfer_ke' => $person->nama_rekening, 'bank' => $person->bank, 'no_rekening' => $person->no_rekening,
                    ]);
                    foreach ($payments as $payment) {
                        $loan = $payment['loan'];
                        $remaining = (int) $loan->sisa_kas - $payment['amount'];
                        $loan->update(['total_bayar' => (int) $loan->total_bayar + $payment['amount'], 'sisa_kas' => $remaining, 'lunas' => $remaining === 0]);
                    }
                }
                if (! $kasBesar || $kasBesar->saldo < $total) {
                    throw ValidationException::withMessages(['bulan' => 'Saldo Kas Besar Tidak Cukup']);
                }
                $rekap->update(['total' => $total]);
                $this->payroll->attendance->seal($lockedPeriod, $rekap->id);

                return KasBesar::create([
                    'uraian' => $rekap->uraian, 'tanggal' => now()->toDateString(), 'nominal_transaksi' => $total, 'jenis_transaksi_id' => 2,
                    'saldo' => $kasBesar->saldo - $total, 'modal_investor_terakhir' => $kasBesar->modal_investor_terakhir,
                    'transfer_ke' => 'Msng2 Karyawan', 'bank' => 'BCA', 'no_rekening' => '-',
                ]);
            });
        });
        if (! $cash) {
            throw ValidationException::withMessages([self::BUSY => 'Pembuatan gaji sedang diproses. Tunggu sebentar lalu muat ulang halaman.']);
        }

        return $cash;
    }

    /** Perbarui snapshot absensi periode (sebelum gaji disahkan), di bawah lock proses gaji. */
    public function refreshAttendance(int $month, int $year): void
    {
        abort_if(RekapGaji::where('bulan', $month)->where('tahun', $year)->exists(), 409, 'Gaji sudah disahkan.');
        $ran = Cache::lock('payroll-generation', 300)->get(function () use ($month, $year): bool {
            $this->payroll->attendance->refresh($month, $year, $this->payroll->employeesForPeriod($month, $year)->modelKeys());

            return true;
        });
        if (! $ran) {
            throw ValidationException::withMessages(['attendance' => 'Proses gaji lain sedang berjalan.']);
        }
    }

    /** WA ke grup Kas Besar; false bila gagal (gaji sudah tersimpan, jangan ulangi). */
    public function notify(KasBesar $kasBesar): bool
    {
        try {
            $group = GroupWa::where('untuk', 'kas-besar')->first();
            if (! $group) {
                return true;
            }
            $pesan = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                "*Form Gaji Karyawan*\n".
                "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                'Nilai :  *Rp. '.number_format((int) $kasBesar->nominal_transaksi, 0, ',', '.')."*\n\n".
                "Ditransfer ke rek:\n\n".
                "Nama     : Masing2 Karyawan\n\n".
                "==========================\n".
                "Sisa Saldo Kas Besar : \n".
                'Rp. '.number_format($kasBesar->saldo, 0, ',', '.')."\n\n".
                "Total Modal Investor : \n".
                'Rp. '.number_format($kasBesar->modal_investor_terakhir, 0, ',', '.')."\n\n".
                "Terima kasih 🙏🙏🙏\n";

            return app(GroupWa::class)->sendWa($group->nama_group, $pesan) !== false;
        } catch (\Throwable $e) {
            Log::warning('WA Notifikasi Gagal: '.$e->getMessage());

            return false;
        }
    }
}
