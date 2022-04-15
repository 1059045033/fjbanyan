<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivityMsgRequest;
use App\Http\Requests\UpdateActivityMsgRequest;
use App\Models\ActivityMsg;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class MemberController extends Controller
{

    public function __construct()
    {
        // 对数据进行处理 处理完就可以拿到用户信息
        $this->middleware('auth:api');
    }

    // 上传
    public function store(Request $request)
    {
        $user = User::with('company')->find($request->user()->id);//$request->user()->with('company');
        if(!empty($user['image_base64'])){
            $user['image_base64'] = config('app.url').$user['image_base64'];
        }

        return $this->myResponse($user,'得到用户信息',200);
    }

    // 上传人脸
    public function uploadeFace(Request $request){
        $user = $request->user();
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);
        $imageName = $user['id'].'.'.$request->image->extension();
        $request->image->move(public_path('faces'),$imageName);

        $face_url = '/faces/'.$imageName;
        $res = User::where(['id'=>$user['id']])->update(['image_base64'=>$face_url]);

        if(!empty($res)){
            return $this->myResponse(['face_url' => config('app.url').$face_url],'更新头像成功',200);
        }else{
            return $this->myResponse([],'更新头像失,败稍后再试',423);
        }
    }

    // 上传图片
    public function uploadeImage(Request $request){
        $user = $request->user();
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'type'  => 'required|in:avator,online,task_atlas'
        ]);

        //'status'   => 'required'.($request->input('type') == 'all' ? '':($request->input('type') == 'iscross' ? '|in:-1,0,1,2,3':'|in:0,10,20,30')),
        $code = str_pad(mt_rand(10, 999999), 6, "0", STR_PAD_BOTH);
        $imageName = $user['id'].'_'.$code.'_'.time().'.'.$request->image->extension();

        $request->image->move(public_path('task_atlas'),$imageName);
        $r_path = public_path('task_atlas').$imageName;

        $face_url = '/task_atlas/'.$imageName;

        return $this->myResponse([
            'url' => config('app.url').$face_url,
            'path' => $face_url
        ],'图片上传成功',200);


//        if(file_exists($r_path)){
//            return $this->myResponse([
//                'url' => config('app.url').$face_url,
//                'path' => $face_url
//                ],'图片上传成功',200);
//        }else{
//            return $this->myResponse([],'图片上传失败,败稍后再试',423);
//        }
    }

    // 团队列表
    public function teamList(Request $request)
    {
        $user = $request->user();

        if(!in_array($user['role'],[20])){
            return $this->myResponse([],'只有区域管理员才有团队列表',423);
        }

        if(empty($user['region_id'])){
            return $this->myResponse([],'还未配置所属区域',423);
        }
        $res = [];
        $list = User::with('company')->where(['region_id'=> $user['region_id']])->where('id','<>',$user['id'])
            ->select('id as user_id','name','avator','created_at','phone','company_id')->get();
        $res['belonging'] = $list;

        $list = User::with('company')->whereNull('region_id')->where('id','>',1)
            ->select('id as user_id','name','avator','created_at','phone','company_id')->get();

        $res['un_belonging'] = $list;

        return $this->myResponse($res,'获取团队列表',200);

    }

    // 安排工作区域
    public function teamJoinWorkRegion(Request $request)
    {
        $user = $request->user();
        $request->validate([
            //'region_id' => 'required|exists:work_regions,id',
            'user_id'   => 'required|exists:users,id'
        ]);
        if(!in_array($user['role'],[20])){
            return $this->myResponse([],'只有区域管理员才能设置归属区域',423);
        }
        $o_user = User::find($request->user_id);
        if(!empty($o_user->region_id)){
            return $this->myResponse([],'该对象已经设置了归属区域',423);
        }

        $o_user->region_id = $user['region_id'];
        $o_user->save();

        return $this->myResponse([],'归属区域设置成功',200);
    }



}
