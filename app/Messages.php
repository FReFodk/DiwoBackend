<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    protected $table = 'messages';
    protected $fillable = [
        'sender_id' , 'receiver_id' ,'title', 'message' , 'is_read'
    ];
    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }
    public function receiver(){
	  return $this->hasOne( 'App\User', 'user_id', 'receiver_id' );
	}
	public function sender(){
	  return $this->hasOne( 'App\User', 'user_id', 'sender_id' );
	}
}
