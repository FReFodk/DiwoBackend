<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExperienceLikes extends Model
{
     protected $table = 'experience_likes';
    public $timestamps = false;
    protected $fillable = [
        'user_id', 'expr_id'
    ];
}
