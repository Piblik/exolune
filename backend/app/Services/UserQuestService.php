<?php

namespace App\Services;

use App\Models\Quest;
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
            }

            return UserQuest::create([
                'user_id' => $userId,
                'quest_id' => $quest->id,
                'daily_date' => $dailyDate,
                'status' => 'active',
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

            $quest = Quest::findOrFail($userQuest->quest_id);

            if (
                $quest->is_daily &&
                $userQuest->daily_date?->toDateString() !== today()->toDateString()
            ) {
                throw ValidationException::withMessages([
                    'quest' => 'Термін виконання цього Daily Quest минув.',
                ]);
            }

            $this->progressService->award(
                userId: $userId,
                characterId: $userQuest->user->character->id,
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

            $userQuest->user->notify(new GameNotification(
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

            return $userQuest->fresh();
        });
    }

    public function assignDailyQuests(int $userId): void
    {
        DB::transaction(function () use ($userId) {
            \App\Models\User::query()
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
                ->whereNotIn('id', $assignedIds)
                ->inRandomOrder()
                ->limit($remaining)
                ->get();

            foreach ($dailyQuests as $quest) {
                UserQuest::create([
                    'user_id' => $userId,
                    'quest_id' => $quest->id,
                    'daily_date' => today()->toDateString(),
                    'status' => 'active',
                ]);
            }
        });
    }

    public function expireOldDailyQuests(int $userId): void
    {
        UserQuest::query()
            ->where('user_id', $userId)
            ->whereNotNull('daily_date')
            ->whereDate('daily_date', '<', today())
            ->where('status', 'active')
            ->update([
                'status' => 'rejected',
            ]);
    }
}
