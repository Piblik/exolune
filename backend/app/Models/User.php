<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ExperienceLog;
use App\Models\UserAchievement;
use App\Models\UserStreak;
use Illuminate\Auth\Notifications\ResetPassword;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function character(): HasOne
    {
        return $this->hasOne(Character::class);
    }

    public function userQuests(): HasMany
    {
        return $this->hasMany(UserQuest::class);
    }

    public function experienceLogs(): HasMany
    {
        return $this->hasMany(ExperienceLog::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function streak(): HasOne
    {
        return $this->hasOne(UserStreak::class);
    }

    public function createdQuests(): HasMany
    {
        return $this->hasMany(Quest::class, 'created_by');
    }

    public function sendPasswordResetNotification($token): void
    {
        $url = config('app.frontend_url') . '/reset-password?token=' . $token
            . '&email=' . urlencode($this->email);

        $this->notify(new class($url) extends ResetPassword {
            public function __construct(private string $resetUrl)
            {
                parent::__construct('');
            }

            protected function resetUrl($notifiable): string
            {
                return $this->resetUrl;
            }
        });
    }
}
