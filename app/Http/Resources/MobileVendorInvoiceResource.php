<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class MobileVendorInvoiceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return Arr::only($this->resource, ['id', 'kind', 'invoice_number', 'category_name', 'issued_on', 'due_on', 'paid', 'paid_on']);
    }
}
