<?php

namespace App\Http\Controllers;

use App\Models\Sms;
use App\Http\Requests\StoreSmsRequest;
use App\Http\Requests\UpdateSmsRequest;
use Illuminate\Support\Facades\DB;

class SmsController extends Controller
{

    public function send(StoreSmsRequest $request)
    {
        $sms = Sms::where(['mobile'=>$request->phone,'type'=>$request->type])->where('expire_time','>',time())->first();
        if(!empty($sms)){
            //return $this->myResponse([],'该号码已经请求过了，请稍后再请求',423);
        }
        Sms::where(['mobile'=>$request->phone,'type'=>$request->type])->delete();
        // 产生验证码
        $code = str_pad(mt_rand(10, 999999), 6, "0", STR_PAD_BOTH);

        // 发送验证码
        // 。。。。。 发送代码

        // 发送成功 记录验证码
        Sms::create([
            'code' => $code,
            'expire_time'=> time()+(60 * 60 * 72),
            'mobile' => $request->phone,
            'type' => $request->type
        ]);

        return $this->myResponse(['code'=>$code],'短信发送成功',200);
    }
}
