<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferBid extends Model
{
    protected $fillable = [
        'offer_id',
        'user_id',
        'role',
        'price',
        'eta_hours',
        'message',
    ];

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
