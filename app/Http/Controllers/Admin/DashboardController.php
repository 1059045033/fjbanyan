<?php

namespace App\Http\Controllers\Admin;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function attendances(Request $request){


        $day = empty($request->query('start_date')) ? false:$request->query('start_date');
        $day_timestamp = Carbon::now()->startOfDay()->timestamp;
        if($day !=false)
        {
            $day_timestamp = Carbon::parse($day)->startOfDay()->timestamp;
        }

        $companies = DB::table('companies')->whereNull('deleted_at')->pluck('name','id')->toArray();

        // 出勤
        $chuqing = DB::table('dashboard_attendances')
            ->where(['type'=>1,'date_day'=>$day_timestamp])
            ->select(DB::raw('company_id,count(company_id) as nums'))
            ->groupBy('company_id')
            ->get()->toArray();
        $chuqing = array_column($chuqing,'nums','company_id');


        if($day == false || Carbon::now()->startOfDay()->timestamp == $day_timestamp){
            // 如果是当天的
            // 公司总人数 - 已经出勤的人数  = 未出勤的人数
            $company_users = DB::table('users')
                ->whereNull('deleted_at')
                ->select(DB::raw('company_id,count(company_id) as nums'))
                ->groupBy('company_id')
                ->get()->toArray();
            $nochuqing = [];
            foreach ($company_users as $k=>$v){
                $chuqin_nums = empty($chuqing[$v->company_id]) ? 0:$chuqing[$v->company_id];
                $nochuqing[$v->company_id] = $v->nums - $chuqin_nums;
            }
        }else{
            // 未出勤
            $nochuqing = DB::table('dashboard_attendances')
                ->where(['type'=>2,'date_day'=>$day_timestamp])
                ->select(DB::raw('company_id,count(company_id) as nums'))
                ->groupBy('company_id')
                ->get()->toArray();
            $nochuqing = array_column($nochuqing,'nums','company_id');
        }

        $list = [];
        foreach ($companies as $k=>$v){
            $list['xAxisData'][] = $v;
            $list['chuqin'][]    = empty($chuqing[$k]) ? 0:$chuqing[$k];
            $list['nochuqin'][]  = empty($nochuqing[$k]) ? 0:$nochuqing[$k];
        }

        return $this->myResponse($list,'',200);
    }


    public function region_nobody(Request $request)
    {
        $day = empty($request->query('start_date')) ? false:$request->query('start_date');
        $day_timestamp = Carbon::now()->startOfDay()->timestamp;
        if($day !=false)
        {
            $day_timestamp = Carbon::parse($day)->startOfDay()->timestamp;
        }

        $list = DB::table('dashboard_region_no_bodies')
            ->where(['date_day'=>$day_timestamp])
            ->select('group_name as name','body_nums as value')
            ->get()->toArray();

        if($day == false || Carbon::now()->startOfDay()->timestamp == $day_timestamp){
            $list = [];
            # 所有的组 每个组有多少网格
            $groups = DB::table('region_groups')->pluck('name','id')->toArray();

            $user_work_region_id = DB::table('users')
                ->whereNull('deleted_at')
                ->whereNotNull('work_region_id')
                ->pluck('work_region_id')->toArray();
            $user_work_region_id = array_unique($user_work_region_id);

            $regions = DB::table('work_regions')
                ->whereNull('deleted_at')
                ->whereNotIn('id',$user_work_region_id)
                ->where('group_id','>',0)
                ->select(DB::raw('group_id,count(group_id) as nums'))
                ->groupBy('group_id')
                ->get()->toArray();
            $regions = array_column($regions,'nums','group_id');
            foreach ($groups as $k=>$v){
                if(empty($regions[$k])){
                    $list[] = ['value'=>0,'name'=>$v];
                }else{
                    $list[] = ['value'=>$regions[$k],'name'=>$v];
                }
            }
        }



        return $this->myResponse($list,$day.' -- '.$day_timestamp,200);
    }

    public function late_early(Request $request)
    {
        $day = empty($request->query('start_date')) ? false:$request->query('start_date');
        $type = empty($request->query('type')) ? 'company_id':$request->query('type');

        $day_timestamp = Carbon::now()->startOfDay()->timestamp;
        if($day !=false)
        {
            $day_timestamp = Carbon::parse($day)->startOfDay()->timestamp;
        }

        // 迟到数据
        $late = DB::table('dashboard_late_or_earlies')
            ->where(['type'=>1,'date_day'=>$day_timestamp])
            ->select(DB::raw($type.',count('.$type.') as nums'))
            ->groupBy($type)
            ->get()->toArray();
        $late = array_column($late,'nums',$type);
        // 早退数据
        $early =  DB::table('dashboard_late_or_earlies')
            ->where(['type'=>2,'date_day'=>$day_timestamp])
            ->select(DB::raw($type.',count('.$type.') as nums'))
            ->groupBy($type)
            ->get()->toArray();
        $early = array_column($late,'nums',$type);
        // x轴
        $xAxisData = [];
        if($type == 'company_id')
        {
            $xAxisData = DB::table('companies')->whereNull('deleted_at')->pluck('name','id')->toArray();
        }elseif ($type == 'user_region'){
            $xAxisData = DB::table('region_groups')->pluck('name','id')->toArray();
        }

        $list = [];
        foreach ($xAxisData as $k=>$v){
            $list['xAxisData'][] = $v;
            $list['late'][]      = empty($late[$k])  ?  0:$late[$k];
            $list['early'][]     = empty($early[$k]) ? 0:$early[$k];
        }

        return $this->myResponse($list,''.$day_timestamp,200);
    }
}
