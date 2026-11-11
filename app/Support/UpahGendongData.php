<?php

namespace App\Support;

use App\Models\UpahGendong;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

/**
 * Satu aturan isi data Upah Gendong untuk menu Database admin (web) dan
 * pengaturan akun vendor (mobile): validasi dan normalisasi yang sama.
 */
class UpahGendongData
{
    /** @return array<string, mixed> */
    public static function rules(?UpahGendong $existing = null, bool $withVehicle = false): array
    {
        $rules = [
            'nominal' => ['required', 'regex:/^\d{1,3}(\.\d{3})+$|^\d{1,12}$/'],
            'tonase_min' => ['required', 'numeric', 'min:0', 'max:1000'],
            'nama_driver' => ['required', 'string', 'max:255'],
            'tanggal_masuk_driver' => ['required', 'date', 'before_or_equal:today'],
            'nama_pengurus' => ['required', 'string', 'max:255'],
            'tanggal_masuk_pengurus' => ['required', 'date', 'before_or_equal:today'],
            'bank' => ['required', 'string', 'max:100'],
            'nama_rek' => ['required', 'string', 'max:255'],
            'no_rek' => ['required', 'string', 'max:50'],
        ];
        if ($withVehicle) {
            $rules['vehicle_id'] = ['required', 'exists:vehicles,id',
                Rule::unique('upah_gendongs', 'vehicle_id')->ignore($existing?->id)];
        }

        return $rules;
    }

    /** @return array<string, string> */
    public static function messages(): array
    {
        return [
            'nominal.regex' => 'Nominal upah gendong harus berupa angka.',
            'vehicle_id.unique' => 'Nolam ini sudah memiliki data upah gendong.',
            'tanggal_masuk_driver.before_or_equal' => 'Tanggal masuk driver tidak boleh melebihi hari ini.',
            'tanggal_masuk_pengurus.before_or_equal' => 'Tanggal masuk pengurus tidak boleh melebihi hari ini.',
        ];
    }

    /**
     * Nominal "20.000" menjadi 20000, tanggal (d-m-Y atau Y-m-d) menjadi Y-m-d,
     * teks dirapikan.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalize(array $data): array
    {
        $data['nominal'] = (int) str_replace('.', '', (string) $data['nominal']);
        foreach (['tanggal_masuk_driver', 'tanggal_masuk_pengurus'] as $date) {
            $data[$date] = Carbon::parse($data[$date])->toDateString();
        }
        foreach (['nama_driver', 'nama_pengurus', 'bank', 'nama_rek', 'no_rek'] as $text) {
            $data[$text] = trim((string) $data[$text]);
        }

        return $data;
    }
}
