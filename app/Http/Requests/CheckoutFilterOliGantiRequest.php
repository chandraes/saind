<?php

namespace App\Http\Requests;

use App\Services\MaintenancePaymentService;
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
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'items' => ['bail', 'required', 'array', 'min:1', function (string $attribute, mixed $value, \Closure $fail): void {
                foreach (array_keys($value) as $id) {
                    if (! ctype_digit((string) $id) || (int) $id < 1) {
                        $fail('Item keranjang tidak valid. Muat ulang dan periksa kembali.');
                        break;
                    }
                }
            }],
            'items.*' => ['required', 'array:tanggal_ganti'],
            'items.*.tanggal_ganti' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'pembayaran' => ['required', Rule::in(array_keys(MaintenancePaymentService::optionsFor($this->user()?->role, 'uj_ditahan')))],
            'total_nominal' => ['exclude_unless:pembayaran,kas_besar,uj_ditahan', 'bail', 'required', 'regex:/\A[1-9][0-9]*\z/', 'integer', 'min:1', 'max:9999999999999'],
            'nama_bank' => ['exclude_unless:pembayaran,kas_besar,uj_ditahan', 'required', 'string', 'max:50'],
            'nomor_rekening' => ['exclude_unless:pembayaran,kas_besar,uj_ditahan', 'required', 'string', 'max:50'],
            'nama_rekening' => ['exclude_unless:pembayaran,kas_besar,uj_ditahan', 'required', 'string', 'max:100'],
        ];
    }
}
