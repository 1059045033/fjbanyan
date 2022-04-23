<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{

    public function login(Request $request)
    {

        $request->validate([
            'username'  => 'required',
            'password'  => 'required'
        ]);


        //echo
        $admin = Admin::where([
            'name'      => $request->username,
        ])->first();

        if(!empty($admin)){
            if(password_verify($request->password,$admin->password)){
                $admin->remember_token = 'admin-token1314626';
                $admin->save();
                return $this->myResponse(["token" => $admin->remember_token],'登入成功',200);
            }else{
                return $this->myResponse([],'密码错误',423);
            }
        }else{
            return $this->myResponse([],'用户不存在',423);
        }
    }

    public function info(Request $request)
    {
        $request->validate([
            'token'  => 'required',
        ]);
        $admin = Admin::where(['remember_token'=>$request->token])->first();
        if(!empty($admin)){
            $data = [
                'avatar' =>config('app.url').$admin->avator,
                'introduction' => 'I am a super administrator',
                'name' =>$admin->name,
                'roles' => ['admin'],
            ];
            return $this->myResponse($data,'获取用户信息成功',200);
        }else{
            return $this->myResponse([],'用户不存在',423);
        }
    }

    public function logout(Request $request)
    {

        $request->validate([
            'token'  => 'required',
        ]);
        $admin = Admin::where(['remember_token'=>$request->token])->first();
        if(!empty($admin)){
            $admin->remember_token = '';
            return $this->myResponse([],'退出成功',200);
        }else{
            return $this->myResponse([],'用户不存在',423);
        }
    }








}
