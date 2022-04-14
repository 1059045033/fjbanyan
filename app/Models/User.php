<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
//use Laravel\Sanctum\HasApiTokens; 不用这个
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable,HasApiTokens;
    protected $dateFormat = 'U';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'image_base64',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * 重写oauth授权验证
     * @param $username
     * @return mixed
     */
    public function findForPassPort($username)
    {
        return $this->where('phone',$username)->first();
    }

    public function findForEmail($username)
    {
        return self::where('email',$username)->first();
    }

    public function findForPhone($username)
    {
        return self::where('phone',$username)->first();
    }

    public function notices()
    {
        return $this->hasMany(WorkNotice::class,'id','user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function workRegion()
    {
        return $this->hasOne(WorkRegion::class,'id','work_region_id');
    }


    public function ownsNotice($notice)
    {
        return $this->id === $notice->user_id;
    }

}
