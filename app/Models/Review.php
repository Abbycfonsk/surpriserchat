<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';

    protected $fillable = [
        'surprise_id',
        'reviewer_id',
        'reviewed_user_id',
        'rating_surprise',
        'rating_genius',
        'comment',
    ];

    public function surprise()
    {
        return $this->belongsTo(Surprise::class, 'surprise_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewedUser()
    {
        return $this->belongsTo(User::class, 'reviewed_user_id');
    }
}
