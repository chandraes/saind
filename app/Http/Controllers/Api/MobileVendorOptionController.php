<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Pilihan vendor untuk filter role operasional di aplikasi mobile. */
class MobileVendorOptionController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        abort_unless($request->user()->isOperasional(), 403);

        return response()->json(['data' => Vendor::query()->orderBy('nama')->get(['id', 'nama', 'nickname'])
            ->map(fn (Vendor $vendor): array => ['id' => $vendor->id, 'name' => $vendor->nama, 'nickname' => $vendor->nickname ?: null])->values()]);
    }
}
