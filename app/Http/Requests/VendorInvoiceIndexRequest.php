<?php

namespace App\Http\Requests;

use App\Services\VendorOperationalBilling;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorInvoiceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Operasional membaca invoice seluruh vendor (disaring lewat vendor_id).
        return (bool) $this->user()?->usesVendorMenus();
    }

    public function rules(): array
    {
        $rules = ['status' => ['sometimes', Rule::in(['unpaid', 'paid', 'all'])],
            'search' => ['nullable', 'string', 'max:100'], 'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'], 'page' => ['sometimes', 'integer', 'min:1'],
            'sort' => ['nullable', Rule::in(array_keys(VendorOperationalBilling::INVOICE_SORTS))], 'dir' => ['nullable', Rule::in(['asc', 'desc'])]];
        if ($this->filled('start_date')) {
            $rules['end_date'][] = 'after_or_equal:start_date';
        }

        return $rules;
    }
}
