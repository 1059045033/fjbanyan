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


        if($day == false){
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
}
