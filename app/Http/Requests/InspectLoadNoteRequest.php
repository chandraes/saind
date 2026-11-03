<?php

namespace App\Http\Requests;

use App\Models\LoadNoteDraft;
use Illuminate\Foundation\Http\FormRequest;

class InspectLoadNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', LoadNoteDraft::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $weight = StoreLoadNoteDraftRequest::weightRules();

        return ['header_name' => ['required', 'string', 'max:200'],
            'unit_code' => ['required', 'string', 'max:30'], 'route_reference' => ['required', 'string', 'max:191'],
            'stampout_date' => ['required', 'date_format:Y-m-d'], 'ticket_number' => ['required', 'string', 'max:100'],
            'gross_weight' => [...$weight, 'gt:0'], 'tare_weight' => [...$weight, 'lt:gross_weight']];
    }
}
