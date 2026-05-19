<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CreatorPlan extends Model
{
    protected $table = 'creator_plans';

    protected $fillable = [
        'user_id',
        'plan_type',
        'starts_at',
        'ends_at',
        'ads_total',
        'ads_used',
        'is_active'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /* ============================
       RELACIONES
       ============================ */

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ads()
    {
        return $this->hasMany(SurpriseAd::class, 'creator_id', 'user_id');
    }

    /* ============================
       HELPERS
       ============================ */

    public function isExpired(): bool
    {
        return $this->ends_at->isPast();
    }

    public function isActiveNow(): bool
    {
        return $this->is_active &&
               $this->starts_at->isPast() &&
               $this->ends_at->isFuture();
    }

    public function remainingAds(): int
    {
        return max(0, $this->ads_total - $this->ads_used);
    }

    public function consumeAd(): bool
    {
        if ($this->remainingAds() <= 0) {
            return false;
        }

        $this->ads_used += 1;
        $this->save();

        return true;
    }

    /* ============================
       DURACIÓN Y PRIORIDAD
       ============================ */

    public function adDurationDays(): int
    {
        return match ($this->plan_type) {
            'starter' => 15,
            'pro' => 20,
            'premium' => 30,
            'free_trial' => 30,
            default => 15,
        };
    }
public function earlyAccessHours(): int
{
    return match ($this->plan_type) {
        'starter' => 1,
        'pro' => 2,
        'premium' => 3,
        default => 0,
    };
}

    public function adPriority(): int
    {
        return match ($this->plan_type) {
            'starter' => 1,
            'pro' => 2,
            'premium' => 3,
            'free_trial' => 3,
            default => 1,
        };
    }
}