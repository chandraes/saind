<?php

namespace App\Http\Controllers\Api;

use App\Services\MobileWorkspace;
use Illuminate\Http\JsonResponse;

class WorkspaceController extends BaseController
{
    public function show(MobileWorkspace $workspace): JsonResponse
    {
        return $this->sendResponse($workspace->identity(), 'Identitas ruang kerja.');
    }
}
