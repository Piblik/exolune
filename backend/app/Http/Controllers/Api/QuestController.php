<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quest;
use App\Services\UserQuestService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\QuestCreationService;

class QuestController extends Controller
{
    public function __construct(
        private UserQuestService $userQuestService,
        private QuestCreationService $questCreationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;

        $quests = Quest::query()
            ->where('is_active', true)
            ->where(function ($query) use ($userId) {
                $query->whereNull('created_by');

                if ($userId) {
                    $query->orWhere('created_by', $userId);
                }
            })
            ->with('steps')
            ->orderBy('difficulty')
            ->get();

        return response()->json($quests);
    }

    public function accept(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quest_id' => ['required', 'exists:quests,id'],
        ]);

        $userQuest = $this->userQuestService->accept(
            $request->user()->id,
            $validated['quest_id']
        );

        return response()->json([
            'message' => 'Квест успішно прийнято!',
            'user_quest' => $userQuest,
        ], 201);
    }

    public function completeStep(
        Request $request,
        int $userQuestId,
        int $stepId
    ): JsonResponse {
        $userQuest = $this->userQuestService->completeStep(
            $request->user()->id,
            $userQuestId,
            $stepId
        );

        return response()->json([
            'message' => 'Етап квесту виконано!',
            'user_quest' => $userQuest,
        ]);
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        $userQuest = $this->userQuestService->complete(
            $request->user()->id,
            $id
        );

        return response()->json([
            'message' => 'Квест успішно завершено!',
            'user_quest' => $userQuest->load('quest.steps'),
        ]);
    }

    public function myQuests(Request $request): JsonResponse
    {
        $this->userQuestService->assignDailyQuests(
            $request->user()->id
        );

        $this->userQuestService->assignWeeklyQuests(
            $request->user()->id
        );

        $userQuests = $request->user()
            ->userQuests()
            ->with([
                'quest.steps',
                'steps.questStep',
            ])
            ->latest()
            ->get();

        return response()->json($userQuests);
    }

    /**
     * Створення особистого квесту.
     */
    public function createCustom(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:1000'],
            'category' => ['required', 'string', 'max:50'],
            'difficulty' => [
                'required',
                'in:easy,medium,hard,epic',
            ],

            'steps' => ['required', 'array', 'min:2', 'max:8'],
            'steps.*.title' => ['required', 'string', 'max:100'],
            'steps.*.description' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        $userQuest = $this->questCreationService->create(
            $request->user(),
            $validated
        );

        return response()->json([
            'message' => 'Особистий квест успішно створено!',
            'user_quest' => $userQuest,
        ], 201);
    }
}
