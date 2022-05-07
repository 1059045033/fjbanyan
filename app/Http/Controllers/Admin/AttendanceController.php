<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attendance;
use App\Models\TaskLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AttendanceController extends Controller
{
    public function lists(Request $request)
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

//        $user_ids = [];
//        if(!empty($search)){
//            $user_ids = User::where('name','like','%'.$search.'%')->pluck('id')->toArray();
//            empty($user_ids) && $user_ids = [-1];
//        }


        $total = Attendance::where($fillter)
            ->when(!empty($search), function ($query) use($search){
                //$query->whereIn('user_id',$user_ids);
                $query->where('user_name','like','%'.$search.'%');
            })
            ->where('created_at','>',$start_date)
            ->where('created_at','<',$end_date)->count();


        $list = Attendance::where($fillter)
            ->when(!empty($search), function ($query) use($search){
                //$query->whereIn('user_id',$user_ids);
                $query->where('user_name','like','%'.$search.'%');
            })
            ->where('created_at','>',$start_date)
            ->where('created_at','<',$end_date)
            ->orderBy('id',$sort)->forPage($page,$limit)
            ->get()
            ->each(function ($data){
                $arr_temp = [];
                if(!empty($data->online_times)){
                    $arr_temp = json_decode($data->online_times,1);
                }
                $data->online_times_arr =  $arr_temp;

                $arr_temp = [];
                if(!empty($data->offline_times)){
                    $arr_temp = json_decode($data->offline_times,1);
                }
                $data->offline_times_arr =  $arr_temp;
            });

        $result = [
            'total' => $total,
            'items' => $list
        ];

        return $this->myResponse($result,'',200);
    }
}
