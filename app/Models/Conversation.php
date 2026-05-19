<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'surprise_id',
        'creator_id',
        'genius_id',
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

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
