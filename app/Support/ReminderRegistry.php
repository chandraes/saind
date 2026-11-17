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

    /** @var array<string, array{title: string, roles: list<string>}> urutan = urutan bawaan */
    private const DEFINITIONS = [
        self::STANDBY => ['title' => 'RFU / BD — standby lebih dari 48 jam sejak bongkar terakhir', 'roles' => self::VEHICLE_ROLES],
        self::UNLOADING => ['title' => 'Belum bongkar lebih dari 24 jam sejak muat', 'roles' => self::VEHICLE_ROLES],
        self::FILTER_OIL => ['title' => 'Filter & oli mesin mendekati limit ritase', 'roles' => [...self::VEHICLE_ROLES, 'user']],
        self::INVOICE_DUE => ['title' => 'Invoice mendekati jatuh tempo', 'roles' => ['su', 'admin', 'user', 'direksi', 'general-manager']],
        self::ATTENDANCE => ['title' => 'Karyawan tidak masuk (alpa) kemarin', 'roles' => ['su', 'admin', 'user', 'direksi', 'general-manager', 'hrd']],
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
