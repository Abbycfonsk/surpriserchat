<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\GeniusPointEvent;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasApiTokens, Notifiable;

   protected $fillable = [
    'name',
    'email',
    'password',
    'username',
    'bio',
    'phone',
    'location_city',
    'location_country',
    'avatar',
    'is_genius',
];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relaciones
    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }


    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'user_one_id')
            ->orWhere('user_two_id', $this->id);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'user_skills')
            ->using(\App\Models\UserSkill::class)
            ->withPivot('id', 'user_id', 'skill_id');
    }

    public function surprisesCreated()
    {
        return $this->hasMany(Surprise::class, 'creator_id');
    }

    public function surprisesAsGenius()
    {
        return $this->hasMany(Surprise::class, 'genius_id');
    }

    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'reviewed_user_id');
    }

    public function offers()
    {
        return $this->hasMany(Offer::class, 'genius_id');
    }

    public function pointEvents()
    {
        return $this->hasMany(GeniusPointEvent::class, 'genius_id');
    }

    /* ============================
       PRIVILEGIOS SEGÚN NIVEL
       ============================ */

    public function canReceiveInitialPayment(): bool
    {
        return in_array($this->genius_level, ['GENIE', 'SULTAN']);
    }

    public function allowedSurpriseSizes(): array
    {
        return match ($this->genius_level) {
            'SPARK' => ['SMALL'],
            'FLAME' => ['SMALL', 'MEDIUM'],
            'GENIE' => ['SMALL', 'MEDIUM', 'LARGE'],
            'SULTAN' => ['SMALL', 'MEDIUM', 'LARGE', 'PREMIUM'],
            default => [],
        };
    }

    public function maxActiveSurprises(): int
    {
        return match ($this->genius_level) {
            'SPARK' => 1,
            'FLAME' => 2,
            'GENIE' => 5,
            'SULTAN' => 10,
            default => 0,
        };
    }
    public function proposedSkills()
    {
        return $this->belongsToMany(Skill::class, 'user_proposed_skills');
    }

    public function activeSkills()
    {
        return $this->belongsToMany(Skill::class, 'user_skills')
            ->withPivot('level', 'xp');
    }

    public function canAcceptUrgent(): bool
    {
        return in_array($this->genius_level, ['FLAME', 'GENIE', 'SULTAN']);
    }
public function dailyOfferLimit()
{
    // Si tiene plan de genio, sumar extras
    $base = match($this->level) {
        'SPARK' => 10,
        'FLAME' => 15,
        'GENIE' => 25,
        'SULTAN' => 40,
        default => 10
    };

    // Si tiene plan de genio, añadir extras
    if ($this->geniusPlan && $this->geniusPlan->is_active) {
        return $base + $this->geniusPlan->extra_offers;
    }

    return $base;
}
/* ============================
   LÍMITE DE SORPRESAS ACTIVAS
   ============================ */

public function activeSurpriseLimit(): int
{
    // Límite base por nivel
    $base = match ($this->genius_level) {
        'SPARK' => 1,
        'FLAME' => 2,
        'GENIE' => 5,
        'SULTAN' => 10,
        default => 0,
    };

    // Si tiene plan de genio, sumar extras
    if (isset($this->geniusPlan) && $this->geniusPlan->is_active) {
        return $base + ($this->geniusPlan->extra_active_surprises ?? 0);
    }

    return $base;
}

public function currentActiveSurprises(): int
{
    return $this->surprisesAsGenius()
        ->whereIn('status', ['in_progress', 'delivered'])
        ->count();
}

public function canAcceptMoreSurprises(): bool
{
    return $this->currentActiveSurprises() < $this->activeSurpriseLimit();
}

    public function canAcceptPremium(): bool
    {
        return $this->genius_level === 'SULTAN';
    }
}
