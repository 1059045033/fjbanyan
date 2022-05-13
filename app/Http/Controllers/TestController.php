<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkingTime;
use App\Services\SmsFgService;
use Illuminate\Http\Request;
use App\Services\JPushService;

class TestController extends Controller
{
    public function test()
    {
        //同步所有人员的上下班时间
        $users = User::all();
        WorkingTime::where('id','>',0)->delete();
        foreach ($users as $k=>$v){
            WorkingTime::create([
                'user_id' => $v['id'],
                'name' => '早班',
                'start_time' => '07:00',
                'end_time' => '11:00',
            ]);

            WorkingTime::create([
                'user_id' => $v['id'],
                'name' => '午班',
                'start_time' => '15:00',
                'end_time' => '17:00',
            ]);
        }

    }
    public function test02()
    {
        $sms= new SmsFgService();
        $content__ = "测试人员(15860816380)||".date('Y.m.d H:i')."||上下行(迟到)";
        $res = $sms->sendsms('15860816380,18046032876',$content__,146515,122136);
        echo json_encode($res);
        die;
    }
}
