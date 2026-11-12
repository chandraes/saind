<?php

namespace App\Support;

use App\Models\User;

/**
 * Menu dashboard untuk akun milik vendor (vendor dan vendor operational).
 * Satu sumber menu agar kedua role selalu seragam; hanya vendor yang
 * mendapat menu keuangan (Kas Vendor).
 */
class VendorDashboardMenu
{
    /** @return list<array{key: string, title: string, menus: list<array{title: string, description: string, url: string, icon: string}>}> */
    public static function sections(User $user): array
    {
        $menu = fn (string $title, string $description, string $url, string $icon): array => compact('title', 'description', 'url', 'icon');
        $documents = [
            $menu('Nota Muat Masuk', 'Pantau pengajuan dan hasil pemeriksaan nota.', route('billing.nota-muat.index'), 'nota-muat-masuk.svg'),
            $menu('Nota Bongkar', 'Lihat perjalanan dan progres pembongkaran.', route('vendor-operational.nota-bongkar'), 'bongkar.svg'),
            $menu('Nota Bayar Vendor', $user->canViewVendorFinance() ? 'Periksa nota beserta nilai bayar vendor.' : 'Periksa nota dan rincian perjalanan vendor.', route('vendor-operational.nota-bayar'), 'bayar.svg'),
            $menu('Invoice Vendor', 'Telusuri invoice dan nota yang tercantum.', route('vendor-operational.invoice'), 'invoice-bayar.svg'),
        ];
        if ($user->canViewVendorFinance()) {
            $documents[] = $menu('Kas Vendor', 'Mutasi kas dan saldo vendor per bulan.', route('kas-per-vendor.index', $user->vendor_id), 'kas-vendor.svg');
        }

        return [
            ['key' => 'documents', 'title' => 'Nota & Invoice', 'menus' => $documents],
            ['key' => 'statistics', 'title' => 'Performa & Upah Gendong', 'menus' => [
                $menu('Perform Unit', 'Performa dan aktivitas kendaraan.', route('statistik.perform-unit'), 'perform-unit.svg'),
                $menu('Perform Unit All', 'Rekap ritase bulanan seluruh unit vendor Anda.', route('statistik.perform-unit.all-vendor'), 'all-vendor.svg'),
                $menu('Upah Gendong', 'Riwayat upah gendong per unit.', route('statistik.upah-gendong'), 'statistik-ug.svg'),
            ]],
            ['key' => 'maintenance', 'title' => 'Maintenance', 'menus' => [
                $menu('Ban Luar', 'Pemakaian dan histori ban per posisi.', route('statistik.ban-luar'), 'db-ban.svg'),
                $menu('Aki', 'Kondisi dan histori aki kendaraan.', route('statistik.aki'), 'aki.svg'),
                $menu('Filter & Oli Mesin', 'Pemakaian dan histori penggantian.', route('statistik.filter-oli'), 'filter-oli-mesin.svg'),
            ]],
        ];
    }
}
