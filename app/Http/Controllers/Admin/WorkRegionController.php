<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\WorkRegion;
use App\Models\User;
use App\Http\Requests\StoreWorkRegionRequest;
use App\Http\Requests\UpdateWorkRegionRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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

        $list = WorkRegion::with('regionManagerInfo:id,name')
            ->where($fillter)
            ->when(!empty($search), function ($query) use($search){
                $query->where('name','like','%'.$search.'%');
            })
            ->orderBy('id',$sort)->forPage($page)->limit($limit)->get()->toArray();
        $result = [
            'total' => $total,
            'items' => $list
        ];

        return $this->myResponse($result,'获取地图区域成功(all)',200);
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
        $users = User::where(['role'=>20])->whereNull('region_id')->orWhere('region_id','')->select('id as user_id','name','phone')->get()->each(function ($data){
            $data->label = $data->name.'('.$data->phone.')';
        })->toArray();

        $regions = WorkRegion::with('regionManagerInfo')
            ->select('id as region_id','name','region_scope','region_manager')
            ->get()->toArray();
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
