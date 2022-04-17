<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Http\Requests\StoreTrackRequest;
use App\Http\Requests\UpdateTrackRequest;
use App\Models\User;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function __construct()
    {
        // 对数据进行处理 处理完就可以拿到用户信息
        $this->middleware('auth:api');//->except(['show','index']);;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'position'=> 'required|array',
            'address' => 'required'
        ]);

        $track_id = Track::create([
                'user_id'           => $user['id'],
                'position'          => json_encode($request->position),
                'address'           => $request->address,
            ])->id;

        return $this->myResponse(['track_id'=>$track_id],'轨迹创建成功',200);
    }


    public function userHistory(Request $request)
    {
        $user = $request->user();
        $user_id = $user['id'];
        // 1.判断权限
        if(!in_array($user['role'],[20,30])){
            return $this->myResponse([],'只有区域管理员/总管理员才能查看轨迹',423);
        }

        // 如果没传user_id 就是查当前这个token用户的
        if(!empty($request->user_id)){
            $temp_user = User::find($request->user_id);
            $user_id = $temp_user->id;
        }

        // 如果当前操作的是 区域管理员 ,那这个区域管理员要先被设置所属区域
        if($user['role'] == 20 && empty($user['region_id'])){
            return $this->myResponse([],'区域管理员请先先给设置下自己的所属区域',423);
        }

        // 如果传的user_id 不是属于该区域的不让查询
        if($user['role'] == 20 && $temp_user->region_id != $user['region_id']){
            return $this->myResponse([],'区域管理员只能查看属于自己区域的人员的轨迹',423);
        }

        // 如果没传 start_date 默认就是今天
        $params['start_date'] = empty($request->start_date) ? date('Y-m-d'): $request->start_date;

        $list = Track::getlist($params,$user_id);
        return $this->myResponse($list,'轨迹列表',200);
    }

}
