<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\ActivityMsg;
use App\Models\Sms;
use App\Models\User;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    private $http;
    private $form_params;


    public function __construct(Guzzle $http)
    {
        $this->http = $http;
        $this->form_params = config('auth.form_params');
    }

    public function register(RegisterUserRequest $request)
    {
        $user = User::create([
            'name'  => $request->name,
            'email' => $request->email,
            'password' => bcrypt('123456'),
            'phone' => $request->phone,
            'image_base64'=>''
        ]);

        $this->form_params['username'] = $user->phone;
        $this->form_params['password'] = '123456';
        $response = $this->http->post(config('app.url').'/oauth/token',[
            'form_params' => $this->form_params,
        ]);

        $token = json_decode((string) $response->getBody(),true);
        return response()->json([
            'token'=>$token
        ],201);
    }

    public function login(LoginUserRequest $request)
    {
        // 验证验证码verificationCode
        if(!$sms = Sms::verificationCode($request->code,$request->username)){
            return response()->json([
                'data'=>[],
                'message'=>'验证码错误'
            ],423);
        }

        // 验证登入
        if(!$user = User::findForPhone($request->username)){
            return response()->json([
                'data'=>[],
                'message'=>'用户不存在'
            ],404);
        };


        // 生成token
        $this->form_params['username'] = $user->phone;
        $this->form_params['password'] = '123456';
        $response = $this->http->post(config('app.url').'/oauth/token',[
            'form_params' => $this->form_params,
        ]);
//        Sms::updateCode($request->code,$request->username);
        $token = json_decode((string) $response->getBody(),true);
        return response()->json([
            'token'=>$token
        ],200);
    }

    public function banner()
    {
        ActivityMsg::where(['is_show'=>1])->select();
    }
}
