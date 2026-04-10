<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurpriseFile extends Model
{
    protected $table = 'surprise_files';
    public $timestamps = false;

    protected $fillable = [
        'surprise_id',
        'filename',
        'path',
        'mime',
        'size',
        'file_url',
        'file_type',
    ];


    public function surprise()
    {
        return $this->belongsTo(Surprise::class, 'surprise_id');
    }
}
