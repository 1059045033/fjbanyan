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
        $search = $request->query('name');
        $sort = 'asc';
        $fillter = [];
       // $request->query('name') && $fillter['name'] = $request->query('name');
        $request->query('type') && $fillter['role'] = $request->query('type');

        $request->query('sort') == '-id' && $sort = 'desc';
        $page = $request->query('page') ?? 1;
        $limit = $request->query('limit') ?? 10;

        $total = User::where($fillter)->count();
        $list = User::with(['company','Region:id,name'])
            ->where($fillter)
            ->when(!empty($search), function ($query) use($search){
                $query->where('name','like','%'.$search.'%');
            })
            ->select('id','name','avator','created_at','phone','image_base64','company_id','region_id','role')
            ->orderBy('id',$sort)->forPage($page)->limit($limit)->get();
        $result = [
            'total' => $total,
            'items' => $list
        ];
        return $this->myResponse($result,'',200);
    }


    public function edit(Request $request)
    {
        return $this->myResponse([],'编辑成功',200);
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
        return $this->myResponse([],'删除成功',200);
    }

}
