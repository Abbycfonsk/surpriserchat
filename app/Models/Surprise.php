<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surprise extends Model
{
    protected $table = 'surprises';

    protected $fillable = [
        'creator_id',
        'genius_id',
        'skill_id',
        'title',
        'description',
        'status',
        'price',
        'deadline',
        'size',
        'is_urgent',
        'target_name',
        'target_city',
        'target_country',
        'target_lat',
        'target_lng',
        'price_creator',
        'price_genius',
        'final_price'
    ];
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function genius()
    {
        return $this->belongsTo(User::class, 'genius_id');
    }
    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function files()
    {
        return $this->hasMany(SurpriseFile::class, 'surprise_id');
    }

   
    public function ads()
{
    return $this->hasMany(SurpriseAd::class, 'surprise_id');
}


public function review()
{
    return $this->hasOne(Review::class, 'surprise_id');
}
    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }
}
