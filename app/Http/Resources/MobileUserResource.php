<?php

namespace App\Http\Resources;

use App\Models\LoadNoteDraft;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobileUserResource extends JsonResource
{
    /** @return array{id: int, username: string, name: string, role: string, vendor_id: ?int, permissions: array{database: bool, load_notes: bool, vendor_billing: bool, all_vendors: bool, vendor_finance: bool, maintenance_history: bool}} */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'username' => $this->username, 'name' => $this->name,
            'role' => $this->role, 'vendor_id' => $this->vendor_id,
            'permissions' => ['database' => in_array($this->role, config('mobile.database_roles'), true),
                'load_notes' => $this->resource->can('viewAny', LoadNoteDraft::class),
                // Menu vendor di aplikasi: billing (vendor & vendor operational), keuangan dan
                // edit/hapus riwayat ban & aki hanya untuk akun vendor.
                'vendor_billing' => ($this->resource->isVendorAccount() && $this->vendor_id !== null) || $this->resource->isOperasional(),
                // Operasional: menu vendor-operational untuk seluruh vendor (filter vendor, baca saja).
                'all_vendors' => $this->resource->isOperasional(),
                'vendor_finance' => $this->resource->canViewVendorFinance(),
                'maintenance_history' => $this->resource->isVendorAccount() && $this->resource->canManageMaintenanceHistory(),
                'upah_gendong_settings' => $this->resource->isVendorAccount() && $this->resource->canManageUpahGendong(),
                // Driver: menu driver, dikunci ke satu nolam (lihat driver_vehicle).
                'driver' => $this->resource->isDriver()],
            'driver_vehicle' => $this->when($this->resource->isDriver(), fn (): ?array => ($vehicle = $this->resource->activeDriverVehicle())
                ? ['id' => $vehicle->id, 'nomor_lambung' => (string) $vehicle->nomor_lambung, 'vendor_id' => (int) $vehicle->vendor_id] : null)];
    }
}
