<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SocialKapital extends Model
{
    protected $table = 'social_kapital';
    public $timestamps = false;
    /*protected $fillable = [
        'user_id', 'review_date' , 'review' , 'last_review_date'
    ];*/

    protected $fillable = [
        'user_id', 'review_date' , 'comment' , 'question1','question2','question3','question4','question5','last_review_date'
    ];
}

