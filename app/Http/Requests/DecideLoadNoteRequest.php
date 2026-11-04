<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideLoadNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('review', $this->route('note')) ?? false;
    }

    public function rules(): array
    {
        return ['revision' => ['required', 'integer', 'min:0'], 'reason' => ['required', 'string', 'max:1000'],
            'action' => ['required', Rule::in(['reject', 'cancel'])]];
    }
}
