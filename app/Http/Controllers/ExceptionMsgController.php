<?php

namespace App\Http\Controllers;

use App\Models\ExceptionMsg;
use App\Models\User;
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
            'type'=> 'required|in:1,2,3',
            'content' => 'required'
        ]);

        // 短信发送

        // 极光推送

        // 消息记录
        $track_id = ExceptionMsg::create([
            'user_id'           => $user['id'],
            'type'              => $request->type,
            'content'           => $request->content,
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
