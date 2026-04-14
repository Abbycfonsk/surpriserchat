<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'category'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_skills');
    }
    public function proposedByUsers()
    {
        return $this->belongsToMany(User::class, 'user_proposed_skills');
    }

    public function activeUsers()
    {
        return $this->belongsToMany(User::class, 'user_skills')
            ->withPivot('level', 'xp');
    }

    public function surprises()
    {
        return $this->hasMany(Surprise::class);
    }
}
