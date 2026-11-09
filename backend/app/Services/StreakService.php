<?php


namespace App\Services;

use App\Models\UserStreak;
use App\Notifications\GameNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StreakService
{
    public function update(int $userId): UserStreak
    {
        return DB::transaction(function () use ($userId) {
            $today = today();

            $streak = UserStreak::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$streak) {
                $streak = UserStreak::create([
                    'user_id' => $userId,
                    'current_streak' => 1,
                    'longest_streak' => 1,
                    'last_activity_date' => $today,
                ]);

                return $streak;
            }

            $lastActivity = $streak->last_activity_date;

            if ($lastActivity && $lastActivity->isSameDay($today)) {
                return $streak;
            }

            if ($lastActivity && $lastActivity->isSameDay($today->copy()->subDay())) {
                $streak->current_streak++;
            } else {
                $streak->current_streak = 1;
            }

            $newRecord = $streak->current_streak > $streak->longest_streak;

            if ($newRecord) {
                $streak->longest_streak = $streak->current_streak;
            }

            $streak->last_activity_date = $today;
            $streak->save();

            if ($newRecord) {
                $streak->user->notify(new GameNotification(
                    type: 'streak_record',
                    title: 'Новий рекорд серії!',
                    message: "Вітаємо! Твоя серія активності тепер становить {$streak->longest_streak} днів!",
                    icon: 'flame',
                    data: [
                        'current_streak' => $streak->current_streak,
                        'longest_streak' => $streak->longest_streak,
                    ]
                ));
            }

            return $streak;
        });
    }

    public function get(int $userId): UserStreak
    {
        $streak = UserStreak::firstOrCreate(
            ['user_id' => $userId],
            [
                'current_streak' => 0,
                'longest_streak' => 0,
                'last_activity_date' => null,
            ]
        );

        if (
            $streak->last_activity_date &&
            !$streak->last_activity_date->isSameDay(today()) &&
            !$streak->last_activity_date->isSameDay(today()->subDay())
        ) {
            $streak->update([
                'current_streak' => 0,
            ]);

            $streak->refresh();
        }

        return $streak;
    }
}
