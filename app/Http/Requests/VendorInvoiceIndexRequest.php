<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorInvoiceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isVendorAccount();
    }

    public function rules(): array
    {
        $rules = ['status' => ['sometimes', Rule::in(['unpaid', 'paid', 'all'])],
            'search' => ['nullable', 'string', 'max:100'], 'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'], 'page' => ['sometimes', 'integer', 'min:1']];
        if ($this->filled('start_date')) {
            $rules['end_date'][] = 'after_or_equal:start_date';
        }

        return $rules;
    }
}
