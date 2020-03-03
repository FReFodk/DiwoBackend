<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    protected $table = 'experience';
    public $timestamps = false;
    protected $fillable = [
        'user_id', 'experience' , 'date','total_likes'
    ];
}
