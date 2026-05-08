<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreatorPackage extends Model
{
    protected $table = 'creator_packages';

    protected $fillable = [
        'user_id',
        'ads_total',
        'ads_used',
        'is_active'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
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

        if ($this->ads_used >= $this->ads_total) {
            $this->is_active = 0;
        }

        $this->save();

        return true;
    }
}