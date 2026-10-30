<?php

namespace App\Http\Controllers;

use App\Services\MobileWorkspace;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MobileConnectionController extends Controller
{
    public function show(Request $request, MobileWorkspace $workspace): View
    {
        abort_unless(in_array($request->user()->role, config('mobile.roles'), true), 403);
        $identity = $workspace->identity();
        $baseUrl = rtrim((string) config('app.url'), '/');
        $url = parse_url($baseUrl);
        abort_unless(is_array($url) && ($url['scheme'] ?? null) === 'https'
            && ! empty($url['host']) && empty($url['user']) && empty($url['pass'])
            && empty($url['query']) && empty($url['fragment']) && empty($url['path']), 503,
            'Alamat HTTPS ruang kerja belum dikonfigurasi dengan benar.');
        $connection = ['type' => 'dsgroup-hauling.workspace', 'version' => 1,
            'base_url' => $baseUrl, 'workspace_id' => $identity['id']];

        return view('mobile.workspace', compact('identity', 'connection', 'baseUrl'));
    }
}
