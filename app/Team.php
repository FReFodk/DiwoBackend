<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $table = 'team';
    public $timestamps = false;
    protected $fillable = [
        'team_name', 'description'
    ];
}
