<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Push_notification extends Model
{
    protected $fillable = [
        'udid',
        'platform',
        'push_id',
        'user_id',
        'type',
    ];
}
