<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'surprise_id',
        'genius_id',
        'price',
        'message',
        'eta_hours',
        'status',
        'creator_bid_count',
        'genius_bid_count',
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
