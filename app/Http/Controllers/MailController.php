<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function send(){

        $params = [
            'to_name' => "尊敬的领导",
            'message' => date('Y-m-d').'的数据',
            'data' =>[
                'task_url' => "http://www.baidu.com",
                'track_url' => "http://www.baidu.com",
            ]
        ];
        Mail::send('email',['params'=>$params],function($message){
            $to = ['190507753@qq.com','1059045033@qq.com','359448144@qq.com'];
            $message ->to($to)->subject(date('Y-m-d')."打包数据");
        });
        // 返回的一个错误数组，利用此可以判断是否发送成功
        dd(Mail::failures());
    }
}
