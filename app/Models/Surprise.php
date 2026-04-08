<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surprise extends Model
{
    protected $table = 'surprises';

    protected $fillable = [
        'creator_id',
        'genius_id',
        'title',
        'description',
        'status',
        'price',
        'deadline',
        'skill_id' // ← AÑADIDO
    ];
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function genius()
    {
        return $this->belongsTo(User::class, 'genius_id');
    }

    public function files()
    {
        return $this->hasMany(SurpriseFile::class, 'surprise_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'surprise_id');
    }
    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }
}
