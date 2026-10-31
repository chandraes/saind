<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKasBesarDepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['user', 'admin', 'su'], true);
    }

    public function rules(): array
    {
        return [
            'nominal_transaksi' => ['required', 'integer', 'regex:/^[1-9][0-9]*$/D', 'min:1', 'max:9999999999999'],
            'request_token' => ['required', 'uuid'],
        ];
    }
}
