<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Character extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'avatar',
        'level',
        'experience',
        'strength',
        'intelligence',
        'discipline',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'experience' => 'integer',
            'strength' => 'integer',
            'intelligence' => 'integer',
            'discipline' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function experienceLogs(): HasMany
    {
        return $this->hasMany(ExperienceLog::class);
    }
}
