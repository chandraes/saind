<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreVendorUangJalanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'su'], true);
    }

    public function rules(): array
    {
        $money = ['bail', 'required', 'regex:/\A(?:0|[1-9][0-9]*)\z/', 'integer', 'min:0', 'max:9999999999999'];

        return [
            'vendor_id' => ['required', 'integer', 'exists:vendors,id', Rule::when($this->route('id') !== null, Rule::in([$this->route('id')]))],
            'rute_id' => ['required', 'array', 'min:1'],
            'rute_id.*' => ['required', 'integer', 'distinct', 'exists:rutes,id'],
            'uang_jalan' => ['required', 'array'], 'uang_jalan.*' => $money,
            'uj_ditahan' => ['required', 'array'], 'uj_ditahan.*' => $money,
            'uj_ditahan_ban' => ['required', 'array'], 'uj_ditahan_ban.*' => $money,
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $routeKeys = array_keys($this->input('rute_id'));
            foreach (['uang_jalan', 'uj_ditahan', 'uj_ditahan_ban'] as $field) {
                if (array_keys($this->input($field)) !== $routeKeys) {
                    $validator->errors()->add($field, 'Nominal setiap rute harus diisi lengkap dan sesuai urutan rute.');
                }
            }
        }];
    }
}
