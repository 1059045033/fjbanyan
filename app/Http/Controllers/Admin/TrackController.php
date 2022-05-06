<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\TaskLog;
use App\Models\Track;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class TrackController extends Controller
{
    private  $admin = null;
    public function __construct(Request $request)
    {
        // 对数据进行处理 处理完就可以拿到用户信息
        $token = $request->header('X-Token');
        $this->admin  =  Admin::where(['remember_token' => $token])->first();
    }

    /**
     *
     */
    public function all_lists(Request $request)
    {
        $search = $request->query('name');
        $sort = 'asc';
        $fillter = [];

        $request->query('sort') == '-id' && $sort = 'desc';
        $page = $request->query('page') ?? 1;
        $limit = $request->query('limit') ?? 10;

        $total = Track::where($fillter)->when(!empty($search), function ($query) use($search){
            $query->where('name','like','%'.$search.'%');
        })->count();
        $start_date = Carbon::parse('2022-01-01')->startOfDay()->timestamp;
        $end_date   = Carbon::parse('2022-05-01')->endOfDay()->timestamp;

        $list  = Track::with('userInfo:id,name')->where($fillter)->when(!empty($search), function ($query) use($search){
            $query->where('name','like','%'.$search.'%');
        })->where('created_at','>',$start_date)
        ->where('created_at','<',$end_date)->get();

        $items = [];
        foreach ($list as $k=>$v)
        {
            $item = json_decode($v['position'],1);
            if(!empty($item))
            {
                if(empty($items[$v['user_id']])){
                    $items[$v['user_id']] = ['user_id'=>$v['user_id'],'name'=>$v['userInfo']['name']];
                    $items[$v['user_id']]['positions'][] = $item;
                }else{
                    $items[$v['user_id']]['positions'][] = $item;
                }
            }

        }

        $items = array_values($items);

        $result = [
            'total' => count($items),
            'items' => $items
        ];

        return $this->myResponse($result,'',200);
    }

    /**
     * 任务轨迹
     */
    public function track(Request $request)
    {
        $search = $request->query('name');
        $fillter = [];

        $page = $request->query('page') ?? 1;
        $limit = $request->query('limit') ?? 10;

        $count = User::where($fillter)
            ->when(!empty($search), function ($query) use($search){
                $query->where('name','like','%'.$search.'%');
            })
            ->count();

        $list  = User::where($fillter)
            ->when(!empty($search), function ($query) use($search){
                $query->where('name','like','%'.$search.'%');
            })
            ->forpage($page,$limit)
            ->get();

        $result = [
            'total' => $count,
            'items' => $list
        ];

        return $this->myResponse($result,'',200);
    }



}
