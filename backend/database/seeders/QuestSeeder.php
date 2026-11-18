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
                'steps' => [
                    [
                        'title' => 'Підготуйся до тренування',
                        'description' => 'Підготуй спортивний одяг та місце для тренування.',
                    ],
                    [
                        'title' => 'Виконай розминку',
                        'description' => 'Проведи 5–10 хвилин розминки.',
                    ],
                    [
                        'title' => 'Проведи силове тренування',
                        'description' => 'Тренуйся щонайменше 30 хвилин.',
                    ],
                ],
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
                'steps' => [
                    [
                        'title' => 'Обери тему',
                        'description' => 'Визнач тему або завдання, над яким працюватимеш.',
                    ],
                    [
                        'title' => 'Опрацюй матеріал',
                        'description' => 'Присвяти навчанню щонайменше одну годину.',
                    ],
                    [
                        'title' => 'Закріпи знання',
                        'description' => 'Виконай практичне завдання або попрацюй над проєктом ще одну годину.',
                    ],
                ],
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
                'steps' => [
                    [
                        'title' => 'Обери книгу',
                        'description' => 'Підготуй книгу, яку хочеш прочитати.',
                    ],
                    [
                        'title' => 'Розпочни читання',
                        'description' => 'Прочитай перші 10 сторінок.',
                    ],
                    [
                        'title' => 'Заверши читання',
                        'description' => 'Прочитай ще 10 сторінок або більше.',
                    ],
                ],
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
                'steps' => [
                    [
                        'title' => 'Склади план',
                        'description' => 'Визнач усі завдання, які необхідно виконати сьогодні.',
                    ],
                    [
                        'title' => 'Виконай основні завдання',
                        'description' => 'Виконай заплановані важливі справи.',
                    ],
                    [
                        'title' => 'Перевір результати',
                        'description' => 'Переконайся, що всі заплановані завдання виконані.',
                    ],
                ],
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
                'steps' => [
                    [
                        'title' => 'Знайди нову тему',
                        'description' => 'Обери тему, з якою раніше не працював.',
                    ],
                    [
                        'title' => 'Досліди матеріал',
                        'description' => 'Опрацюй навчальні матеріали та знайди корисні джерела.',
                    ],
                    [
                        'title' => 'Створи конспект',
                        'description' => 'Запиши основні поняття та висновки.',
                    ],
                    [
                        'title' => 'Перевір знання',
                        'description' => 'Спробуй пояснити вивчену тему власними словами.',
                    ],
                ],
            ],
        ];

        foreach ($quests as $questData) {
            $steps = $questData['steps'];

            unset($questData['steps']);

            $quest = Quest::updateOrCreate(
                ['title' => $questData['title']],
                array_merge($questData, [
                    'is_active' => true,
                ])
            );

            foreach ($steps as $index => $stepData) {
                $quest->steps()->updateOrCreate(
                    ['sort_order' => $index + 1],
                    [
                        'title' => $stepData['title'],
                        'description' => $stepData['description'],
                    ]
                );
            }
        }
    }
}
