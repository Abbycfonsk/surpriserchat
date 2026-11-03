<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
   protected $fillable = [
    'surprise_id',
    'genius_id',
    'price',
    'original_price',
    'message',
    'eta_hours',
    'status',
    'accepted_at',
    'creator_bid_count',
    'genius_bid_count',
];
protected $casts = [
    'price' => 'decimal:2',
    'original_price' => 'decimal:2',
    'accepted_at' => 'datetime',
];

    public function surprise()
    {
        return $this->belongsTo(Surprise::class);
    }

    public function genius()
    {
        return $this->belongsTo(User::class, 'genius_id');
    }

    public function bids()
    {
        return $this->hasMany(OfferBid::class);
    }
}
