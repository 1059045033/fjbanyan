<?php

namespace App\Http\Controllers;

use App\Http\Resources\WorkNoticeCollection;
use App\Http\Resources\WorkNoticeResource;
use App\Models\ActivityMsg;
use App\Models\ExceptionMsg;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkNotice;
use App\Http\Requests\StoreWorkNoticeRequest;
use App\Http\Resources\TopicCollection;
use App\Services\JPushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkNoticeController extends Controller
{
    public function __construct()
    {
        // 对数据进行处理 处理完就可以拿到用户信息
        $this->middleware('auth:api');//->except(['show','index']);;
    }


    // 创建一条新的通知
    public function create(StoreWorkNoticeRequest $request)
    {
        $user = $request->user();
        if(empty($user->region_id))
        {
            return $this->myResponse([],'还未分配所属区域',423);
        }

        // 给区域管理员也加上 工作区域 就为自身所属区域
        $user->work_region_id = $user->region_id;
        $user->save();

        $users = User::where('work_region_id',$user->region_id)->select('id as user_id')->get();
        if(empty($users))
        {
            return $this->myResponse([],'该区域还未安排作业人员',423);
        }

        // 任务通知字段
        $fields = $request->all();
        $fields['atlas'] = json_encode($fields['atlas'],JSON_UNESCAPED_SLASHES);
        $fields['position'] = json_encode($fields['position']);
        $fields['type'] = 2;

        // 任务字段
        $task_fields = [
            'type' => 1,
            'content' => $fields['content'],
            'atlas' => $fields['atlas'],
            'position'=> $fields['position'],
            'create_user'=> $user['id'],
            'region_id'=> $user->region_id,
            'address'=> $fields['address'],
            'business_district'=>$fields['business_district']
        ];

        DB::beginTransaction();
        try {
            $task_id = Task::create($task_fields)->id;
            $jpush_reg_ids = [];
            foreach ($users as $k=>$v){
                // 创建任务消息
                $fields['user_id'] = $v['user_id'];
                $fields['task_id'] = $task_id;
                WorkNotice::create($fields);
                // 调用三方推送
                !empty($v['jpush_reg_id']) && $jpush_reg_ids[] = $v['jpush_reg_id'];
            }
            #=========== 派发任务推送 start =========
            JPushService::pushInApp([
                'reg_id' =>  $jpush_reg_ids,
                'extras' =>  [
                    'type' => 2,
                ],
                'type' =>  JPushService::PUSH_TYPE_REG_ID,
            ]);
            #=========== 派发任务推送   end =========

            DB::commit();
        } catch (QueryException $exception) {
            DB::rollback();
            return $this->myResponse([],'创建任务失败,请联系管理人员',423);
        }
        return $this->myResponse([],'创建任务成功,并通知该区域工作人员',200);

    }

    /**
     * 展示一条通知
     */
    public function show(WorkNotice $workNotice)
    {
        //
    }

    /**
     * 工作通知列表
     */
    public function index(Request $request)
    {
        $user = $request->user();
        // $notices = WorkNotice::with('user')->where('user_id',$user['id'])->paginate(3);
        // $list = new WorkNoticeCollection($notices);
        $list = WorkNotice::getlist($request->all(),$user['id']);
        return $this->myResponse($list,'获取成功',200);
    }

    public function read(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'notice_id' => 'required|exists:work_notices,id'
        ]);

        $notice = WorkNotice::find($request->notice_id);

       if(!$user->ownsNotice($notice))
       {
           return $this->myResponse([],'只能查阅自己的',423);
       }
        if(empty($notice->is_read)){
            $notice->is_read = 1;
            $notice->save();
            return $this->myResponse([],'查阅完成',200);
        }

    }

    public function unReadTip(Request $request)
    {
        $user = $request->user();
        $tips = [ 'notice'=>0,'activity'=>0,'exception'=>0];
        $notice = WorkNotice::where(['user_id'=>$user['id'],'is_read'=>0])->count();
        $tips['notice'] = $notice;

        // $activity = ActivityMsg::where(['user_id'=>$user['id'],'is_read'=>0])->count();
        //$tips['activity'] = $activity;

        $exception = ExceptionMsg::where(['user_id'=>$user['id'],'is_read'=>0])->count();
        $tips['exception'] = $exception;

        $tips['total'] = $tips['notice'] + $tips['activity'] + $tips['exception'];

        $user_n = User::with('workRegion:id,name')->find($user['id']);//
        $tips['work_region_info'] = $user_n['workRegion'];
        return $this->myResponse($tips,'获取未读提醒消息',200);
    }
}
