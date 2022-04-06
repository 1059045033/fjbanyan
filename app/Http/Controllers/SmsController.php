<?php

namespace App\Http\Controllers;

use App\Models\Sms;
use App\Http\Requests\StoreSmsRequest;
use App\Http\Requests\UpdateSmsRequest;

class SmsController extends Controller
{

    public function send(StoreSmsRequest $request)
    {

        // 产生验证码
        $code = str_pad(mt_rand(10, 999999), 6, "0", STR_PAD_BOTH);

        // 发送验证码
        // 。。。。。 发送代码

        // 发送成功 记录验证码
        Sms::create([
            'code' => $code,
            'expire_time'=> time()+600,
            'mobile' => $request->phone,
            'type' => $request->type
        ]);

        return response()->json([
            'data'=>[
                'code' => $code
            ],
            'message'=> '短信发送成功'
        ],201);
    }
}
