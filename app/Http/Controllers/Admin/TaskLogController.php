<?php

namespace App\Http\Controllers\Admin;

use App\Models\Company;
use App\Models\TaskLog;
use App\Http\Requests\StoreTaskLogRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskLogController extends Controller
{
    public function logs(Request $request)
    {

        $search = $request->query('name');
        $sort = 'desc';
        $fillter = [];


        $request->query('sort') == '+id' && $sort = 'asc';
        $page = $request->query('page') ?? 1;
        $limit = $request->query('limit') ?? 10;

        $day = empty($request->query('start_date')) ? date('Y-m-d'):$request->query('start_date');

        $start_date = Carbon::parse($day)->startOfDay()->timestamp;
        $end_date   = Carbon::parse($day)->endOfDay()->timestamp;

        $user_ids = [];
        if(!empty($search)){
            $user_ids = User::where('name','like','%'.$search.'%')->pluck('id')->toArray();
            empty($user_ids) && $user_ids = [-1];
        }


        $total = TaskLog::where($fillter)
            ->when(!empty($user_ids), function ($query) use($user_ids){
                $query->whereIn('user_id',$user_ids);
            })
            ->where('created_at','>',$start_date)
            ->where('created_at','<',$end_date)->count();


        $list = TaskLog::with(['userInfo:id,name,phone','workRegionInfo:id,name'])->where($fillter)
            ->when(!empty($user_ids), function ($query) use($user_ids){
                $query->whereIn('user_id',$user_ids);
            })
            ->where('created_at','>',$start_date)
            ->where('created_at','<',$end_date)
            ->orderBy('id',$sort)->forPage($page,$limit)->get();

        $result = [
            'total' => $total,
            'items' => $list
        ];

        return $this->myResponse($result,''.$start_date.' -- '.$end_date." == ".$day,200);
    }
}
