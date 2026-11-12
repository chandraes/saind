<?php

namespace App\Http\Controllers\Api;

use App\Models\Setting;
use App\Support\RevocationList;
use Illuminate\Http\JsonResponse;
use Throwable;

/** Cermin publik daftar pencabutan ruang kerja (ditandatangani DS Groups; diverifikasi aplikasi). */
class TrustController extends BaseController
{
    public function show(): JsonResponse
    {
        $stored = Setting::query()->where('key', 'mobile_trust_bundle')->value('value');
        try {
            $bundle = RevocationList::bundle(json_decode((string) $stored, true));
        } catch (Throwable) {
            return $this->sendError('Daftar keamanan belum tersedia.', [], 404);
        }

        return $this->sendResponse(['revocations' => $bundle['revocations'], 'timestamp' => $bundle['timestamp']], 'Daftar pencabutan ruang kerja.');
    }
}
