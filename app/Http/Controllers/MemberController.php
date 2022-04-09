<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivityMsgRequest;
use App\Http\Requests\UpdateActivityMsgRequest;
use App\Models\ActivityMsg;
use Illuminate\Http\Request;

class MemberController extends Controller
{

    public function __construct()
    {
        // 对数据进行处理 处理完就可以拿到用户信息
        $this->middleware('auth:api');
    }

    public function index()
    {
    }


    public function create()
    {
    }

    public function store(Request $request)
    {
        $user = $request->user();
        return $this->myResponse($user,'得到用户信息',200);
    }

    public function show(Request $request)
    {


    }


    public function update(UpdateActivityMsgRequest $request, ActivityMsg $activityMsg)
    {

    }

    public function destroy(ActivityMsg $activityMsg)
    {
    }
}
