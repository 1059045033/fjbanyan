<?php

namespace App\Http\Controllers;

use App\Models\TaskLog;
use App\Models\User;
use App\Models\WorkRegion;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskLogController extends Controller
{
    public function __construct()
    {
        // 对数据进行处理 处理完就可以拿到用户信息
        $this->middleware('auth:api');//->except(['show','index']);;
    }

    public function logs(Request $request)
    {
        $user = $request->user();

        if(!in_array($user['role'],[20,30])){
            return $this->myResponse([],'只有区域管理员/总管理员才能获取数据',423);
        }


        $sort = 'desc';
        $fillter = [];


        $limit = $request->size ?? 10;

        $day = empty($request->start_date) ? date('Y-m-d'):$request->start_date;

        $start_date = Carbon::parse($day)->startOfDay()->timestamp;
        $end_date   = Carbon::parse($day)->endOfDay()->timestamp;

        $user_ids = [];
        if($user['role'] == 20){
            // 获取区域管理员所有区域
            $region_ids = WorkRegion::where('region_manager',$user['id'])->pluck('id')->toArray();
            if(!empty($region_ids))
            {
                $user_ids = User::whereIn('region_id',$region_ids)->pluck('id')->toArray();
            }else{
                $user_ids = [-1];
            }
        }


//        if(!is_array($request->user_ids) || !is_array($request->user_ids) || !is_array($request->user_ids)){
//            //return $this->myResponse([],'user_ids,region_id,company_ids请传数组',423);
//        }


        if(!empty($request->user_ids) || !empty($request->company_ids) || !empty($request->region_ids))
        {
            $params_user_ids = empty($request->user_ids) ? []:$request->user_ids;
            $params_region_ids = empty($request->region_ids) ? []:$request->region_ids;
            $params_company_ids = empty($request->company_ids) ? []:$request->company_ids;
            $user_idsss = User::when(!empty($params_user_ids), function ($query) use($params_user_ids){
                $query->whereIn('id',$params_user_ids);
            })->when(!empty($params_region_ids), function ($query) use($params_region_ids){
                $query->whereIn('region_id',$params_region_ids);
            })->when(!empty($params_company_ids), function ($query) use($params_company_ids){
                $query->whereIn('company_id',$params_company_ids);
            })
            ->pluck('id')->toArray();
            print_r($user_idsss);
            die;
        }





        $list = TaskLog::with(['userInfo:id,name,phone','workRegionInfo:id,name'])->where($fillter)
            ->when(!empty($user_ids), function ($query) use($user_ids){
                $query->whereIn('user_id',$user_ids);
            })
            ->where('created_at','>',$start_date)
            ->where('created_at','<',$end_date)
            ->orderBy('id',$sort)->paginate($limit);

        return $this->myResponse($list,'获取数据成功',200);
    }

}
