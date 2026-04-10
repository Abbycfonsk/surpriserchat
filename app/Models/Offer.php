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
        'status'
    ];

    public function surprise()
    {
        return $this->belongsTo(Surprise::class);
    }

    public function genius()
    {
        return $this->belongsTo(User::class, 'genius_id');
    }
}
