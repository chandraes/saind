<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLoadNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('review', $this->route('note')) ?? false;
    }

    public function rules(): array
    {
        $weight = StoreLoadNoteDraftRequest::weightRules();

        return ['revision' => ['required', 'integer', 'min:0'], 'reason' => ['required', 'string', 'max:1000'],
            'vehicle_id' => ['required', 'integer', Rule::exists('vehicles', 'id')->where('vendor_id', $this->route('note')->vendor_id)],
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->where('status', 1)],
            'rute_id' => ['required', 'integer', 'exists:rutes,id'],
            'ticket_number' => ['required', 'string', 'max:100'], 'loaded_on' => ['required', 'date_format:Y-m-d'],
            'gross_weight' => [...$weight, 'gt:0'], 'tare_weight' => [...$weight, 'lt:gross_weight'],
            'notes' => ['nullable', 'string', 'max:1000']];
    }
}
