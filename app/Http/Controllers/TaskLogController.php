<?php

namespace App\Http\Controllers;

use App\Models\TaskLog;
use App\Http\Requests\StoreTaskLogRequest;
use App\Http\Requests\UpdateTaskLogRequest;

class TaskLogController extends Controller
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
     * @param  \App\Http\Requests\StoreTaskLogRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTaskLogRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TaskLog  $taskLog
     * @return \Illuminate\Http\Response
     */
    public function show(TaskLog $taskLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TaskLog  $taskLog
     * @return \Illuminate\Http\Response
     */
    public function edit(TaskLog $taskLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTaskLogRequest  $request
     * @param  \App\Models\TaskLog  $taskLog
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTaskLogRequest $request, TaskLog $taskLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TaskLog  $taskLog
     * @return \Illuminate\Http\Response
     */
    public function destroy(TaskLog $taskLog)
    {
        //
    }
}
