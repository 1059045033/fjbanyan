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
                $admin->remember_token = md5('admin-'.$admin['id']);
                $admin->save();

                # ======== 记录操作 start ===============
                $desc = "【{$admin['name']}】 在 ".date('Y-m-d H:i:s')."【登入】";
                $this->recordLogs($request,4,$admin,$desc);
                # ======== 记录操作 end   ===============

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
        $admin = Admin::with('company')->where(['remember_token'=>$request->token])->first();
        if(!empty($admin)){
            $data = [
                'avatar' =>config('app.url').$admin->avator,
                'introduction' => '一个管理者',
                'name' =>$admin->name,
                'company' =>$admin->company,
                'roles' => ['admin'],
            ];
            return $this->myResponse($data,'获取用户信息成功',200);
        }else{
            return $this->myResponse([],'用户不存在',423);
        }
    }

    public function logout(Request $request)
    {

        $token = $request->header('X-Token');
        $admin = Admin::where(['remember_token' => $token])->first();
        if(!empty($admin)){
            $admin->remember_token = '';
            $admin->save();

            # ======== 记录操作 start ===============
            $desc = "【{$admin['name']}】 在 ".date('Y-m-d H:i:s')."【退出】";
            $this->recordLogs($request,5,$admin,$desc);
            # ======== 记录操作 end   ===============

            return $this->myResponse([],'退出成功',200);
        }else{
            return $this->myResponse([],'用户不存在',423);
        }
    }
    public function update(Request $request)
    {

        $request->validate([
            'password'  => 'required',
        ]);

        $token = $request->header('X-Token');
        $admin = Admin::where(['remember_token' => $token])->first();

        if(!empty($admin)){
            //$admin->remember_token = '';
            $admin->password = bcrypt($request->password);
            $admin->save();

            # ======== 记录操作 start ===============
            $desc = "【{$admin['name']}】 在 ".date('Y-m-d H:i:s')."【修改密码】";
            $this->recordLogs($request,6,$admin,$desc);
            # ======== 记录操作 end   ===============

            return $this->myResponse([],'更新成功',200);
        }else{
            return $this->myResponse([],'用户不存在',423);
        }
    }









}
