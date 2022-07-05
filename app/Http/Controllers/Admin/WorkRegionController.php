<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\WorkRegion;
use App\Models\User;
use App\Http\Requests\StoreWorkRegionRequest;
use App\Http\Requests\UpdateWorkRegionRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class WorkRegionController extends Controller
{

    private  $admin = null;
    public function __construct(Request $request)
    {
        // 对数据进行处理 处理完就可以拿到用户信息
        //$this->middleware('auth:api');
        $token = $request->header('X-Token');
        $this->admin  =  Admin::where(['remember_token' => $token])->first();
    }

    public function regions(Request $request)
    {
        $search = $request->query('title');
        $sort = 'asc';
        $fillter = [];
        //$request->query('title') && $fillter['name'] = $request->query('title');

        $request->query('sort') == '-id' && $sort = 'desc';
        $page = $request->query('page') ?? 1;
        $limit = $request->query('limit') ?? 10;

        $total = WorkRegion::where($fillter)->when(!empty($search), function ($query) use($search){
            $query->where('name','like','%'.$search.'%');
        })->count();

        $list = WorkRegion::with(['regionManagerInfo:id,name','groupInfo:id,name'])
            ->where($fillter)
            ->when(!empty($search), function ($query) use($search){
                $query->where('name','like','%'.$search.'%');
            })
            ->orderBy('id',$sort)->forPage($page,$limit)->get()->each(function ($data){
                $data->zhongxin = $this->getCenterPoint($data->region_scope);
            })->toArray();

        $result = [
            'total' => $total,
            'items' => $list
        ];

        return $this->myResponse($result,'获取地图区域成功(all)'.$page.' - '.$limit,200);
    }

    public function regions_all(Request $request)
    {
        $search = $request->query('title');
        $sort = 'asc';
        $fillter = [];

        $list = WorkRegion::with('regionManagerInfo:id,name')
            ->where($fillter)
            ->select('id','name')
            ->orderBy('id',$sort)->get()->toArray();
        $result = [
            'total' => count($list),
            'items' => $list
        ];
        return $this->myResponse($result,'获取地图区域成功(all)',200);
    }

    public function unArrange()
    {
        $users = User::where(['role'=>20])
//            ->whereNull('region_id')
//            ->orWhere('region_id','')
            ->select('id as user_id','name','phone')
            ->get()->each(function ($data){
            $data->label = $data->name.'('.$data->phone.')';
        })->toArray();

        $regions = WorkRegion::with('regionManagerInfo')
            ->select('id as region_id','name','region_scope','region_manager')
            ->get()->each(function ($data){
                $data->zhongxin = $this->getCenterPoint($data->region_scope);
            })->toArray();
        $res = [
            'users' => $users,
            'regions' => $regions
        ];
        return $this->myResponse($res,'',200);
    }

    public function roleOne(Request $request)
    {
        $has_region = empty($request->has_region) ? 0 :$request->has_region;

        // where(['role'=>10])->
        $users = User::when(!empty($has_region), function ($query) use($has_region){
            if($has_region == 1){
                $query->where('region_id','>',0);
            }elseif ($has_region == 2)
            {
                $query->whereNull('region_id');
            }
            })->select('id as user_id','name','phone','region_id','role')
            ->get()->each(function ($data){
                $data->label = $data->name.'('.$data->phone.')';
            })->toArray();

        $old = [];
        if(!empty($request->region_id)){
            $old_ = WorkRegion::with('regionUsers')->where(['id'=>$request->region_id])->first();
            if(!empty($old_->regionUsers)){
                foreach ($old_->regionUsers as $v){
                    //if($v['role'] == 10){
                        $old[] = ['user_id'=>$v['id'],'name'=>$v['name'],'phone'=>$v['phone'],'label'=>$v['name'].'('.$v['phone'].')'];
                    //}

                }
            }
        }

        $res = [
            'users' => $users,
            'old' => $old,
        ];
        return $this->myResponse($res,'',200);
    }

    public function belongTo(Request $request){
        $request->validate([
            'id' => 'required|exists:work_regions,id',
            'manager_id' => 'required|exists:users,id',
            'title'    => 'required',
        ],[
            'manager_id.*' => '管理员参数错误',
            'title' => '请输入名字',
        ]);

        $regInfo = WorkRegion::find($request->id);

        if($request->id != $regInfo->region_manager){
            $regInfo->region_manager = $request->manager_id;
        }

        if($request->title != $regInfo->name){
            $regInfo->name = $request->title;
        }

        if($request->group_id != $regInfo->group_id){
            $regInfo->group_id = $request->group_id;
        }


        if(empty($request->works)){
            User::where(['region_id'=>$request->id,'role'=>10])->update(['region_id'=>null]);
        }else{
            User::where(['region_id'=>$request->id,'role'=>10])->update(['region_id'=>null]);
            User::whereIn('id',$request->works)->update(['region_id'=>$request->id]);
        }

        if($regInfo->save())
        {
            $new_user = WorkRegion::with(['regionManagerInfo:id,name','groupInfo:id,name'])
                ->where('id',$request->id)
                ->first();

            # ======== 记录操作 start ===============
            $desc = "【{$this->admin['name']}】 于 ".date('Y-m-d H:i:s')."【网格】模块【编辑】网格【{$regInfo['name']}】";
            $this->recordLogs($request,2,$this->admin,$desc);
            # ======== 记录操作 end   ===============
            return $this->myResponse($new_user,'修改成功'.$request->group_id.' -- '.$regInfo->group_id,200);
        }
        return $this->myResponse([],'修改失败',423);
    }


    public function create(Request $request)
    {

        if(empty($request->manager_id))
        {
            return $this->myResponse([],'管理人员不能为空',423);
        }

        $res = WorkRegion::where('region_manager',$request->manager_id)->first();
        if(!empty($res)){
            //return $this->myResponse([],'该工作人员已经是区域经理了',423);
        }

        if(empty($request->scope))
        {
            return $this->myResponse([],'选择的区域不能为空',423);
        }

        if(empty($request->title))
        {
            return $this->myResponse([],'选择的标题不能为空',423);
        }

        $new_id = WorkRegion::create([
            'name'=> $request->title,
            'region_scope' => json_encode($request->scope),
            'region_manager' =>$request->manager_id
        ])->id;

        // 修改所属的区域
//        User::where('id',$request->manager_id)->update([
//            'region_id'=> $new_id,
//            'role'     => 20
//        ]);

        # ======== 记录操作 start ===============
        $desc = "【{$this->admin['name']}】 于 ".date('Y-m-d H:i:s')."【网格】模块【新增】网格【{$request->title}】";
        $this->recordLogs($request,1,$this->admin,$desc);
        # ======== 记录操作 end   ===============
        return $this->myResponse([],'创建新区域成功',200);
    }

    public function delete(Request $request)
    {

        $request->validate([
            'id' => 'required|exists:work_regions,id'
        ],[
            'id.required' => '区域ID',
            'id.unique' => '区域不存在',
        ]);

//        $users = User::where('company_id',$request->id)->select('id')->get();
//        if(!empty($users)){
//            return $this->myResponse([],'公司下还有人员',200);
//        }

        //WorkRegion::where('id',$request->id)->delete();

        $workregion = WorkRegion::findOrFail($request->id);
        $workregion->delete();
        // 删除区域 解除对应的区域绑定 (归属/工作 )
        User::where('region_id',$request->id)->update(['region_id'=>null]);
        User::where('work_region_id',$request->id)->update(['work_region_id'=>null]);

        # ======== 记录操作 start ===============
        $desc = "【{$this->admin['name']}】 于 ".date('Y-m-d H:i:s')."【网格】模块【删除】网格【{$workregion['name']}】";
        $this->recordLogs($request,3,$this->admin,$desc);
        # ======== 记录操作 end   ===============

        return $this->myResponse([],'删除成功',200);
    }


    public function region(Request $request)
    {
        $request->validate([
            'region_id' => 'required|exists:work_regions,id'
        ]);
        $regions = WorkRegion::find($request->region_id);
        return $this->myResponse($regions,'获取地图区域成功(单个)',200);
    }






    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreWorkRegionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreWorkRegionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WorkRegion  $workRegion
     * @return \Illuminate\Http\Response
     */
    public function show(WorkRegion $workRegion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WorkRegion  $workRegion
     * @return \Illuminate\Http\Response
     */
    public function edit(WorkRegion $workRegion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateWorkRegionRequest  $request
     * @param  \App\Models\WorkRegion  $workRegion
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateWorkRegionRequest $request, WorkRegion $workRegion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WorkRegion  $workRegion
     * @return \Illuminate\Http\Response
     */
    public function destroy(WorkRegion $workRegion)
    {
        //
    }


    public function getCenterPoint($path)
    {
        $path = json_decode($path,1);
        $x = 0.0;
        $y = 0.0;
        $leng = count($path);
        for($i=0 ; $i < $leng ;$i++)
        {
            $x = $x + $path[$i]['lng'];
            $y = $y + $path[$i]['lat'];
        }
        $x = $x/$leng;
        $y = $y/$leng;

        return [
            'lng'=>$x,
            'lat'=>$y
        ];
    }

    public function getRegionUser($region_id)
    {

    }
}
