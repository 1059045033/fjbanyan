<?php

namespace App\Http\Controllers;

use App\Models\ExceptionMsg;
use App\Models\User;
use App\Models\WorkRegion;
use App\Services\JPushService;
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
            return $this->myResponse([],'user_id 请传当前用户',423);
        }

        if($user['role'] != 10){
            return $this->myResponse([],'异常只有三级工作人员才产生',423);
        }


        // 工作区域
        $workRegion    = WorkRegion::find($request->region_id);
        $role_20_users = !empty($workRegion['region_manager']) ? [$workRegion['region_manager']]:[];
        if($request->region_id == $user['region_id']){
            // 工作区和所属区相同
            $region    = $workRegion;
        }else{
            // 所属区域 和 工作区不同
            $region    = WorkRegion::find($user['region_id']);
            !empty($region['region_manager']) && $role_20_users[] = $region['region_manager'];
        }
        // 找到所以一级管理员
        $role_30_users = User::where('role',30)->select('id')->get()->pluck('id')->toArray();
        $all_user_ids     = array_unique(array_merge($role_20_users,$role_30_users));
        $all_user_ids     = array_push($all_user_ids,$user['id']);
        $all_users     = User::whereIn('id',$all_user_ids)->select('id','phone','jpush_reg_id')->get()->toArray();

        // 【郭伟文】于【2022.04.19 15.09】在【软件园F区】【迟到打卡/早退打卡/跑出工作区域】
        $content_ = $user['name']."(".$user['phone'].")".'于'.date('Y.m.d H:i').',在'.$workRegion['name'].' '.$type_enum[$request->type];


        # ================ 短信发送  1,2 (不给自己发) start ======
        if(in_array($request->type,[1,2]))
        {
            //发送短信
        }
        # ================ 短信发送  1,2 (不给自己发)   end ======


        # ================ 极光推送  1,2,3,4(给自己发) start ======
        $jpush_reg_ids = [];
        foreach ($all_users as $k => $v)
        {
            !empty($v['jpush_reg_id']) && $jpush_reg_ids[] = $v['jpush_reg_id'];
        }

        JPushService::pushNotify([
            'title' => $type_enum[$request->type],
            'content' => $content_,
            'reg_id' =>  $jpush_reg_ids,
            'extras' =>  [],
            'type' =>  JPushService::PUSH_TYPE_REG_ID,
        ]);
        # ================ 极光推送  1,2,3,4(给自己发)   end ======


        # ================ 产生记录  1,2,3,4(给自己发) start ======
        foreach ($all_users as $k => $v)
        {
            ExceptionMsg::create([
                'user_id'           => $v['id'],
                'type'              => $request->type,
                'content'           => $content_,
                'position'          => json_encode($request->position),
                'address'           => $request->address
            ]);
        }
        # ================ 产生记录  1,2,3,4(给自己发) start ======

        return $this->myResponse([],'异常记录成功',200);
    }


    public function userHistory(Request $request)
    {
        $user = $request->user();
        $user_id = $user['id'];

        $list = ExceptionMsg::getlist($request->all(),$user_id);
        return $this->myResponse($list,'轨迹列表',200);
    }

    public function check(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'exception_id' => 'required|exists:exception_msgs,id',
        ]);
        $exception = ExceptionMsg::find($request->exception_id);
        if($exception->user_id != $user['id'] )
        {
            return $this->myResponse([],'只能查阅自己的',423);
        }

        if(empty($exception->is_read))
        {
            $exception->is_read = 1;
            $exception->save();
        }
        return $this->myResponse($exception,'查阅异常信息',200);
    }

    // 批量发送短信
    public function batchSms()
    {

    }
}
