<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\User;
use App\Models\UserQuest;
use App\Notifications\GameNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserQuestService
{
    public function __construct(
        private CharacterProgressService $progressService,
        private AchievementService $achievementService,
        private StreakService $streakService
    ) {
    }

    public function accept(int $userId, int $questId): UserQuest
    {
        return DB::transaction(function () use ($userId, $questId) {
            $quest = Quest::query()
                ->where('is_active', true)
                ->findOrFail($questId);

            if (
                $quest->created_by !== null &&
                $quest->created_by !== $userId
            ) {
                throw ValidationException::withMessages([
                    'quest' => 'Цей особистий квест тобі недоступний.',
                ]);
            }

            $dailyDate = $quest->is_daily
                ? today()->toDateString()
                : null;

            if ($quest->is_daily) {
                $alreadyAccepted = UserQuest::query()
                    ->where('user_id', $userId)
                    ->where('quest_id', $questId)
                    ->whereDate('daily_date', $dailyDate)
                    ->exists();

                if ($alreadyAccepted) {
                    throw ValidationException::withMessages([
                        'quest' => 'Ти вже приймав цей Daily Quest сьогодні.',
                    ]);
                }
            } else {
                $alreadyActive = UserQuest::query()
                    ->where('user_id', $userId)
                    ->where('quest_id', $questId)
                    ->where('status', 'active')
                    ->exists();

                if ($alreadyActive) {
                    throw ValidationException::withMessages([
                        'quest' => 'Цей квест уже активний.',
                    ]);
                }
            }

            return $this->createUserQuestWithSteps(
                $userId,
                $quest,
                $dailyDate
            );
        });
    }

    private function createUserQuestWithSteps(
        int $userId,
        Quest $quest,
        ?string $dailyDate
    ): UserQuest {
        $userQuest = UserQuest::create([
            'user_id' => $userId,
            'quest_id' => $quest->id,
            'daily_date' => $dailyDate,
            'status' => 'active',
        ]);

        $this->ensureStepProgress($userQuest);

        return $userQuest->fresh([
            'quest.steps',
            'steps.questStep',
        ]);
    }

    private function ensureStepProgress(UserQuest $userQuest): void
    {
        $quest = $userQuest->quest()->with('steps')->firstOrFail();

        foreach ($quest->steps as $step) {
            $userQuest->steps()->firstOrCreate(
                ['quest_step_id' => $step->id],
                ['completed_at' => null]
            );
        }
    }

    public function completeStep(
        int $userId,
        int $userQuestId,
        int $questStepId
    ): UserQuest {
        return DB::transaction(function () use (
            $userId,
            $userQuestId,
            $questStepId
        ) {
            $userQuest = UserQuest::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->findOrFail($userQuestId);

            if ($userQuest->status !== 'active') {
                throw ValidationException::withMessages([
                    'quest' => 'Цей квест більше не активний.',
                ]);
            }

            $quest = $userQuest->quest;

            if ($quest->is_daily) {
                if ($userQuest->daily_date?->toDateString() !== today()->toDateString()) {
                    throw ValidationException::withMessages([
                        'quest' => 'Термін виконання цього Daily Quest минув.',
                    ]);
                }
            }

            if ($quest->is_weekly) {
                $weekStart = today()->startOfWeek()->toDateString();
                $weekEnd = today()->endOfWeek()->toDateString();

                if (
                    $userQuest->daily_date?->toDateString() < $weekStart ||
                    $userQuest->daily_date?->toDateString() > $weekEnd
                ) {
                    throw ValidationException::withMessages([
                        'quest' => 'Термін виконання цього Weekly Quest минув.',
                    ]);
                }
            }

            $this->ensureStepProgress($userQuest);

            $questStep = $quest->steps()
                ->whereKey($questStepId)
                ->first();

            if (!$questStep) {
                throw ValidationException::withMessages([
                    'step' => 'Цей етап не належить до вибраного квесту.',
                ]);
            }

            $userStep = $userQuest->steps()
                ->where('quest_step_id', $questStepId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($userStep->completed_at === null) {
                $userStep->update([
                    'completed_at' => now(),
                ]);
            }

            return $userQuest->fresh([
                'quest.steps',
                'steps.questStep',
            ]);
        });
    }

    public function complete(int $userId, int $userQuestId): UserQuest
    {
        return DB::transaction(function () use ($userId, $userQuestId) {
            $userQuest = UserQuest::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->findOrFail($userQuestId);

            if ($userQuest->status !== 'active') {
                throw ValidationException::withMessages([
                    'quest' => 'Це завдання вже не можна завершити.',
                ]);
            }

            $quest = $userQuest->quest;

            if ($quest->is_daily) {
                if ($userQuest->daily_date?->toDateString() !== today()->toDateString()) {
                    throw ValidationException::withMessages([
                        'quest' => 'Термін виконання цього Daily Quest минув.',
                    ]);
                }
            }

            if ($quest->is_weekly) {
                $weekStart = today()->startOfWeek()->toDateString();
                $weekEnd = today()->endOfWeek()->toDateString();

                if (
                    $userQuest->daily_date?->toDateString() < $weekStart ||
                    $userQuest->daily_date?->toDateString() > $weekEnd
                ) {
                    throw ValidationException::withMessages([
                        'quest' => 'Термін виконання цього Weekly Quest минув.',
                    ]);
                }
            }

            $this->ensureStepProgress($userQuest);

            $totalSteps = $quest->steps()->count();

            $completedSteps = $userQuest->steps()
                ->whereNotNull('completed_at')
                ->count();

            if ($totalSteps > 0 && $completedSteps !== $totalSteps) {
                throw ValidationException::withMessages([
                    'quest' => 'Спочатку виконай усі етапи квесту.',
                ]);
            }

            $user = User::with('character')->findOrFail($userId);

            $this->progressService->award(
                userId: $userId,
                characterId: $user->character->id,
                experience: $quest->experience_reward,
                statRewards: [
                    'strength' => $quest->strength_reward,
                    'intelligence' => $quest->intelligence_reward,
                    'discipline' => $quest->discipline_reward,
                ],
                source: 'quest',
                description: $quest->title
            );

            $userQuest->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $this->achievementService->checkAndUnlock($userId);

            $this->streakService->update($userId);

            $user->notify(new GameNotification(
                type: 'quest_completed',
                title: 'Квест виконано!',
                message: "Ти успішно завершив квест «{$quest->title}» та отримав {$quest->experience_reward} XP.",
                icon: 'trophy',
                data: [
                    'quest_id' => $quest->id,
                    'user_quest_id' => $userQuest->id,
                    'experience' => $quest->experience_reward,
                    'stat_rewards' => [
                        'strength' => $quest->strength_reward,
                        'intelligence' => $quest->intelligence_reward,
                        'discipline' => $quest->discipline_reward,
                    ],
                ]
            ));

            return $userQuest->fresh([
                'quest.steps',
                'steps.questStep',
            ]);
        });
    }

    public function assignDailyQuests(int $userId): void
    {
        DB::transaction(function () use ($userId) {
            User::query()
                ->whereKey($userId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->expireOldDailyQuests($userId);

            $todayQuests = UserQuest::query()
                ->where('user_id', $userId)
                ->whereDate('daily_date', today())
                ->get();

            $assignedCount = $todayQuests->count();

            if ($assignedCount >= 3) {
                return;
            }

            $remaining = 3 - $assignedCount;

            $assignedIds = $todayQuests->pluck('quest_id')->toArray();

            $dailyQuests = Quest::query()
                ->where('is_daily', true)
                ->where('is_active', true)
                ->whereNull('created_by')
                ->whereNotIn('id', $assignedIds)
                ->inRandomOrder()
                ->limit($remaining)
                ->get();

            foreach ($dailyQuests as $quest) {
                $this->createUserQuestWithSteps(
                    $userId,
                    $quest,
                    today()->toDateString()
                );
            }
        });
    }


    public function assignWeeklyQuests(int $userId): void
    {
        DB::transaction(function () use ($userId) {
            User::query()
                ->whereKey($userId)
                ->lockForUpdate()
                ->firstOrFail();

            $weekStart = today()->startOfWeek()->toDateString();
            $weekEnd = today()->endOfWeek()->toDateString();

            // Завершуємо прострочені Weekly Quests
            UserQuest::query()
                ->where('user_id', $userId)
                ->whereHas('quest', function ($query) {
                    $query->where('is_weekly', true);
                })
                ->where('status', 'active')
                ->whereDate('daily_date', '<', $weekStart)
                ->update(['status' => 'rejected']);

            // Перевіряємо вже призначені квести цього тижня
            $weekQuests = UserQuest::query()
                ->where('user_id', $userId)
                ->whereBetween('daily_date', [$weekStart, $weekEnd])
                ->whereHas('quest', function ($query) {
                    $query->where('is_weekly', true);
                })
                ->get();

            $assignedCount = $weekQuests->count();

            if ($assignedCount >= 3) {
                return;
            }

            $remaining = 3 - $assignedCount;
            $assignedIds = $weekQuests->pluck('quest_id')->toArray();

            $weeklyQuests = Quest::query()
                ->where('is_weekly', true)
                ->where('is_active', true)
                ->where('is_daily', false)
                ->whereNull('created_by')
                ->whereNotIn('id', $assignedIds)
                ->inRandomOrder()
                ->limit($remaining)
                ->get();

            foreach ($weeklyQuests as $quest) {
                $this->createUserQuestWithSteps(
                    $userId,
                    $quest,
                    $weekStart
                );
            }
        });
    }


    public function expireOldDailyQuests(int $userId): void
    {
        UserQuest::query()
            ->where('user_id', $userId)
            ->whereHas('quest', function ($query) {
                $query->where('is_daily', true);
            })
            ->whereNotNull('daily_date')
            ->whereDate('daily_date', '<', today())
            ->where('status', 'active')
            ->update([
                'status' => 'rejected',
            ]);
    }
}
