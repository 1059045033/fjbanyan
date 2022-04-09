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

    // 獲取
    public function store(Request $request)
    {
        $user = $request->user();

        $user['image_base64'] = config('app.url').$user['image_base64'];
        return $this->myResponse($user,'得到用户信息',200);
    }




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



}
