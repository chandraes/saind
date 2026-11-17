<?php

namespace App\Services;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Ganti username/password akun sendiri, dipakai bersama aplikasi mobile (PATCH /api/v1/me) dan web (/akun).
 * Password saat ini selalu wajib; password baru opsional (kosong = hanya ganti username).
 */
class AccountCredentials
{
    /**
     * @param  Closure(User): void  $onPasswordChanged  pencabutan sesi lain, dijalankan di dalam transaksi bila password berubah
     */
    public function update(Request $request, Closure $onPasswordChanged): User
    {
        $actor = $request->user();
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($actor->id)],
            'current_password' => ['required', 'string', 'max:1024'],
            'password' => ['nullable', 'string', 'min:8', 'max:72', 'confirmed'],
        ], [
            'username.required' => 'Isi username.',
            'username.unique' => 'Username sudah digunakan.',
            'current_password.required' => 'Isi password saat ini.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        return DB::transaction(function () use ($actor, $data, $onPasswordChanged): User {
            $user = User::query()->lockForUpdate()->findOrFail($actor->id);
            if (! Hash::check($data['current_password'], $user->password)) {
                throw ValidationException::withMessages(['current_password' => 'Password saat ini tidak sesuai.']);
            }

            $user->username = $data['username'];
            if (isset($data['password'])) {
                if (strlen($data['password']) > 72) {
                    throw ValidationException::withMessages(['password' => 'Password baru maksimal 72 byte.']);
                }
                $user->password = Hash::make($data['password']);
                $onPasswordChanged($user);
            }
            $user->save();

            return $user;
        });
    }
}
