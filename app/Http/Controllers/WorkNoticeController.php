<?php

namespace App\Http\Controllers;

use App\Models\WorkNotice;
use App\Http\Requests\StoreWorkNoticeRequest;
use App\Http\Requests\UpdateWorkNoticeRequest;

class WorkNoticeController extends Controller
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
     * @param  \App\Http\Requests\StoreWorkNoticeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreWorkNoticeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WorkNotice  $workNotice
     * @return \Illuminate\Http\Response
     */
    public function show(WorkNotice $workNotice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WorkNotice  $workNotice
     * @return \Illuminate\Http\Response
     */
    public function edit(WorkNotice $workNotice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateWorkNoticeRequest  $request
     * @param  \App\Models\WorkNotice  $workNotice
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateWorkNoticeRequest $request, WorkNotice $workNotice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WorkNotice  $workNotice
     * @return \Illuminate\Http\Response
     */
    public function destroy(WorkNotice $workNotice)
    {
        //
    }
}
