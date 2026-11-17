<?php

namespace App\Services;

use App\Models\Karyawan;
use App\Support\ReminderRegistry;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Rekap Dashboard Pengingat: satu kali pemindaian untuk semua akun, hasilnya disimpan
 * di reminder_items. Yang disimpan titik waktu pemicu; ambang (48 jam, 24 jam, 2 hari)
 * diterapkan saat dibaca (ReminderReader) sehingga rekap yang berumur tidak basi.
 */
class ReminderRefresher
{
    /** Status absensi yang dilewati saat menghitung alpa berturut-turut (bukan hari kerja). */
    private const DAY_OFF = ['off', 'holiday'];

    /** Batas mundur penghitungan alpa berturut-turut. */
    private const STREAK_LIMIT_DAYS = 62;

    public function __construct(private PayrollAttendanceService $attendance) {}

    /** @param list<string>|null $types */
    public function refresh(?array $types = null): array
    {
        $counts = [];
        foreach ($types ?? ReminderRegistry::keys() as $type) {
            $rows = match ($type) {
                ReminderRegistry::STANDBY => $this->standby(),
                ReminderRegistry::UNLOADING => $this->unloading(),
                ReminderRegistry::FILTER_OIL => $this->filterOil(),
                ReminderRegistry::INVOICE_DUE => $this->invoices(),
                ReminderRegistry::ATTENDANCE => $this->absences(now()->subDay()->startOfDay()),
            };
            $this->store($type, $rows);
            $counts[$type] = count($rows);
        }

        return $counts;
    }

    /** Nolam aktif (tidak sedang jalan) beserta waktu bongkar terakhirnya; yang belum pernah bongkar tidak ikut. */
    private function standby(): array
    {
        return DB::table('vehicles as v')
            ->join('kas_uang_jalans as k', 'k.vehicle_id', '=', 'v.id')
            ->join('transaksis as t', 't.kas_uang_jalan_id', '=', 'k.id')
            ->leftJoin('vendors as vd', 'vd.id', '=', 'v.vendor_id')
            ->where('v.status', 'aktif')->where('t.status', 3)->where('t.void', 0)->whereNotNull('t.tanggal_bongkar')
            ->groupBy('v.id', 'v.vendor_id', 'v.nomor_lambung', 'vd.nama')
            ->get(['v.id', 'v.vendor_id', 'v.nomor_lambung', 'vd.nama as vendor', DB::raw('MAX(t.tanggal_bongkar) as last_unloaded')])
            ->map(fn ($row): array => $this->row($row->id, $row->vendor_id, $row->last_unloaded,
                ['nomor_lambung' => (string) $row->nomor_lambung, 'vendor' => (string) $row->vendor]))->all();
    }

    /** Perjalanan yang sudah UJ keluar (nota muat) tetapi belum bongkar; waktu muat = input UJ keluar. */
    private function unloading(): array
    {
        return DB::table('transaksis as t')
            ->join('kas_uang_jalans as k', 'k.id', '=', 't.kas_uang_jalan_id')
            ->leftJoin('vehicles as v', 'v.id', '=', 'k.vehicle_id')
            ->leftJoin('vendors as vd', 'vd.id', '=', 'k.vendor_id')
            ->leftJoin('rutes as r', 'r.id', '=', 'k.rute_id')
            ->where('t.status', 2)->where('t.void', 0)
            ->get(['t.id', 'k.vendor_id', 't.created_at', 'v.nomor_lambung', 'vd.nama as vendor', 'r.nama as rute'])
            ->map(fn ($row): array => $this->row($row->id, $row->vendor_id, $row->created_at,
                ['nomor_lambung' => (string) $row->nomor_lambung, 'vendor' => (string) $row->vendor, 'rute' => (string) $row->rute]))->all();
    }

    /**
     * Nolam (tidak nonaktif) yang minimal satu kategori filter & olinya tinggal <= 2 rit dari limit
     * (negatif bila sudah lewat), memakai log terakhir per kategori seperti menu Filter & Oli.
     */
    private function filterOil(): array
    {
        $limits = DB::table('kategori_filter_oli_mesins')->pluck('limit_ritase', 'id');
        $vehicles = [];
        DB::table('filter_oli_logs as l')
            ->join('vehicles as v', 'v.id', '=', 'l.vehicle_id')
            ->leftJoin('vendors as vd', 'vd.id', '=', 'v.vendor_id')
            ->where('v.status', '!=', 'nonaktif')->where('l.created_at', '<=', now())
            ->orderByDesc('l.created_at')->orderByDesc('l.id')
            ->select(['l.vehicle_id', 'l.kategori_filter_oli_mesin_id as category', 'l.ritase', 'v.vendor_id', 'v.nomor_lambung', 'v.pembatasan_filter_oli', 'vd.nama as vendor'])
            ->lazy()->each(function ($log) use ($limits, &$vehicles): void {
                $vehicle = $vehicles[$log->vehicle_id] ??= ['log' => $log, 'sisa' => [], 'seen' => []];
                if (isset($vehicle['seen'][$log->category]) || ! isset($limits[$log->category])) {
                    return;
                }
                $vehicle['seen'][$log->category] = true;
                $remaining = round((float) $limits[$log->category] - (float) $log->ritase, 1);
                if ($remaining <= ReminderRegistry::FILTER_OIL_REMAINING) {
                    $vehicle['sisa'][$log->category] = $remaining;
                }
                $vehicles[$log->vehicle_id] = $vehicle;
            });

        // Disimpan urut nomor lambung; halaman membaca urut id.
        return collect($vehicles)->filter(fn (array $vehicle): bool => $vehicle['sisa'] !== [])
            ->sortBy(fn (array $vehicle): string => (string) $vehicle['log']->nomor_lambung, SORT_NATURAL)
            ->map(fn (array $vehicle, int $id): array => $this->row($id, $vehicle['log']->vendor_id, null,
                ['nomor_lambung' => (string) $vehicle['log']->nomor_lambung, 'vendor' => (string) $vehicle['log']->vendor, 'sisa' => $vehicle['sisa']],
                (bool) $vehicle['log']->pembatasan_filter_oli))->values()->all();
    }

