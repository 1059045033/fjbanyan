<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\ActivityMsg;
use App\Models\Sms;
use App\Models\User;
use App\Models\VersionControl;
use App\Services\JPushService;
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
        return $this->myResponse(['token'=>$token],'注册成功',200);
    }

    public function login(LoginUserRequest $request)
    {
        // 验证验证码verificationCode
        if(!$sms = Sms::verificationCode($request->code,$request->username)){
            return $this->myResponse([],'验证码错误',423);
        }

        // 验证登入
        if(!$user = User::findForPhone($request->username)){
            return $this->myResponse([],'用户不存在',423);
        };

        User::where('jpush_reg_id',$request->jpush_reg_id)->update(['jpush_reg_id'=>null]);
        $user->jpush_reg_id = empty($request->jpush_reg_id) ? "":$request->jpush_reg_id;
        $user->save();
        // 生成token
        $this->form_params['username'] = $user->phone;
        $this->form_params['password'] = '123456';
        $response = $this->http->post(config('app.url').'/oauth/token',[
            'form_params' => $this->form_params,
        ]);
        //Sms::updateCode($request->code,$request->username);

        $token = json_decode((string) $response->getBody(),true);

        return $this->myResponse(['token'=>$token],'登入成功',200);

    }

    public function version()
    {
            $v = VersionControl::first();
            return $this->myResponse($v,'获取版本信息',200);
    }

    public function banner()
    {
        ActivityMsg::where(['is_show'=>1])->select();
    }

    public function jpush(){

        JPushService::pushNotify([
            'title' => '测试批量',
            'content' => '测试批量',
            //设备标识，跟设备相关
            'reg_id' =>  ['13065ffa4ef8acd9480','18071adc03ff79a6454'],//'13065ffa4ef8acd9480',//1507bfd3f735ffaf601
            'extras' =>  [
                'type'=> 'zidingyi'
            ],
            'type' =>  JPushService::PUSH_TYPE_REG_ID,
        ]);
    }
}
