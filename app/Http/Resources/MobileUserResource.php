<?php

namespace App\Http\Resources;

use App\Models\LoadNoteDraft;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobileUserResource extends JsonResource
{
    /** @return array{id: int, username: string, name: string, role: string, vendor_id: ?int, permissions: array{database: bool, load_notes: bool, vendor_billing: bool, vendor_finance: bool, maintenance_history: bool}} */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'username' => $this->username, 'name' => $this->name,
            'role' => $this->role, 'vendor_id' => $this->vendor_id,
            'permissions' => ['database' => in_array($this->role, config('mobile.database_roles'), true),
                'load_notes' => $this->resource->can('viewAny', LoadNoteDraft::class),
                // Menu vendor di aplikasi: billing (vendor & vendor operational), keuangan dan
                // edit/hapus riwayat ban & aki hanya untuk akun vendor.
                'vendor_billing' => $this->resource->isVendorAccount() && $this->vendor_id !== null,
                'vendor_finance' => $this->resource->canViewVendorFinance(),
                'maintenance_history' => $this->resource->isVendorAccount() && $this->resource->canManageMaintenanceHistory(),
                'upah_gendong_settings' => $this->resource->isVendorAccount() && $this->resource->canManageUpahGendong()]];
    }
}
