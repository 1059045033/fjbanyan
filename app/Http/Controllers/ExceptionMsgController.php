<?php

namespace App\Http\Controllers;

use App\Models\ExceptionMsg;
use App\Models\User;
use App\Models\WorkRegion;
use Illuminate\Http\Request;

class ExceptionMsgController extends Controller
{
    public function __construct()
    {
        // 对数据进行处理 处理完就可以拿到用户信息
        $this->middleware('auth:api');
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
            'type'=> 'required|in:1,2,3,4',
            'user_id' => 'required|exists:users,id',
            'region_id' => 'required|exists:work_regions,id'
        ]);
        $type_enum = ['1'=>'迟到','2'=>'早退','3'=>'出圈','4'=>'滞留过久'];
        if($request->user_id != $user['id'])
        {
            return $this->myResponse([],'user_id 请传当前用户',200);
        }
        $region = WorkRegion::find($request->region_id);
        $temp_user = User::find($request->user_id);

        // 【郭伟文】于【2022.04.19 15.09】在【软件园F区】【迟到打卡/早退打卡/跑出工作区域】
        $content_ = $temp_user['name'].'于'.date('Y.m.d H:i').',在'.$region['name'].' '.$type_enum[$request->type];
        // 短信发送

        // 极光推送

        // 消息记录
        $track_id = ExceptionMsg::create([
            'user_id'           => $user['id'],
            'type'              => $request->type,
            'content'           => $content_,
            'position'          => json_encode($request->position),
            'address'           => $request->address
        ])->id;

        return $this->myResponse(['exception_msg_id'=>$track_id],'异常记录成功',200);
    }


    public function userHistory(Request $request)
    {
        $user = $request->user();
        $user_id = $user['id'];

        $list = ExceptionMsg::getlist($request->all(),$user_id);
        return $this->myResponse($list,'轨迹列表',200);
    }
}
