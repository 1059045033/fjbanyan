<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\User;
use App\Services\JPushService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

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
        $search = $request->query('name');
        $sort = 'asc';
        $fillter = [];
       // $request->query('name') && $fillter['name'] = $request->query('name');
        $request->query('type') && $fillter['role'] = $request->query('type');

        $request->query('sort') == '-id' && $sort = 'desc';
        $page = $request->query('page') ?? 1;
        $limit = $request->query('limit') ?? 10;

        $total = User::where($fillter)->when(!empty($search), function ($query) use($search){
            $query->where('name','like','%'.$search.'%');
        })->count();

        $list = User::with(['company','Region:id,name'])
            ->where($fillter)
            ->when(!empty($search), function ($query) use($search){
                $query->where('name','like','%'.$search.'%');
            })
            ->select('id','name','avator','created_at','phone','image_base64','company_id','region_id','role')
            ->orderBy('id',$sort)->forPage($page,$limit)->get();
        $result = [
            'total' => $total,
            'items' => $list
        ];
        return $this->myResponse($result,'',200);
    }


    public function edit(Request $request)
    {
        $request->validate([
            'role'    => 'required|in:10,20,30',
            'company' => 'required|exists:companies,id',
            'name'    => 'required',
            'phone'   => 'required'
        ],[
            'role.*' => '等级参数错误',
            'company.required' => '公司参数错误111',
            'company.exists' => '公司不存在',
            'name' => '请输入名字',
            'phone.required' => '号码必填',
        ]);

        $userInfo = User::find($request->id);
        if($request->phone != $userInfo->phone){
            $res = DB::table('users')->where('phone',$request->phone)->where('id','<>',$request->id)->first();
            if(empty($res)){
                $userInfo->phone = $request->phone;
            }else{
                return $this->myResponse([],'该号码已经被注册',423);
            }
        }

        if($request->name != $userInfo->name){
            $userInfo->name = $request->name;
        }
        if($request->company != $userInfo->company_id){
            $userInfo->company_id = $request->company;
        }
        if($request->role != $userInfo->role){
            $userInfo->role = intval($request->role);
        }

        if(!empty($request->region) && $request->region != $userInfo->region_id){
            $userInfo->region_id = $request->region;
        }

        if($userInfo->save())
        {
            $new_user = User::with(['company','Region:id,name'])
                ->where('id',$request->id)
                ->first();
            return $this->myResponse($new_user,'修改成功',200);
        }
        return $this->myResponse([],'修改失败',423);
    }

    public function create(Request $request)
    {
        $request->validate([
            'role'    => 'required|in:10,20,30',
            'company' => 'required|exists:companies,id',
            'name'    => 'required',
            'phone'   => 'required|unique:users,phone'
        ],[
            'role.*' => '等级参数错误',
            'company.*' => '公司参数错误',
            'name' => '请输入名字',
            'phone.required' => '号码必填',
            'phone.unique' => '号码已经存在',
        ]);

        if($new_id = User::create([
            'name'=>$request->name,
            'company_id'=>$request->company,
            'role'=>$request->role,
            'phone'=>$request->phone,
            'password'=>bcrypt('123456'),
            'image_base64'=>'',
            'region_id'=>$request->region,
            'avator'=>''
            ])->id){
            $new_user = User::with(['company','Region:id,name'])
                ->where('id',$new_id)
                ->first();
            return $this->myResponse($new_user,'创建成功',200);
        }
        return $this->myResponse([],'创建失败',423);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id'
        ]);

        $o_user = User::find($request->id);
        if(empty($o_user)){
            return $this->myResponse([],'已经删除过了',423);
        }
        if($o_user->delete()){
            return $this->myResponse([],'删除成功',200);
        }
        return $this->myResponse([],'删除失败',423);
    }

}
