<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quest extends Model
{
    protected $fillable = [
        'title',
        'description',
        'category',
        'difficulty',
        'experience_reward',
        'strength_reward',
        'intelligence_reward',
        'discipline_reward',
        'is_active',
    ];

    protected function casts():array
    {
        return [
            'experience_reward' => 'integer',
            'strength_reward' => 'integer',
            'intelligence_reward' => 'integer',
            'discipline_reward' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function userQuests(): HasMany
    {
        return $this->hasMany(UserQuest::class);
    }
}
