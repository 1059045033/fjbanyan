<?php

namespace App\Http\Controllers;

use App\Models\WorkRegion;
use App\Http\Requests\StoreWorkRegionRequest;
use App\Http\Requests\UpdateWorkRegionRequest;
use Illuminate\Http\Request;

class WorkRegionController extends Controller
{


    public function __construct(Request $request)
    {
        // 对数据进行处理 处理完就可以拿到用户信息
        $this->middleware('auth:api');
    }

    public function regions()
    {
        $regions = WorkRegion::all();
        return $this->myResponse($regions,'获取地图区域成功(all)',200);
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
