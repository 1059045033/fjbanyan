<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function send(){
        //echo '发送邮件';
        $name = '测试邮件';
        // Mail::send()的返回值为空，所以可以其他方法进行判断
        // Mail::send();需要传三个参数;
        // 第一个为引用的模板
        // 第二个为给模板传递的变量（邮箱发送的文本内容）
        // 第三个为一个闭包，参数绑定Mail类的一个实例。
        Mail::send('welcome',['name'=>$name],function($message){
            $to = '190507753@qq.com';
            $message ->to($to)->subject('邮件测试');
        });
        // 返回的一个错误数组，利用此可以判断是否发送成功
        dd(Mail::failures());
    }
}
