<?php

namespace App\Http\Controllers;

use App\Models\OnlineOffline;
use App\Http\Requests\StoreOnlineOfflineRequest;
use App\Http\Requests\UpdateOnlineOfflineRequest;
use App\Models\User;
use Illuminate\Http\Request;

class OnlineOfflineController extends Controller
{

    public function __construct(Request $request)
    {
        // 对数据进行处理 处理完就可以拿到用户信息
        $this->middleware('auth:api');
    }

    public function online(StoreOnlineOfflineRequest $request)
    {
        $position = ['lng'=>$request->lng,'lat'=>$request->lat];
        // 判断上线时间是否有效 7:03-11:00  15:03-23:00
        $Hi = date('H:i');
        $is_late = 0 ;  //迟到
        if(($Hi > '07:03' && $Hi < '11:00') ||  ($Hi > '15:03' && $Hi < '23:00'))
        {
            $is_late = 1;

        }

        OnlineOffline::create([
            'user_id'   => $request->user()['id'],
            'position'  => json_encode($position),
            'address'   => empty($request->address) ? '':$request->address,
            'type'      => 1,
            'tag'       => !empty($is_late) ? 'late':''
        ]);
        User::where('id',$request->user()['id'])->update(['is_online'=>1]);

        if($is_late)
        {
            return $this->myResponse(['tag'=>'late'],'迟到',200);
        }

        return $this->myResponse(['tag'=>'normal'],'上线成功',200);
    }

    public  function offline(StoreOnlineOfflineRequest $request)
    {
        $position = ['lng'=>$request->lng,'lat'=>$request->lat];
        $Hi = date('H:i');
        $is_early = 0 ;  //早退
        if(($Hi > '07:03' && $Hi < '11:00') ||  ($Hi > '15:03' && $Hi < '23:00'))
        {
            $is_early = 1;

        }

        OnlineOffline::create([
            'user_id'   => $request->user()['id'],
            'position'  => json_encode($position),
            'address'   => empty($request->address) ? '':$request->address,
            'type'      => 2,
            'tag'       => !empty($is_early) ? 'early':''
        ]);
        User::where('id',$request->user()['id'])->update(['is_online'=>0]);

        if($is_early)
        {
            return $this->myResponse(['tag'=>'early'],'早退',200);
        }


        return $this->myResponse(['tag'=>'normal'],'下线成功',200);
    }

    public function history(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'month' => 'nullable'//|date_format:Y-m',
        ]);

        $data['start'] = strtotime($request->month);        //指定月份的开始时间戳
        $data['end'] = mktime(23,59,59,date('m',$data['start'])+1,00);

        $histories = OnlineOffline::where(['user_id'=>$user['id']])
            ->whereBetween('created_at', [$data['start'], $data['end']])
            ->select('id','type','position','address','is_effective','created_at','imag')
            ->orderByDesc('created_at')
            ->get()->each(function ($data,$key){
                $data->day = date('Y-m-d',strtotime($data->created_at));
                $data->time = date('H:i:s',strtotime($data->created_at));
                $data->position = json_decode($data->position,1);
            });

        $histories_ = [];
        $day = [];
        foreach ($histories as $k=>$v)
        {
//            $day_ = intval(date('d',strtotime($v['day'])));
//            if(!in_array($day_,$day)){
//                $day[] =$day_;
//            }
            @$histories_[$v['day']][] = $v;
        }
        $result = [];
        foreach ($histories_ as $k=>$v){
            $temp = [];
            foreach ($v as $kk=>$vv){
                $temp[] = $vv;
            }
            $result[] = ['day' => $k ,'details'=>$temp];
        }
//        $days = ['days'=>$day];
//        $results = array_merge([$days],$result);
        return $this->myResponse($result,'获取上线/线下日历成功',200);
    }

}
