<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\UserQuest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserQuestService
{
    public function __construct(
        private CharacterProgressService $progressService
    ){
    }

    public function accept(int $userId, int $questId): UserQuest{
        return DB::transaction(function () use ($userId, $questId) {
            $quest = Quest::quest()
                ->where('is_active', true)
                ->findOrFail($questId);

            return UserQuest::create([
                'user_id' => $userId,
                'quest_id' => $quest->id,
                'status' => 'active',
            ]);
        });
    }

    public function complete(
        int $userId,
        int $userQuestId
    ): UserQuest{
        return DB::transaction(function () use (
            $userId,
            $userQuestId
        ){
            $userQuest = UserQuest::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->findOrFail($userQuestId);

            if ($userQuest->status !== 'active'){
                throw ValidationException::withMessages([
                    'quest' => 'Це завдання вже не можна завершити.',
                ]);
            }

            $quest = Quest::findOrFail($userQuest->quest_id);

            $character = $this->progressService->award(
                userId: $userId,
                characterId: $userQuest->user->character->id,
                experience: $quest->experience_reward,
                statRewards:[
                    'strength' => $quest->strength_reward,
                    'intelligence' => $quest->intelligence_reward,
                    'discipline' => $quest->discipline_reward,
                ],
                source: 'quest',
                description: $quest->title
            );
            return $userQuest->fresh();
        });
    }

}
