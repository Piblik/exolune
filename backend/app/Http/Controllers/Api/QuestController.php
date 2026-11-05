<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quest;
use App\Services\UserQuestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestController extends Controller
{
    public function index(): JsonResponse
    {
        $quests = Quest::query()
            ->where('is_active', true)
            ->orderBy('difficulty')
            ->get();

        return response()->json([
            'success' => true,
            'quests' => $quests,
        ]);
    }

    public function accept(
        Request $request,
        UserQuestService $userQuestService
    ): JsonResponse {
        $data = $request->validate([
            'quest_id' => ['required', 'integer', 'exists:quests,id'],
        ]);

        $userQuest = $userQuestService->accept(
            $request->user()->id,
            $data['quest_id']
        );

        return response()->json([
            'success' => true,
            'message' => 'Завдання прийнято!',
            'data' => $userQuest,
        ], 201);
    }

    public function complete(
        Request $request,
        UserQuestService $userQuestService,
        int $id
    ): JsonResponse {
        $userQuest = $userQuestService->complete(
            $request->user()->id,
            $id
        );

        return response()->json([
            'success' => true,
            'message' => 'Завдання виконано!',
            'data' => $userQuest,
        ]);
    }

    public function myQuests(Request $request): JsonResponse
    {
        $userQuests = $request->user()
            ->userQuests()
            ->with('quest')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $userQuests,
        ]);
    }
}
