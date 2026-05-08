<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurpriseAd extends Model
{
    protected $table = 'surprise_ads';

    protected $fillable = [
        'surprise_id',
        'creator_id',
        'ad_type',
        'priority',
        'activated_at',
        'expires_at',
        'is_active',
        'notified_at'
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function surprise()
    {
        return $this->belongsTo(Surprise::class, 'surprise_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}