    /** Invoice tagihan belum lunas; jatuh tempo = estimasi_pembayaran. */
    private function invoices(): array
    {
        return DB::table('invoice_tagihans as i')
            ->leftJoin('customers as c', 'c.id', '=', 'i.customer_id')
            ->where('i.lunas', 0)->whereNotNull('i.estimasi_pembayaran')
            ->get(['i.id', 'i.no_invoice', 'i.estimasi_pembayaran', 'i.sisa_tagihan', 'i.total_tagihan', 'c.nama as customer', 'c.singkatan'])
            ->map(fn ($row): array => $this->row($row->id, null, $row->estimasi_pembayaran, [
                'customer' => (string) $row->customer, 'singkatan' => (string) $row->singkatan, 'no_invoice' => (string) $row->no_invoice,
                'sisa_tagihan' => (float) $row->sisa_tagihan, 'total_tagihan' => (float) $row->total_tagihan]))->all();
    }

    /**
     * Karyawan yang alpa pada $day (bukan libur). Alpa berturut-turut dihitung mundur dari $day dengan
     * melewati hari libur; total = jumlah alpa periode gaji berjalan sampai $day.
     */
    public function absences(CarbonInterface $day): array
    {
        if (! $this->attendance->enabled()) {
            return [];
        }
        $current = $this->snapshot($day);
        if ($current === null) {
            return [];
        }
        $absent = collect($current['employees'] ?? [])->filter(fn (array $person): bool => ($person['attendance_required'] ?? true)
            && $this->status($person, $day) === 'absent');
        $positions = Karyawan::with('jabatan:id,nama')->whereIn('id', $absent->pluck('source_employee_id'))->get()->keyBy('id');

        return $absent->map(function (array $person) use ($day, $current, $positions): array {
            $id = (int) $person['source_employee_id'];
            $streak = 0;
            for ($date = Carbon::parse($day), $i = 0; $i < self::STREAK_LIMIT_DAYS; $date = $date->subDay(), $i++) {
                $status = $this->status($this->snapshot($date)['people'][$id] ?? null, $date);
                if ($status === 'absent') {
                    $streak++;
                } elseif (! in_array($status, self::DAY_OFF, true)) {
                    break;
                }
            }
            $total = collect($person['days'] ?? [])->filter(fn (array $item): bool => ($item['status'] ?? null) === 'absent'
                && $item['date'] <= $day->toDateString() && $item['date'] >= $current['from'])->count();
            $employee = $positions->get($id);

            return $this->row($id, null, $day->toDateString(), [
                'nama' => (string) ($employee->nama ?? $person['name'] ?? '—'), 'jabatan' => (string) ($employee?->jabatan?->nama ?? '—'),
                'berturut' => $streak, 'total' => $total]);
        })->values()->all();
    }

    /** Snapshot periode gaji (26 s.d. 25) yang memuat $date, beserta indeks per karyawan. */
    private function snapshot(CarbonInterface $date): ?array
    {
        $end = $date->day > 25 ? Carbon::parse($date)->startOfMonth()->addMonthNoOverflow() : Carbon::parse($date);
        $period = $this->attendance->period($end->month, $end->year);
        if (! $period) {
            return null;
        }
        $snapshot = $this->attendance->payload($period);

        return $snapshot + ['people' => array_column($snapshot['employees'] ?? [], null, 'source_employee_id')];
    }

    private function status(?array $person, CarbonInterface $date): ?string
    {
        foreach ($person['days'] ?? [] as $day) {
            if (($day['date'] ?? null) === $date->toDateString()) {
                return $day['status'] ?? null;
            }
        }

        return null;
    }

    private function row(int $subject, int|string|null $vendor, ?string $startedAt, array $payload, bool $activated = true): array
    {
        return ['subject_id' => $subject, 'vendor_id' => $vendor === null ? null : (int) $vendor,
            'started_at' => $startedAt === null ? null : Carbon::parse($startedAt)->toDateTimeString(),
            'activated' => $activated, 'payload' => json_encode($payload, JSON_THROW_ON_ERROR)];
    }

    /** Ganti isi satu jenis pengingat secara atomik, lalu catat waktu rekapnya. */
    private function store(string $type, array $rows): void
    {
        $now = now();
        DB::transaction(function () use ($type, $rows, $now): void {
            DB::table('reminder_items')->where('type', $type)->delete();
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('reminder_items')->insert(array_map(fn (array $row): array => $row + ['type' => $type, 'created_at' => $now, 'updated_at' => $now], $chunk));
            }
            DB::table('reminder_refreshes')->updateOrInsert(['type' => $type], ['refreshed_at' => $now]);
        });
    }
}
