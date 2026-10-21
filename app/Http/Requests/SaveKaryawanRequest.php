<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveKaryawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'su'], true);
    }

    protected function prepareForValidation(): void
    {
        $values = [];
        foreach (['gaji_pokok', 'tunjangan_jabatan', 'tunjangan_keluarga'] as $field) {
            $value = $this->input($field);
            $values[$field] = is_string($value) && preg_match('/^(?:[0-9]+|[0-9]{1,3}(?:\.[0-9]{3})+)$/', $value) ? str_replace('.', '', $value) : $value;
        }
        foreach (['apa_bpjs_tk', 'apa_bpjs_kesehatan'] as $field) {
            $values[$field] = $this->input($field) === 'on' ? 1 : $this->input($field, 0);
        }
        $this->merge($values);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $rules = [];
        foreach (['nama', 'nickname', 'nik', 'npwp', 'tempat_lahir', 'no_hp', 'no_wa', 'bank', 'no_rekening', 'nama_rekening'] as $field) {
            $rules[$field] = ['required', 'string', 'max:255'];
        }

        return array_merge($rules, [
            'jabatan_id' => ['required', 'integer', 'exists:jabatans,id'],
            'gaji_pokok' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'tunjangan_jabatan' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'tunjangan_keluarga' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'tanggal_lahir' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'mulai_bekerja' => ['required', 'date_format:Y-m-d', 'after_or_equal:tanggal_lahir'],
            'alamat' => ['required', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'apa_bpjs_tk' => ['required', 'boolean'],
            'apa_bpjs_kesehatan' => ['required', 'boolean'],
            'bpjs_tk' => ['required_if:apa_bpjs_tk,1', 'nullable', 'string', 'max:255'],
            'bpjs_kesehatan' => ['required_if:apa_bpjs_kesehatan,1', 'nullable', 'string', 'max:255'],
            'perjanjian_kerja' => [$this->route('karyawan')?->perjanjian_kerja ? 'nullable' : 'required', 'file', 'mimes:pdf', 'extensions:pdf', 'max:5120'],
            'foto_ktp' => [$this->isMethod('POST') ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:10000'],
            'foto_diri' => [$this->isMethod('POST') ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:10000'],
        ]);
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['perjanjian_kerja' => 'perjanjian kerja', 'jabatan_id' => 'jabatan', 'gaji_pokok' => 'gaji pokok', 'tunjangan_jabatan' => 'tunjangan jabatan', 'tunjangan_keluarga' => 'tunjangan keluarga', 'tanggal_lahir' => 'tanggal lahir', 'mulai_bekerja' => 'tanggal mulai bekerja', 'foto_ktp' => 'foto KTP', 'foto_diri' => 'foto diri', 'bpjs_tk' => 'nomor BPJS Ketenagakerjaan', 'bpjs_kesehatan' => 'nomor BPJS Kesehatan'];
    }
}
