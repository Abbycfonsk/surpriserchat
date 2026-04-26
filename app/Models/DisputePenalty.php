<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisputePenalty extends Model
{
    protected $fillable = [
        'dispute_id',
        'genius_id',
        'penalty_level',
        'starts_at',
        'ends_at',
    ];

    public function dispute()
    {
        return $this->belongsTo(Dispute::class);
    }

    public function genius()
    {
        return $this->belongsTo(User::class, 'genius_id');
    }
}
