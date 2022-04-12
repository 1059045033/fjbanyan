<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\User;

class TaskController extends Controller
{

    public function __construct()
    {
        // 对数据进行处理 处理完就可以拿到用户信息
        $this->middleware('auth:api');//->except(['show','index']);;
    }

    public function detail(StoreTaskRequest $request)
    {
            $task = Task::find($request->task_id);
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
            return $this->myResponse([],'不能自己领取',423);
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

    



}
