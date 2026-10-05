<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFilterOliGantiCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['su', 'admin', 'user'], true);
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', Rule::exists('vehicles', 'id')->where(fn ($query) => $query->where('status', '!=', 'nonaktif')->where('pembatasan_filter_oli', true))],
            'kategori_filter_oli_mesin_id' => ['required', 'exists:kategori_filter_oli_mesins,id'],
            'merk' => ['required', 'string', 'max:100'],
            'kondisi' => ['required', 'integer', 'min:1', 'max:100'],
            'tanggal_ganti' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
        ];
    }
}
