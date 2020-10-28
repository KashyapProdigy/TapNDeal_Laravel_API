<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use Notifiable;

    protected $table="users";
    public $timestamps=false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'mobile', 'password','type_id','city_id','state_id','isVerified','date_time',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    public function routeNotificationForOneSignal()
    {
        return ['include_external_user_ids' => $this->id];
    }

    public function routeNotificationForFcm($notification)
    {
        // dd($this->msg_token);
        return $this->msg_token;
    }
}
