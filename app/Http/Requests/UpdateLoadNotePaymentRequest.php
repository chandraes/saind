<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLoadNotePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('adjustPayment', $this->route('note')) ?? false;
    }

    public function rules(): array
    {
        return ['revision' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'max:1000'], 'use_system_amount' => ['required', 'boolean'],
            'transfer_amount' => ['required_if:use_system_amount,0', 'nullable', 'integer', 'min:0', 'max:999999999999']];
    }
}
