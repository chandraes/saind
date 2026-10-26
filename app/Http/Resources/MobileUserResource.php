<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobileUserResource extends JsonResource
{
    /** @return array{id: int, username: string, name: string, role: string, vendor_id: ?int} */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'username' => $this->username, 'name' => $this->name,
            'role' => $this->role, 'vendor_id' => $this->vendor_id];
    }
}
