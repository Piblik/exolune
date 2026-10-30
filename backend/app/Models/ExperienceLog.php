<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExperienceLog extends Model
{
    protected $fillable = [
        'user_id',
        'character_id',
        'source',
        'discipline',
        'experience',
        'strength_change',
        'intelligence_change',
        'discipline_change',
    ];

    protected function casts(): array
    {
        return [
            'experience' => 'integer',
            'strength_change' => 'integer',
            'intelligence_change' => 'integer',
            'discipline_change' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
