<?php

namespace App\Services;

use App\Models\User;
use App\Support\ReminderRegistry;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Membaca rekap pengingat untuk satu akun: cakupan vendor, nolam filter & oli yang
 * diaktifkan, dan ambang waktu (dihitung terhadap waktu sekarang, bukan waktu rekap).
 */
class ReminderReader
{
    public function query(User $user, string $type): Builder
    {
        abort_unless(ReminderRegistry::allows($user, $type), 403);
        $now = now();
        $query = DB::table('reminder_items')->where('type', $type)
            ->when($user->isVendorAccount(), fn (Builder $query) => $query->where('vendor_id', $user->vendor_id ?? 0));

        return match ($type) {
            ReminderRegistry::STANDBY => $query->where('started_at', '<=', $now->copy()->subHours(ReminderRegistry::STANDBY_HOURS))->orderBy('started_at'),
            ReminderRegistry::UNLOADING => $query->where('started_at', '<=', $now->copy()->subHours(ReminderRegistry::UNLOADING_HOURS))->orderBy('started_at'),
            ReminderRegistry::FILTER_OIL => $query->when($user->requiresFilterOilActivation(), fn (Builder $query) => $query->where('activated', true))->orderBy('id'),
            ReminderRegistry::INVOICE_DUE => $query->where('started_at', '<=', $now->copy()->startOfDay()->addDays(ReminderRegistry::INVOICE_DAYS))->orderBy('started_at'),
            ReminderRegistry::ATTENDANCE => $query->where('started_at', $now->copy()->subDay()->startOfDay())->orderByDesc('id'),
        };
    }

    /** Baris rekap siap tampil: payload + durasi/selisih dihitung saat ini. */
    public function rows(User $user, string $type, int $offset = 0, int $limit = 25): array
    {
        $now = now();

        return $this->query($user, $type)->offset($offset)->limit($limit)->get()->map(function ($row) use ($type, $now): array {
            $data = json_decode($row->payload, true, 512, JSON_THROW_ON_ERROR) + ['id' => (int) $row->subject_id];
            $started = $row->started_at ? Carbon::parse($row->started_at) : null;

            return $data + match ($type) {
                ReminderRegistry::STANDBY, ReminderRegistry::UNLOADING => ['since' => $started->toDateTimeString(), 'elapsed' => self::elapsed($started, $now)],
                ReminderRegistry::INVOICE_DUE => ['jatuh_tempo' => $started->toDateString(), 'sisa_hari' => (int) $now->copy()->startOfDay()->diffInDays($started, false)],
                default => [],
            };
        })->all();
    }

    /** Jumlah pengingat per tabel yang ditampilkan akun (tabel nonaktif di pengaturan tidak dihitung/di-query). */
    public function counts(User $user): array
    {
        return collect(app(ReminderPreferences::class)->enabled($user))->mapWithKeys(fn (string $type): array => [$type => $this->query($user, $type)->count()])->all();
    }

    public function refreshedAt(string $type): ?Carbon
    {
        $value = DB::table('reminder_refreshes')->where('type', $type)->value('refreshed_at');

        return $value ? Carbon::parse($value) : null;
    }

    /** "X hari Y jam" sejak $from. */
    public static function elapsed(Carbon $from, Carbon $now): array
    {
        $hours = (int) floor($from->diffInMinutes($now) / 60);

        return ['days' => intdiv($hours, 24), 'hours' => $hours % 24];
    }
}
