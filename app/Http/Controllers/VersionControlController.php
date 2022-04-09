<?php

namespace App\Http\Controllers;

use App\Models\VersionControl;
use App\Http\Requests\StoreVersionControlRequest;
use App\Http\Requests\UpdateVersionControlRequest;

class VersionControlController extends Controller
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
     * @param  \App\Http\Requests\StoreVersionControlRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreVersionControlRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\VersionControl  $versionControl
     * @return \Illuminate\Http\Response
     */
    public function show(VersionControl $versionControl)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\VersionControl  $versionControl
     * @return \Illuminate\Http\Response
     */
    public function edit(VersionControl $versionControl)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateVersionControlRequest  $request
     * @param  \App\Models\VersionControl  $versionControl
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateVersionControlRequest $request, VersionControl $versionControl)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VersionControl  $versionControl
     * @return \Illuminate\Http\Response
     */
    public function destroy(VersionControl $versionControl)
    {
        //
    }
}
