<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawUjDitahanBanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'su', 'user'], true);
    }

    public function rules(): array
    {
        return [
            'nominal' => ['required', 'integer', 'regex:/^[1-9][0-9]*$/D', 'min:1', 'max:9999999999999'],
            'keterangan' => ['required', 'string', 'max:1000'],
            'bank' => ['required', 'string', 'max:100'],
            'no_rekening' => ['required', 'string', 'max:100'],
            'nama_rekening' => ['required', 'string', 'max:255'],
            'bukti_pdf' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'request_token' => ['required', 'uuid'],
        ];
    }
}
