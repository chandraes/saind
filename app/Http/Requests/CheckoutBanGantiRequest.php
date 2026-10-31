<?php

namespace App\Http\Requests;

use App\Services\MaintenancePaymentService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutBanGantiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'su', 'user'], true);
    }

    public function rules(): array
    {
        $methods = array_keys(MaintenancePaymentService::optionsFor($this->user()?->role, 'uj_ditahan_ban'));
        $paid = 'required_if:pembayaran,kas_besar,uj_ditahan_ban';

        return [
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'pembayaran' => ['required', Rule::in($methods)],
            'total_nominal' => [$paid, 'nullable', 'integer', 'regex:/^[1-9][0-9]*$/D', 'min:1', 'max:9999999999999'],
            'nama_bank' => [$paid, 'nullable', 'string', 'max:50'],
            'nomor_rekening' => [$paid, 'nullable', 'string', 'max:50'],
            'nama_rekening' => [$paid, 'nullable', 'string', 'max:100'],
        ];
    }
}
