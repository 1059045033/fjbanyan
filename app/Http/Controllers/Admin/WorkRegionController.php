<?php

namespace App\Http\Controllers\Admin;

use App\Models\WorkRegion;
use App\Models\User;
use App\Http\Requests\StoreWorkRegionRequest;
use App\Http\Requests\UpdateWorkRegionRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WorkRegionController extends Controller
{


    public function __construct(Request $request)
    {
        // 对数据进行处理 处理完就可以拿到用户信息
        //$this->middleware('auth:api');
    }

    public function regions()
    {
        $regions = WorkRegion::with('regionManagerInfo:id,name')->get()->toArray();
        $result = [
            'total' => count($regions),
            'items' => $regions
        ];
        return $this->myResponse($result,'获取地图区域成功(all)',200);
    }


    public function unArrange()
    {
        $users = User::where(['role'=>10])->select('id as user_id','name','phone')->get()->each(function ($data){
            $data->label = $data->name.'('.$data->phone.')';
        })->toArray();

        $regions = WorkRegion::with('regionManagerInfo')->select('id as region_id','name','region_scope','region_manager')->get()->toArray();
        $res = [
            'users' => $users,
            'regions' => $regions
        ];

        return $this->myResponse($res,'',200);
    }


    public function create(Request $request)
    {

        $res = WorkRegion::where('region_manager',$request->manager_id)->first();
        if(!empty($res)){
            return $this->myResponse([],'该工作人员已经是区域经理了',423);
        }

        $new_id = WorkRegion::create([
            'name'=> $request->title,
            'region_scope' => json_encode($request->scope),
            'region_manager' =>$request->manager_id
        ])->id;

        // 修改所属的区域
        User::where('id',$request->manager_id)->update([
            'region_id'=> $new_id,
            'role'     => 20
        ]);
        return $this->myResponse([],'创建新区域成功',200);
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
}
