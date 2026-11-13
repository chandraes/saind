<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\MobileUserResource;
use App\Models\User;
use App\Services\MobileWorkspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseController
{
    public function store(Request $request, MobileWorkspace $workspace): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
            'device_name' => ['required', 'string', 'max:100'],
        ]);
        $user = User::where('username', $data['username'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return $this->sendError('Username atau password tidak sesuai.', [], 401);
        }
        abort_unless(in_array($user->role, config('mobile.roles'), true), 403, 'Akun tidak diizinkan menggunakan API mobile.');
        if ($user->isVendorAccount()) {
            abort_unless($user->vendor_id && $user->vendor()->exists(), 403, 'Akun belum terhubung dengan vendor.');
        }
        if ($user->isDriver()) {
            abort_unless($user->activeDriverVehicle() !== null, 403, 'Akun driver tidak aktif atau belum memegang nolam aktif.');
        }
        $identity = $workspace->identity();
        $expiresAt = now()->addMinutes(max(1, (int) config('mobile.token_expiration_minutes')));
        $token = $user->createToken($data['device_name'], ['workspace:'.$identity['id']], $expiresAt);

        return $this->sendResponse([
            'token' => $token->plainTextToken, 'token_type' => 'Bearer', 'expires_at' => $expiresAt->toIso8601String(),
            'user' => new MobileUserResource($user), 'workspace' => $identity,
        ], 'Login berhasil.');
    }

    public function show(Request $request): JsonResponse
    {
        return $this->sendResponse(new MobileUserResource($request->user()), 'Informasi akun.');
    }

    public function update(Request $request): JsonResponse
    {
        $actor = $request->user();
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($actor->id)],
            'current_password' => ['required', 'string', 'max:1024'],
            'password' => ['nullable', 'string', 'min:8', 'max:72', 'confirmed'],
        ], [
            'username.unique' => 'Username sudah digunakan.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = DB::transaction(function () use ($actor, $data): User {
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
                $user->tokens()->where('id', '!=', $actor->currentAccessToken()->getKey())->delete();
            }
            $user->save();

            return $user;
        });

        return $this->sendResponse(new MobileUserResource($user), 'Akun diperbarui.');
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse(null, 'Logout berhasil.');
    }
}
