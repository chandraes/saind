<?php

namespace App\Http\Resources;

use App\Models\LoadNoteDraft;
use App\Support\ReminderRegistry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobileUserResource extends JsonResource
{
    /** @return array{id: int, username: string, name: string, role: string, vendor_id: ?int, permissions: array{database: bool, load_notes: bool, vendor_billing: bool, all_vendors: bool, vendor_finance: bool, maintenance_history: bool, maintenance_history_edit: bool, reminders: bool}} */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'username' => $this->username, 'name' => $this->name,
            'role' => $this->role, 'vendor_id' => $this->vendor_id,
            'permissions' => ['database' => in_array($this->role, config('mobile.database_roles'), true),
                'load_notes' => $this->resource->can('viewAny', LoadNoteDraft::class),
                // Menu vendor di aplikasi: billing (vendor & vendor operational), keuangan dan
                // edit/hapus riwayat ban & aki hanya untuk akun vendor.
                'vendor_billing' => ($this->resource->isVendorAccount() && $this->vendor_id !== null) || $this->resource->seesAllVendors(),
                // Operasional: menu vendor-operational untuk seluruh vendor (filter vendor, baca saja).
                'all_vendors' => $this->resource->seesAllVendors(),
                'vendor_finance' => $this->resource->canViewVendorFinance(),
                'maintenance_history' => $this->resource->isVendorAccount() && $this->resource->canManageMaintenanceHistory(),
                // Ubah tanggal/hapus riwayat ban, aki, filter & oli: vendor (nolam sendiri), Admin & SU
                // (semua vendor, password konfirmasi). Catat baru tetap hanya vendor (maintenance_history).
                'maintenance_history_edit' => $this->resource->canManageMaintenanceHistory(),
                // Setting Upah Gendong: vendor & vendor-induk (nolam sendiri), Admin & SU (semua vendor).
                'upah_gendong_settings' => $this->resource->canManageUpahGendong(),
                // Kas UJ Ditahan per nolam: vendor-induk (nolam vendornya) & operasional (semua nolam).
                'held_cash' => $this->resource->canViewNolamHeldCash(),
                // Driver: menu driver, dikunci ke satu nolam (lihat driver_vehicle).
                'driver' => $this->resource->isDriver(),
                // HRD: hanya menu Potongan Absensi (baca saja).
                'attendance_deductions' => $this->resource->isHrd(),
                // Eksekutif (GM & Direksi) serta Admin/SU: Nota Tagihan per customer (baca saja).
                'customer_bills' => $this->resource->readsExecutiveMenus(),
                // Eksekutif (GM & Direksi) serta Admin/SU: Gaji Karyawan (seluruh kolom Form Gaji, baca saja).
                'payroll' => $this->resource->readsExecutiveMenus(),
                // Dashboard Pengingat: role yang punya minimal satu tabel pengingat.
                'reminders' => ReminderRegistry::for($this->resource) !== []],
            'driver_vehicle' => $this->when($this->resource->isDriver(), fn (): ?array => ($vehicle = $this->resource->activeDriverVehicle())
                ? ['id' => $vehicle->id, 'nomor_lambung' => (string) $vehicle->nomor_lambung, 'vendor_id' => (int) $vehicle->vendor_id] : null)];
    }
}
