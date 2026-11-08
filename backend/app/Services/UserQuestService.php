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
        private AchievementService $achievementService
    ) {
    }

    public function accept(int $userId, int $questId): UserQuest
    {
        return DB::transaction(function () use ($userId, $questId) {
            $quest = Quest::query()
                ->where('is_active', true)
                ->findOrFail($questId);

            return UserQuest::create([
                'user_id' => $userId,
                'quest_id' => $quest->id,
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
}
