<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            [
                'title' => 'Перший крок',
                'description' => 'Виконай свій перший квест.',
                'icon' => '🎯',
                'condition_type' => 'quests_completed',
                'condition_value' => 1,
                'experience_reward' => 25,
            ],
            [
                'title' => 'Досвідчений шукач',
                'description' => 'Виконай 10 квестів.',
                'icon' => '🏆',
                'condition_type' => 'quests_completed',
                'condition_value' => 10,
                'experience_reward' => 100,
            ],
            [
                'title' => 'Новачок RPG',
                'description' => 'Досягни 5 рівня.',
                'icon' => '⭐',
                'condition_type' => 'level_reached',
                'condition_value' => 5,
                'experience_reward' => 50,
            ],
            [
                'title' => 'Титан сили',
                'description' => 'Розвивай силу до 50 пунктів.',
                'icon' => '⚔️',
                'condition_type' => 'strength_reached',
                'condition_value' => 50,
                'experience_reward' => 75,
            ],
            [
                'title' => 'Гострий розум',
                'description' => 'Розвивай інтелект до 50 пунктів.',
                'icon' => '🧠',
                'condition_type' => 'intelligence_reached',
                'condition_value' => 50,
                'experience_reward' => 75,
            ],
            [
                'title' => 'Незламна воля',
                'description' => 'Розвивай дисципліну до 50 пунктів.',
                'icon' => '🔥',
                'condition_type' => 'discipline_reached',
                'condition_value' => 50,
                'experience_reward' => 75,
            ],
        ];

        foreach ($achievements as $achievement) {
            Achievement::updateOrCreate(
                ['title' => $achievement['title']],
                $achievement
            );
        }
    }
}
