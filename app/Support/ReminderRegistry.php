<?php

namespace App\Support;

use App\Models\User;

/**
 * Daftar tabel Dashboard Pengingat: judul, role yang boleh melihat, dan urutan bawaan.
 * Satu sumber untuk rekap, halaman web, dan kelak API mobile.
 */
class ReminderRegistry
{
    public const STANDBY = 'standby';

    public const UNLOADING = 'unloading';

    public const FILTER_OIL = 'filter_oil';

    public const INVOICE_DUE = 'invoice_due';

    public const ATTENDANCE = 'attendance';

    /** Ambang yang diterapkan saat rekap dibaca (bukan saat direkap). */
    public const STANDBY_HOURS = 48;

    public const UNLOADING_HOURS = 24;

    public const FILTER_OIL_REMAINING = 2;

    public const INVOICE_DAYS = 2;

    /** Rute simpan pengaturan tampilan (satu-satunya penulisan yang diizinkan bagi role baca saja). */
    public const PREFERENCE_ROUTE = 'reminders.settings';

    private const VEHICLE_ROLES = ['su', 'admin', 'direksi', 'general-manager', 'operasional', 'vendor', 'vendor-induk', 'vendor-operational'];

    /**
     * label = nama singkat (ringkasan & pengaturan), icon = Font Awesome 4.
     *
     * @var array<string, array{label: string, title: string, description: string, icon: string, roles: list<string>}> urutan = urutan bawaan
     */
    private const DEFINITIONS = [
        self::STANDBY => ['label' => 'RFU / BD', 'title' => 'Unit standby sejak bongkar terakhir', 'icon' => 'fa-truck',
            'description' => 'Nolam aktif yang belum jalan lagi lebih dari 48 jam sejak bongkar terakhir.', 'roles' => self::VEHICLE_ROLES],
        self::UNLOADING => ['label' => 'On Going', 'title' => 'Unit belum bongkar sejak muat', 'icon' => 'fa-hourglass-half',
            'description' => 'Perjalanan yang sudah UJ keluar tetapi belum bongkar lebih dari 24 jam.', 'roles' => self::VEHICLE_ROLES],
        self::FILTER_OIL => ['label' => 'Maintenance', 'title' => 'Filter & oli mesin mendekati limit', 'icon' => 'fa-tint',
            'description' => 'Sisa ritase kategori yang tinggal 2 rit atau kurang; negatif berarti sudah melewati limit.', 'roles' => [...self::VEHICLE_ROLES, 'user']],
        self::INVOICE_DUE => ['label' => 'Jatuh Tempo', 'title' => 'Invoice mendekati jatuh tempo', 'icon' => 'fa-file-text-o',
            'description' => 'Invoice belum lunas yang jatuh tempo dalam 2 hari atau sudah lewat (sisa hari negatif).', 'roles' => ['su', 'admin', 'user', 'direksi', 'general-manager']],
        self::ATTENDANCE => ['label' => 'Absensi', 'title' => 'Karyawan alpa kemarin', 'icon' => 'fa-user-times',
            'description' => 'Karyawan yang tidak masuk tanpa keterangan kemarin; hari libur tidak dihitung.', 'roles' => ['su', 'admin', 'user', 'direksi', 'general-manager', 'hrd']],
    ];

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::DEFINITIONS);
    }

    public static function title(string $key): string
    {
        return self::DEFINITIONS[$key]['title'];
    }

    /** @return array{key: string, label: string, title: string, description: string, icon: string} */
    public static function meta(string $key): array
    {
        return ['key' => $key] + array_diff_key(self::DEFINITIONS[$key], ['roles' => true]);
    }

    public static function allows(User $user, string $key): bool
    {
        return isset(self::DEFINITIONS[$key]) && in_array($user->role, self::DEFINITIONS[$key]['roles'], true);
    }

    /** @return list<string> tabel yang boleh dilihat akun, urutan bawaan */
    public static function for(User $user): array
    {
        return array_values(array_filter(self::keys(), fn (string $key): bool => self::allows($user, $key)));
    }
}
