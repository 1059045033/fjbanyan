<?php

namespace App\Http\Controllers\Admin;;

use App\Models\OptRecord;
use App\Models\User;
use App\Models\WorkingTime;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WorkingTimeController extends Controller
{
    public function lists(Request $request)
    {
        $search = $request->query('name');
        $sort = 'asc';
        $fillter = [];
        //$request->query('name') && $fillter['name'] = $request->query('name');

        $request->query('sort') == '-id' && $sort = 'desc';
        $page = $request->query('page') ?? 1;
        $limit = $request->query('limit') ?? 10;

        !empty( $request->query('id')) && $fillter['user_id'] = $request->query('id');

        $total = WorkingTime::where($fillter)->when(!empty($search), function ($query) use($search){
            $query->where('name','like','%'.$search.'%');
        })->count();

        $list = WorkingTime::where($fillter)->when(!empty($search), function ($query) use($search){
            $query->where('name','like','%'.$search.'%');
        })->orderBy('id',$sort)->forPage($page,$limit)->get();

        $result = [
            'total' => $total,
            'items' => $list
        ];
        return $this->myResponse($result,'',200);
    }

    public function create(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_time'   => 'required',
            'end_time'   => 'required'
        ],[
            'user_id.*' => '人员参数错误',
            'start_time.required' => '开始必填',
            'end_time.required' => '结束必填',
        ]);



        $res = User::where(['phone'=>$request->phone])->first();
        if(!empty($res)){
            return $this->myResponse([],'号码已经存在',423);
        }

        if($new_id = WorkingTime::create([
            'user_id'=>$request->user_id,
            'name'=>$request->name,
            'start_time'=>$request->start_time,
            'end_time'=>$request->end_time,
        ])->id){
            $new_user = WorkingTime::where('id',$new_id)
                ->first();
            return $this->myResponse($new_user,'创建成功',200);
        }
        return $this->myResponse([],'创建失败',423);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:working_times,id'
        ]);

      
        $o_user = WorkingTime::find($request->id);
        if(empty($o_user)){
            return $this->myResponse([],'已经删除过了',423);
        }
        if($o_user->delete()){
            return $this->myResponse([],'删除成功',200);
        }
        return $this->myResponse([],'删除失败',423);
    }









}
