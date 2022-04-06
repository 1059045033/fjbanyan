<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sms extends Model
{
    use HasFactory;
    protected $guarded = [] ;
    //public $timestamps = FALSE;
    protected $dateFormat = 'U';
    //protected $fillable = ['code','mobile','expire_time','type'];

    public function verificationCode($code = '',$mobile = '',$type=0)
    {
        return self::where([
            'mobile' => $mobile,
            'code'   => $code,
            'type'   => $type,
            //'expire_time' => ['>',time()]
        ])->where('expire_time','>',time())->first();
    }

    public function updateCode($code = '',$mobile = '',$type=0)
    {
        return self::where([
            'mobile' => $mobile,
            'code'   => $code,
            'type'   => $type,
        ])->update(['expire_time'=>time()]);
    }
}
