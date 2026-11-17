<?php

namespace App\Services;

use App\Models\User;
use App\Support\ReminderRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Pengaturan tampilan Dashboard Pengingat per akun (urutan & aktif/nonaktif), hanya untuk
 * tabel yang boleh dilihat role-nya. Dipakai web dan kelak API mobile.
 */
class ReminderPreferences
{
    /** @return list<array{key: string, title: string, enabled: bool}> semua tabel milik role, urut sesuai pengaturan */
    public function layout(User $user): array
    {
        $allowed = ReminderRegistry::for($user);
        $saved = DB::table('user_reminder_settings')->where('user_id', $user->id)->whereIn('reminder_key', $allowed)
            ->get(['reminder_key', 'position', 'enabled'])->keyBy('reminder_key');

        // Tabel tanpa pengaturan (mis. tabel baru) menyusul di belakang dengan urutan bawaan, aktif.
        return collect($allowed)
            ->sortBy(fn (string $key, int $default): array => [$saved->has($key) ? 0 : 1, $saved[$key]->position ?? $default, $default])
            ->map(fn (string $key): array => ['key' => $key, 'title' => ReminderRegistry::title($key), 'enabled' => (bool) ($saved[$key]->enabled ?? true)])
            ->values()->all();
    }

    /** @return list<string> tabel aktif sesuai urutan akun */
    public function enabled(User $user): array
    {
        return array_column(array_filter($this->layout($user), fn (array $table): bool => $table['enabled']), 'key');
    }

    /**
     * @param  list<string>  $order  seluruh tabel milik role dalam urutan baru
     * @param  list<string>  $enabled  tabel yang ditampilkan
     */
    public function save(User $user, array $order, array $enabled): void
    {
        $allowed = ReminderRegistry::for($user);
        if (count($order) !== count($allowed) || array_diff($allowed, $order) !== [] || array_diff($enabled, $allowed) !== []) {
            throw ValidationException::withMessages(['order' => 'Daftar tabel pengingat tidak sesuai. Muat ulang halaman lalu coba lagi.']);
        }
        $now = now();
        DB::table('user_reminder_settings')->upsert(array_map(fn (string $key, int $position): array => [
            'user_id' => $user->id, 'reminder_key' => $key, 'position' => $position, 'enabled' => in_array($key, $enabled, true),
            'created_at' => $now, 'updated_at' => $now,
        ], $order, array_keys($order)), ['user_id', 'reminder_key'], ['position', 'enabled', 'updated_at']);
    }
}
