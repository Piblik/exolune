<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quest extends Model
{
    protected $fillable = [
        'created_by',
        'title',
        'description',
        'category',
        'difficulty',
        'experience_reward',
        'strength_reward',
        'intelligence_reward',
        'discipline_reward',
        'is_active',
        'is_daily',
        'is_weekly',
    ];

    protected function casts():array
    {
        return [
            'experience_reward' => 'integer',
            'strength_reward' => 'integer',
            'intelligence_reward' => 'integer',
            'discipline_reward' => 'integer',
            'is_active' => 'boolean',
            'is_daily' => 'boolean',
            'is_weekly' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function userQuests(): HasMany
    {
        return $this->hasMany(UserQuest::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(QuestStep::class)->orderBy('sort_order');
    }
}
