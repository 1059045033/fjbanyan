<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\DashboardRegionNoBody;
use App\Models\RegionGroup;
use App\Models\TaskLog;
use App\Models\User;
use App\Models\WorkingTime;
use App\Models\WorkRegion;
use App\Services\SmsFgService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\JPushService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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
    public function test04(){
        $params = [
            'to_name' => "尊敬的领导",
            'message' => '福州共享单车 '.date('Y-m-d').'打包数据',//福州共享单车 2022-05-16 打包数据
            'type'    => 'day',
            'data' =>[
                'task_url' => "http://task_url.com",
                'track_url' => "http://track_url.com",
            ]
        ];

        Mail::send('email',['params'=>$params],function($message){
            $to = ['190507753@qq.com'];//'190507753@qq.com;hui7893308@126.com';
            $message ->to($to)->subject(date('Y-m-d')."打包数据");
        });
        // 返回的一个错误数组，利用此可以判断是否发送成功
        dd(Mail::failures());
    }
    public function test05()
    {


        $day_timestamp =  $start_date = Carbon::parse('2022-09-01')->startOfDay()->timestamp;
        echo $day_timestamp;
        // 记录前一天的三方未出勤人数 ------end
        die;
        //echo json_encode($users);die;

        $yesterday = Carbon::yesterday()->timestamp;
        echo $yesterday;die;


        echo Carbon::now()->startOfDay()->timestamp;
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
