<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    public function index(): JsonResponse
    {
        $achievements = Achievement::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'achievements' => $achievements,
        ]);
    }

    public function myAchievements(Request $request): JsonResponse
    {
        $achievements = $request->user()
            ->achievements()
            ->with('achievement')
            ->latest('unlocked_at')
            ->get();

        return response()->json([
            'success' => true,
            'achievements' => $achievements,
        ]);
    }
}
