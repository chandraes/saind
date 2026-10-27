<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobileUserResource extends JsonResource
{
    /** @return array{id: int, username: string, name: string, role: string, vendor_id: ?int, permissions: array{database: bool}} */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'username' => $this->username, 'name' => $this->name,
            'role' => $this->role, 'vendor_id' => $this->vendor_id,
            'permissions' => ['database' => in_array($this->role, config('mobile.database_roles'), true)]];
    }
}
