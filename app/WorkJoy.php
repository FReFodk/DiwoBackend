<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkJoy extends Model
{
    protected $table = 'work_joy';
    public $timestamps = false;
    protected $fillable = [
        'user_id', 'review_date' , 'review' , 'last_review_date'
    ];
}
