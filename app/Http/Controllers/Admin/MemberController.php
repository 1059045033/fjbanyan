<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\User;
use App\Services\JPushService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MemberController extends Controller
{
    private  $admin = null;
    public function __construct(Request $request)
    {
        $token = $request->header('X-Token');
        $this->admin  =  Admin::where(['remember_token' => $token])->first();

    }

    public function lists(Request $request)
    {
        $list = User::with(['company','Region:id,name'])
            ->select('id as user_id','name','avator','created_at','phone','image_base64','company_id','region_id','role')
            ->get();
        return $this->myResponse($list,'',200);
    }


    public function edit(Request $request)
    {
        return $this->myResponse([],'编辑成功',200);
    }

    public function create(Request $request)
    {
        return $this->myResponse([],'创建成功',200);
    }

    public function delete(Request $request)
    {
        return $this->myResponse([],'删除成功',200);
    }

}
