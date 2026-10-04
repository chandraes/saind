<?php

namespace App\Http\Requests;

use App\Models\KategoriFilterOliMesin;
use Illuminate\Validation\Rule;

class UpdateKategoriFilterOliMesinRequest extends StoreKategoriFilterOliMesinRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['su', 'admin'], true);
    }

    public function rules(): array
    {
        return array_replace(parent::rules(), [
            'nama' => ['required', 'string', 'max:255', Rule::unique(KategoriFilterOliMesin::class, 'nama')->ignore($this->route('kategori'))],
        ]);
    }
}
