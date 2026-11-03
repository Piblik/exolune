<?php

namespace App\Services;

use App\Models\Character;
use App\Models\ExperienceLog;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CharacterProgressService
{
    public function experienceForNextLevel(int $level): int
    {
        return (int) ceil(100 * pow($level, 1.5));
    }

    public function award(
        int $userId,
        int $characterId,
        int $experience,
        array $statRewards,
        string $source,
        string $description
    ): Character {
        if ($experience < 0) {
            throw new InvalidArgumentException(
                'Experience cannot be negative.'
            );
        }

        $strength = $statRewrds['strength'] ?? 0;
        $intelligence = $statRewrds['intelligence'] ?? 0;
        $discipline = $statRewrds['discipline'] ?? 0;

        if (
            $strength < 0 ||
            $intelligence < 0 ||
            $discipline < 0
        ){
            throw new InvalidArgumentException(
                'Stat rewards cannot be negative.'
            );
        }

        return DB::transaction(function() use (
            $userId,
            $characterId,
            $experience,
            $strength,
            $intelligence,
            $discipline,
            $source,
            $description
        ) {
            $character = Character::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->findOrFail($characterId);

            $character->experience += $experience;

            while (
                $character->experience >=
                $this->experienceForNextLevel($character->level)
            ){
                $requiredExperience = $this->experienceForNextLevel(
                    $character->level
                );

                $character->experience -= $requiredExperience;
                $character->level++;
            }

            $character->strength += $strength;
            $character->intelligence += $intelligence;
            $character->discipline += $discipline;

            $character->save();

            ExperienceLog::create([
                'user_id' => $userId,
                'character_id' => $character->id,
                'source' => $source,
                'description' => $description,
                'experience' => $experience,
                'strength_change' => $strength,
                'intelligence_change' => $intelligence,
                'discipline_change' => $discipline,
            ]);

            return $character;
        });
    }
}
