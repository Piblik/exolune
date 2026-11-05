<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Achievement extends Model
{
    protected $fillable = [
        'title',
        'description',
        'icon',
        'condition_type',
        'condition_value',
        'experience_reward',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'condition_value' => 'integer',
            'experience_reward' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }
}
