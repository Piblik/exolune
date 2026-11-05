<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AchievementService
{
    public function __construct(
        private CharacterProgressService $progressService
    ) {
    }

    public function checkAndUnlock(int $userId): array
    {
        return DB::transaction(function () use ($userId) {
            $user = User::with('character')->findOrFail($userId);
            $character = $user->character;

            $completedQuests = $user->userQuests()
                ->where('status', 'completed')
                ->count();

            $achievements = Achievement::query()
                ->where('is_active', true)
                ->whereDoesntHave('userAchievements', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->get();

            $unlocked = [];

            foreach ($achievements as $achievement) {
                $currentValue = match ($achievement->condition_type) {
                    'quests_completed' => $completedQuests,
                    'level_reached' => $character->level,
                    'strength_reached' => $character->strength,
                    'intelligence_reached' => $character->intelligence,
                    'discipline_reached' => $character->discipline,
                    default => 0,
                };

                if ($currentValue < $achievement->condition_value) {
                    continue;
                }

                $userAchievement = UserAchievement::create([
                    'user_id' => $userId,
                    'achievement_id' => $achievement->id,
                    'unlocked_at' => now(),
                ]);

                if ($achievement->experience_reward > 0) {
                    $this->progressService->award(
                        userId: $userId,
                        characterId: $character->id,
                        experience: $achievement->experience_reward,
                        statRewards: [],
                        source: 'achievement',
                        description: $achievement->title
                    );
                }

                $unlocked[] = $userAchievement->load('achievement');
            }

            return $unlocked;
        });
    }
}
