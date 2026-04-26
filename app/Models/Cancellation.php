<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cancellation extends Model
{
    protected $fillable = [
        'surprise_id',
        'genius_id',
        'creator_id',
        'cancelled_by',
        'reason_key',
        'reason_text',
    ];

    public function surprise()
    {
        return $this->belongsTo(Surprise::class);
    }

    public function genius()
    {
        return $this->belongsTo(User::class, 'genius_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
