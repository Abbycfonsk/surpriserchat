<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Models\Surprise;

class UserSkill extends Pivot
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'skill_id'];

    protected $appends = ['level'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function getLevelAttribute()
    {
        $user = $this->user;
        $skillId = $this->skill_id;

        // SOLO cuenta las sorpresas realizadas como GENIUS
        $genius = \App\Models\Surprise::where('genius_id', $user->id)
            ->where('skill_id', $skillId)
            ->count();

        $total = $genius;

        if ($total >= 50) return 5;
        if ($total >= 25) return 4;
        if ($total >= 10) return 3;
        if ($total >= 4) return 2;
        return 1;
    }
}
