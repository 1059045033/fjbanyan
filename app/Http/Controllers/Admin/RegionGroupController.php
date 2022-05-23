<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\Company;
use App\Models\RegionGroup;
use App\Models\User;
use App\Models\WorkRegion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RegionGroupController extends Controller
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
        //$request->query('name') && $fillter['name'] = $request->query('name');

        $request->query('sort') == '-id' && $sort = 'desc';
        $page = $request->query('page') ?? 1;
        $limit = $request->query('limit') ?? 10;

        $total = RegionGroup::where($fillter)->when(!empty($search), function ($query) use($search){
            $query->where('name','like','%'.$search.'%');
        })->count();

        $list = RegionGroup::select('id','name')->when(!empty($search), function ($query) use($search){
            $query->where('name','like','%'.$search.'%');
        })->orderBy('id',$sort)->forPage($page,$limit)->get();

        $result = [
            'total' => $total,
            'items' => $list
        ];

        return $this->myResponse($result,'',200);
    }

    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required|unique:region_groups,name'
        ],[
            'title.required' => '组名必填',
            'title.unique' => '组名已经存在',
        ]);

        if($new_id = RegionGroup::create([
            'name'=>$request->title,
        ])->id){
            $new_user = RegionGroup::where('id',$new_id)->first();
            # ======== 记录操作 start ===============
            $desc = "【{$this->admin['name']}】 于 ".date('Y-m-d H:i:s')."【网格】模块【新增】分组【{$new_user['name']}】";
            $this->recordLogs($request,1,$this->admin,$desc);
            # ======== 记录操作 end   ===============

            return $this->myResponse($new_user,'创建成功',200);
        }
        return $this->myResponse([],'创建失败',423);
    }

    public function delete(Request $request)
    {

        $request->validate([
            'id' => 'required|exists:region_groups,id'
        ],[
            'id.required' => 'ID',
            'id.unique' => '组不存在',
        ]);

        //$users = WorkRegion::where('group_id',$request->id)->select('id')->get();
//        if(!empty($users)){
//            //return $this->myResponse([],'',423);
//        }

        $company = RegionGroup::findOrFail($request->id);
        $company->delete();
        //Company::where('id',$request->id)->delete();
        WorkRegion::where('group_id',$request->id)->update(['group_id'=>null]);

        # ======== 记录操作 start ===============
        $desc = "【{$this->admin['name']}】 于 ".date('Y-m-d H:i:s')."【网格】模块【删除】组【{$company['name']}】";
        $this->recordLogs($request,3,$this->admin,$desc);
        # ======== 记录操作 end   ===============
        return $this->myResponse([],'删除成功',200);
    }



    public function group_all(Request $request)
    {
        $list = RegionGroup::select('id','name')->get()->toArray();
        return $this->myResponse($list,'',200);
    }
}
