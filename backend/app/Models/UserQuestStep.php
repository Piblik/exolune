<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserQuestStep extends Model
{
    protected $fillable = [
        'user_quest_id',
        'quest_step_id',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function userQuest(): BelongsTo
    {
        return $this->belongsTo(UserQuest::class);
    }

    public function questStep(): BelongsTo
    {
        return $this->belongsTo(QuestStep::class);
    }
}
