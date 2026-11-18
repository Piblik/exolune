<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\User;
use App\Models\UserQuest;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class QuestCreationService
{
    public function __construct(
        private UserQuestService $userQuestService
    ) {}

    /**
     * Створення особистого квесту з автоматичними нагородами.
     */
    public function create(User $user, array $data): UserQuest
    {
        return DB::transaction(function () use ($user, $data) {
            $stepsCount = count($data['steps']);

            if ($stepsCount < 2 || $stepsCount > 8) {
                throw new InvalidArgumentException(
                    'Квест повинен містити від 2 до 8 етапів.'
                );
            }

            $rewards = $this->calculateRewards(
                $data['difficulty'],
                $data['category'],
                $stepsCount
            );

            $quest = Quest::create([
                'created_by' => $user->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'category' => $data['category'],
                'difficulty' => $data['difficulty'],

                'experience_reward' => $rewards['experience'],
                'strength_reward' => $rewards['strength'],
                'intelligence_reward' => $rewards['intelligence'],
                'discipline_reward' => $rewards['discipline'],

                'is_active' => true,
                'is_daily' => false,
            ]);

            foreach ($data['steps'] as $index => $step) {
                $quest->steps()->create([
                    'title' => $step['title'],
                    'description' => $step['description'] ?? null,
                    'sort_order' => $index + 1,
                ]);
            }

            // Автоматично додаємо створений квест до активних.
            return $this->userQuestService->accept(
                $user->id,
                $quest->id
            );
        });
    }

    /**
     * Автоматичний розрахунок нагород.
     */
    private function calculateRewards(
        string $difficulty,
        string $category,
        int $stepsCount
    ): array {
        $baseExperience = [
            'easy' => 30,
            'medium' => 50,
            'hard' => 80,
            'epic' => 120,
        ];

        $baseStatReward = [
            'easy' => 2,
            'medium' => 4,
            'hard' => 7,
            'epic' => 10,
        ];

        $experience = min(
            200,
            $baseExperience[$difficulty]
            + (($stepsCount - 2) * 10)
        );

        $statReward = min(
            15,
            $baseStatReward[$difficulty]
            + intdiv($stepsCount - 2, 2)
        );

        $strength = 0;
        $intelligence = 0;
        $discipline = 0;

        $normalizedCategory = mb_strtolower($category);

        if (
            str_contains($normalizedCategory, 'спорт') ||
            str_contains($normalizedCategory, 'трен') ||
            str_contains($normalizedCategory, 'фітнес') ||
            str_contains($normalizedCategory, 'sport') ||
            str_contains($normalizedCategory, 'fitness')
        ) {
            $strength = $statReward;
        } elseif (
            str_contains($normalizedCategory, 'навч') ||
            str_contains($normalizedCategory, 'освіт') ||
            str_contains($normalizedCategory, 'читан') ||
            str_contains($normalizedCategory, 'study') ||
            str_contains($normalizedCategory, 'education')
        ) {
            $intelligence = $statReward;
        } else {
            $discipline = $statReward;
        }

        return [
            'experience' => $experience,
            'strength' => $strength,
            'intelligence' => $intelligence,
            'discipline' => $discipline,
        ];
    }
}
