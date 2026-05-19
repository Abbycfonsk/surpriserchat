<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeniusPointEvent extends Model
{
    protected $fillable = [
        'genius_id',
        'surprise_id',
        'type',
        'points_delta',
    ];

    public function genius()
    {
        return $this->belongsTo(User::class, 'genius_id');
    }

    public function surprise()
    {
        return $this->belongsTo(Surprise::class);
    }
}
