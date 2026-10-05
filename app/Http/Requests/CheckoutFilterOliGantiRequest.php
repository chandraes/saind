<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutFilterOliGantiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['su', 'admin', 'user'], true);
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array:tanggal_ganti'],
            'items.*.tanggal_ganti' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'pembayaran' => ['required', Rule::in(config('maintenance.filter_oli.kas_besar_enabled') ? ['dibayar_sendiri', 'kas_besar'] : ['dibayar_sendiri'])],
            'total_nominal' => ['exclude_unless:pembayaran,kas_besar', 'required', 'integer', 'min:1', 'max:9999999999999'],
            'nama_bank' => ['exclude_unless:pembayaran,kas_besar', 'required', 'string', 'max:50'],
            'nomor_rekening' => ['exclude_unless:pembayaran,kas_besar', 'required', 'string', 'max:50'],
            'nama_rekening' => ['exclude_unless:pembayaran,kas_besar', 'required', 'string', 'max:100'],
        ];
    }
}
