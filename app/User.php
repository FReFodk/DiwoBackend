<?php

    namespace App;

    use Illuminate\Notifications\Notifiable;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Tymon\JWTAuth\Contracts\JWTSubject;

    class User extends Authenticatable implements JWTSubject
    {
        use Notifiable;

        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $table = 'users';
        protected $primaryKey = 'user_id';
        public $timestamps = false;
        protected $fillable = [
            'role_id' , 'team_id' ,'first_name', 'last_name' , 'email', 'password' , 'hours_hired'  , 'is_prev_logged_in'
        ];

        /**
         * The attributes that should be hidden for arrays.
         *
         * @var array
         */
        protected $hidden = [
            'password',
        ];

        public function getJWTIdentifier()
        {
            return $this->getKey();
        }
        public function getJWTCustomClaims()
        {
            return [];
        }
        public function messages(){
            return $this->hasMany('App\Messages','receiver_id');

        }
    }