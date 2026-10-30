<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\MobileUserResource;
use App\Models\User;
use App\Services\MobileWorkspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        if ($user->role === 'vendor-operational') {
            abort_unless($user->vendor_id && $user->vendor()->exists(), 403, 'Akun belum terhubung dengan vendor.');
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

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse(null, 'Logout berhasil.');
    }
}
