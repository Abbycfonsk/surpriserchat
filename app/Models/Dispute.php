<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    protected $fillable = [
        'surprise_id',
        'creator_id',
        'genius_id',
        'opened_by',
        'reason',
        'status',
        'resolution',
        'winner',
        'resolved_at',
    ];

    public function surprise()
    {
        return $this->belongsTo(Surprise::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function genius()
    {
        return $this->belongsTo(User::class, 'genius_id');
    }
}
