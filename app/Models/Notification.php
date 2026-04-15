<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    public $timestamps = true; // ahora usamos created_at y updated_at

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'metadata',
        'read_flag',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'read_flag' => 'boolean',
    ];
}
