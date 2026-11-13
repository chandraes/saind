<?php

namespace App\Http\Requests;

use App\Models\LoadNoteDraft;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLoadNoteDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', LoadNoteDraft::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $weight = self::weightRules();

        return ['request_id' => ['required', 'uuid'],
            'vehicle_id' => ['required', 'integer', Rule::exists('vehicles', 'id')->where('vendor_id', $this->user()->effectiveVendorId())->where(fn ($query) => $query->when($this->user()->isDriver(), fn ($query) => $query->where('driver_id', $this->user()->driver_id)))],
            'ticket_number' => ['required', 'string', 'max:100'], 'mine_name' => ['required', 'string', 'max:200'],
            'loaded_on' => ['required', 'date_format:Y-m-d'], 'driver_name' => ['nullable', 'string', 'max:120'],
            'gross_weight' => [...$weight, 'gt:0'], 'tare_weight' => [...$weight, 'lt:gross_weight'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'photo' => self::photoRules()];
    }

    /** @return list<string> */
    public static function photoRules(): array
    {
        return ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:max_width=8000,max_height=8000'];
    }

    /** @return list<string> */
    public static function weightRules(): array
    {
        return ['required', 'numeric', 'min:0', 'max:1000', 'regex:/^\d{1,4}(?:\.\d{1,3})?$/'];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['vehicle_id.exists' => 'Kendaraan tidak tersedia pada vendor Anda.',
            'photo.mimes' => 'Gunakan foto JPEG, PNG, atau WebP.', 'photo.max' => 'Ukuran foto maksimal 8 MB.',
            'tare_weight.lt' => 'Tare harus lebih kecil dari gross.',
            'gross_weight.regex' => 'Gross menggunakan maksimal tiga angka desimal dalam ton.',
            'tare_weight.regex' => 'Tare menggunakan maksimal tiga angka desimal dalam ton.'];
    }
}
