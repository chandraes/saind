<?php

namespace App\Http\Requests;

use App\Models\KategoriFilterOliMesin;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKategoriFilterOliMesinRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'su';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255', Rule::unique(KategoriFilterOliMesin::class, 'nama')],
            'limit_ritase' => ['required', 'integer', 'min:1', 'max:4294967295'],
        ];
    }
}
