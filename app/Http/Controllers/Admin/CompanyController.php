<?php

namespace App\Http\Controllers\Admin;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//header('Access-Control-Allow-Origin: *');
class CompanyController extends Controller
{


    public function lists(Request $request)
    {
        $search = $request->query('name');
        $sort = 'asc';
        $fillter = [];
        //$request->query('name') && $fillter['name'] = $request->query('name');

        $request->query('sort') == '-id' && $sort = 'desc';
        $page = $request->query('page') ?? 1;
        $limit = $request->query('limit') ?? 10;

        $total = Company::where($fillter)->when(!empty($search), function ($query) use($search){
            $query->where('name','like','%'.$search.'%');
        })->count();

        $list = Company::select('id','name')->when(!empty($search), function ($query) use($search){
            $query->where('name','like','%'.$search.'%');
        })->orderBy('id',$sort)->forPage($page)->limit($limit)->get();

        $result = [
            'total' => $total,
            'items' => $list
        ];

        return $this->myResponse($result,'',200);
    }

    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required|unique:companies,name'
        ],[
            'title.required' => '公司名字必填',
            'title.unique' => '公司名字已经存在',
        ]);

        if($new_id = Company::create([
            'name'=>$request->title,
        ])->id){
            $new_user = Company::where('id',$new_id)->first();
            return $this->myResponse($new_user,'创建成功',200);
        }
        return $this->myResponse([],'创建失败',423);
    }

    public function delete(Request $request)
    {

        $request->validate([
            'id' => 'required|exists:companies,id'
        ],[
            'id.required' => '公司ID',
            'id.unique' => '公司不存在',
        ]);

        $users = User::where('company_id',$request->id)->select('id')->get();
        if(!empty($users)){
            return $this->myResponse([],'公司下还有人员',200);
        }

        Company::where('id',$request->id)->delete();
        User::where('company_id',$request->id)->update(['company_id'=>null]);

        return $this->myResponse([],'删除成功',200);
    }



    public function company_all(Request $request)
    {
        $list = Company::select('id','name')->get()->toArray();
        return $this->myResponse($list,'',200);
    }
}
