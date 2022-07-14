<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\TaskLog;
use App\Models\TaskLogNoSy;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskController extends Controller
{

    public function __construct()
    {
        // 对数据进行处理 处理完就可以拿到用户信息
        $this->middleware('auth:api');//->except(['show','index']);;
    }

    public function detail(StoreTaskRequest $request)
    {
            $task = Task::with(['completeUserInfo:id,name','completeTaskInfo'])->find($request->task_id);
            //$task = Task::with(['completeUserInfo:name'])->where('id',$request->task_id)->first();
            return $this->myResponse($task,'任务详情',200);
    }

    public function accept(StoreTaskRequest $request)
    {
        $task = Task::find($request->task_id);
        if(!empty($task->complete_user)){
            return $this->myResponse([],'任务已经被领取',423);
        }

        $user = $request->user();
        if($task->create_user == $user['id']){
            // return $this->myResponse([],'不能自己领取',423);
        }

        // 只有该区域下的人方可领取
        $users = User::where('work_region_id',$task->region_id)->select('id')->get();
        $users_ids = array_column($users->toArray(),'id');
        if(!in_array($user['id'],$users_ids)){
            return $this->myResponse([],'只有在该区域的工作人员可以领取',423);
        }

        $task->complete_user = $user['id'];
        $task->save();
        return $this->myResponse([],'接受成功',200);
    }

    public function todayProgress(Request $request)
    {
        $user = $request->user();
        $total = 24;
        $progress = ['complete'=>0,'incomplete'=>0,'progress'=>0];
        $start = Carbon::now()->startOfDay()->timestamp;
        $end   = Carbon::now()->endOfDay()->timestamp;
        $effective = TaskLog::where(['user_id'=>$user['id']])->where('is_effective','>',0)->whereBetWeen('created_at',[$start,$end])->get()->count();
        $progress['complete']   = TaskLog::where(['user_id'=>$user['id']])->whereBetWeen('created_at',[$start,$end])->get()->count();
        $progress['incomplete'] = $total - $effective;


        $progress['progress'] = (round($effective/$total,2)*100).'%';
        return $this->myResponse($progress,'当日任务量进度',200);

    }

    public function execute(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'type'    => 'required|in:1,2',
            'atlas'   => 'required|array',
            'position'=> 'required|array',
            'address' => 'required',
            'task_id' => ($request->input('type') == 1 ) ? 'nullable':'required|exists:tasks,id'
        ]);

        $is_effective = 1 ;
        $start = Carbon::now()->startOfDay()->timestamp;
        $end   = Carbon::now()->endOfDay()->timestamp;
        if($request->type == 2)
        {
            $temp =Task::where(['id'=>$request->task_id,'complete_user'=>$user['id']])->first();
            if(empty($temp)){
                return $this->myResponse([],'该任务未被接受，或者这个任务不是你的',423);
            }
            if(!empty($temp->is_complete)){
                return $this->myResponse([],'任务已经完成。不用重复做',423);
            }


        }

        $tasks = TaskLog::where(['user_id'=>$user['id'],'is_effective'=>1])
            ->whereBetWeen('created_at',[$start,$end])
            //->orderByDesc('created_at')
            ->get()->toArray();

        if(!empty($tasks)){
            // 最近的一个有效任务
            $task_last = array_pop($tasks);

            // 超过一个小时了
            if( Carbon::now()->timestamp >= (strtotime($task_last['created_at']) + ( 60 * 60 ))){
                $is_effective = 1 ;
            }else{

                // 一小时内的有效数量
                $effective_num = TaskLog::where(['user_id'=>$user['id']])
                    ->where('is_effective','>',0)
                    ->whereBetWeen('created_at',[strtotime($task_last['created_at']),$end])
                    ->orderByDesc('created_at')
                    ->get()->count();
                ($effective_num < 3) ? $is_effective = 2 : $is_effective = 0;
            }
        }

        $task_log_id = TaskLog::create([
            'user_id'           => $user['id'],
            'position'          => json_encode($request->position),
            'atlas'             => json_encode($request->atlas,JSON_UNESCAPED_SLASHES),
            'type'              => $request->type,
            'address'           => $request->address,
            'task_id'           => ($request->type == 1 ) ? null:$request->task_id,
            'is_effective'      => $is_effective,
            'content'           => $request->content_,
            'business_district' => $request->business_district,
            'work_region_id'    => empty($user['work_region_id']) ? null:$user['work_region_id']
        ])->id;

        // 修改 指派任务的任务状态
        if($is_effective && $request->type == 2){
            Task::where('id',$request->task_id)->update([
                'is_complete' => 1,
                'complete_time' => Carbon::now()->timestamp
            ]);
        }

        return $this->myResponse(['task_log_id'=>$task_log_id],'提交任务成功',200);
    }

    public function executeList(Request $request)
    {
        $user = $request->user();

        $list = TaskLog::getlist($request->all(),$user['id']);
        return $this->myResponse($list,'执行任务列表',200);

    }

    public function distributeList(Request $request)
    {
        $request->validate([
            'status'    => 'required|in:0,1,2,3',
        ]);
        $user = $request->user();
        $list = Task::getlist($request->all(),$user['id']);
        return $this->myResponse($list  ,'派发任务列表',200);
    }



    public function executeNoSY(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'atlas'   => 'required|array',
//            'task_log_id' => 'required'
        ]);

        $task_log_id = TaskLogNoSy::create([
            'user_id'           => $user['id'],
            'atlas'             => json_encode($request->atlas,JSON_UNESCAPED_SLASHES),
            'task_log_id'       => empty($request->task_log_id) ? 0:$request->task_log_id,
            'type'              => 0,
        ])->id;

        return $this->myResponse([],'记录成功',200);
    }

    public function executeListNoSY(Request $request)
    {
        $user = $request->user();

        $list = TaskLogNoSy::getlist($request->all(),$user['id']);
        return $this->myResponse($list,'执行任务列表-无水印',200);

    }

}
