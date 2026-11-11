<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DecideLoadNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('review', $this->route('note')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['reason' => trim((string) $this->input('reason'))]);
    }

    public function messages(): array
    {
        return ['reason.required' => 'Alasan void wajib diisi.'];
    }

    public function rules(): array
    {
        return ['revision' => ['required', 'integer', 'min:0'], 'reason' => ['required', 'string', 'max:1000']];
    }
}
