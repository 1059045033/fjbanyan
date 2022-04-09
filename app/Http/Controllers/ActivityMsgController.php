<?php

namespace App\Http\Controllers;

use App\Models\ActivityMsg;
use App\Http\Requests\StoreActivityMsgRequest;
use App\Http\Requests\UpdateActivityMsgRequest;

class ActivityMsgController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Http\Requests\StoreActivityMsgRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreActivityMsgRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ActivityMsg  $activityMsg
     * @return \Illuminate\Http\Response
     */
    public function show(ActivityMsg $activityMsg)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ActivityMsg  $activityMsg
     * @return \Illuminate\Http\Response
     */
    public function edit(ActivityMsg $activityMsg)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateActivityMsgRequest  $request
     * @param  \App\Models\ActivityMsg  $activityMsg
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateActivityMsgRequest $request, ActivityMsg $activityMsg)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ActivityMsg  $activityMsg
     * @return \Illuminate\Http\Response
     */
    public function destroy(ActivityMsg $activityMsg)
    {
        //
    }
}
