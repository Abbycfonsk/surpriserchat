<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    public $timestamps = false; // porque solo tienes created_at

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'read_flag',
        'created_at',
    ];
}
