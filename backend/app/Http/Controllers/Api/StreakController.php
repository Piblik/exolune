<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StreakService;
use Illuminate\Http\Request;

class StreakController extends Controller
{
    public function __construct(
        private StreakService $streakService
    ) {
    }

    public function index(Request $request)
    {
        $streak = $this->streakService->get($request->user()->id);

        return response()->json([
            'data' => [
                'current_streak' => $streak->current_streak,
                'longest_streak' => $streak->longest_streak,
                'last_activity_date' => $streak->last_activity_date?->format('Y-m-d'),
            ],
        ]);
    }
}
