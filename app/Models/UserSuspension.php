<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSuspension extends Model
{
    protected $fillable = [
        'user_id',
        'suspended_until',
        'reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
