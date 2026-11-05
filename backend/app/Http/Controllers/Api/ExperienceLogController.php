<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExperienceLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $logs = $request->user()
            ->experienceLogs()
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}
