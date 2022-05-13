<?php

namespace App\Http\Controllers;

use App\Models\WorkingTime;
use App\Http\Requests\StoreWorkingTimeRequest;
use App\Http\Requests\UpdateWorkingTimeRequest;

class WorkingTimeController extends Controller
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
     * @param  \App\Http\Requests\StoreWorkingTimeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreWorkingTimeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WorkingTime  $workingTime
     * @return \Illuminate\Http\Response
     */
    public function show(WorkingTime $workingTime)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WorkingTime  $workingTime
     * @return \Illuminate\Http\Response
     */
    public function edit(WorkingTime $workingTime)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateWorkingTimeRequest  $request
     * @param  \App\Models\WorkingTime  $workingTime
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateWorkingTimeRequest $request, WorkingTime $workingTime)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WorkingTime  $workingTime
     * @return \Illuminate\Http\Response
     */
    public function destroy(WorkingTime $workingTime)
    {
        //
    }
}
