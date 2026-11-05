<?php

namespace Database\Seeders;

use App\Models\Quest;
use Illuminate\Database\Seeder;

class QuestSeeder extends Seeder
{
    public function run(): void
    {
        $quests = [
            [
                'title' => 'Ранкове тренування',
                'description' => 'Проведи щонайменше 30 хвилин за силовим тренуванням.',
                'category' => 'Спорт',
                'difficulty' => 'easy',
                'experience_reward' => 40,
                'strength_reward' => 8,
                'intelligence_reward' => 0,
                'discipline_reward' => 2,
            ],
            [
                'title' => 'Глибоке навчання',
                'description' => 'Присвяти 2 години навчанню або роботі над власним проєктом.',
                'category' => 'Навчання',
                'difficulty' => 'medium',
                'experience_reward' => 80,
                'strength_reward' => 0,
                'intelligence_reward' => 12,
                'discipline_reward' => 4,
            ],
            [
                'title' => 'Читацький ритуал',
                'description' => 'Прочитай щонайменше 20 сторінок книги.',
                'category' => 'Саморозвиток',
                'difficulty' => 'easy',
                'experience_reward' => 25,
                'strength_reward' => 0,
                'intelligence_reward' => 5,
                'discipline_reward' => 3,
            ],
            [
                'title' => 'Залізна дисципліна',
                'description' => 'Виконай усі заплановані на день завдання.',
                'category' => 'Дисципліна',
                'difficulty' => 'hard',
                'experience_reward' => 120,
                'strength_reward' => 0,
                'intelligence_reward' => 5,
                'discipline_reward' => 15,
            ],
            [
                'title' => 'Нові горизонти',
                'description' => 'Вивчи нову тему та законспектуй отримані знання.',
                'category' => 'Навчання',
                'difficulty' => 'epic',
                'experience_reward' => 200,
                'strength_reward' => 0,
                'intelligence_reward' => 20,
                'discipline_reward' => 10,
            ],
        ];

        foreach ($quests as $quest) {
            Quest::updateOrCreate(
                ['title' => $quest['title']],
                array_merge($quest, ['is_active' => true])
            );
        }
    }
}
