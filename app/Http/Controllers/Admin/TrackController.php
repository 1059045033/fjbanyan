<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
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
        //1、红色red，绿色green，白色white，黑色black，黄色yellow。 2、灰色gray，粉红色pink，褐色茶色brown，银色silver，栗色maroon。 3、金色gold，黄绿色greenyellow，蓝色blue，藏青色navy，薄荷色mintcream
        $index = ['0'=>'red','1'=>'green','3'=>'yellow','4'=>'pink'];
        $ind = 0;
        foreach ($list as $k=>$v)
        {
            $item = json_decode($v['position'],1);
            if(!empty($item))
            {
                if(empty($items[$v['user_id']])){
                    $items[$v['user_id']] = ['user_id'=>$v['user_id'],'name'=>$v['userInfo']['name'],'color'=>$index[$ind]];
                    $items[$v['user_id']]['positions'][] = $item;
                    $ind ++;
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


}
