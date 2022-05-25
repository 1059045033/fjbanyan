<?php

namespace App\Http\Controllers;

use App\Models\TaskLog;
use App\Models\User;
use App\Models\WorkingTime;
use App\Services\SmsFgService;
use Illuminate\Http\Request;
use App\Services\JPushService;

class TestController extends Controller
{
    public function test()
    {
        $url = "http://test.com.cn/weww/eww";

        if($this->filterURL($url)){
            echo "白名单域名";
        }else{
            echo "不在白名单中";
        }
        die;
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
                'start_time' => '14:00',
                'end_time' => '18:00',
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


    public function test03()
    {
        $tasks = TaskLog::where('created_at','>',1652976000)->pluck('id')->toArray();
        $next = 0;
        $ttt = [];
        foreach ($tasks as $k=>$v){
            if($k == 0 )
            {
                $next = $v;
            }else{
                $diff = $v - $next;
                if($diff > 1){
                    $ttt[] = $diff;
                }
                $next = $v;
            }
        }

        echo json_encode($ttt);
    }


    function filterURL($url)
    {
        $allowDomains = ["test.com", "demo.com"];
        $obj_url = parse_url($url);
        $obj_url['host'];
        if(in_array($obj_url['host'],$allowDomains)){
            return true;
        }
        return false;

    }

}
