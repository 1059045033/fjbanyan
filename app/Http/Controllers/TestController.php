<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JPushService;

class TestController extends Controller
{
    public function test()
    {
        // https://cloud.tencent.com/developer/article/1722522
        // https://blog.csdn.net/qq_40679463/article/details/117247134
        JPushService::pushNotify([
            //标题
            'title' =>  '测试',
            //内容
            'content' =>  '测试',
            //设备标识，跟设备相关
            'reg_id' =>  'xxxxxxxxxxx',
            //扩展字段
            'extras' =>  [
                'key' =>  'value',
            ],
            //推送类型
            'type' =>  JPushService::PUSH_TYPE_REG_ID,
        ]);



        //
        $user =[];
        //
        JPushService::updateAlias($user->jpush_reg_id, 'user_id_' . $user->id);
        //
        JPushService::updateAlias($user->jpush_reg_id, '');

        JPushService::pushNotify([
            'title' =>  '测试',
            'content' =>  '测试',
            'alias' =>  'user_id_' . $message->receive_id,
            'extras' =>  $extras,
            'type' =>  JPushService::PUSH_TYPE_ALIAS,
        ]);
    }
}
