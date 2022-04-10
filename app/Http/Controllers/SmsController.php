<?php

namespace App\Http\Controllers;

use App\Models\Sms;
use App\Http\Requests\StoreSmsRequest;
use App\Http\Requests\UpdateSmsRequest;
use Illuminate\Support\Facades\DB;

class SmsController extends Controller
{

    public function send(StoreSmsRequest $request)
    {
        $sms = Sms::where(['mobile'=>$request->phone,'type'=>$request->type])->where('expire_time','>',time())->first();
        if(!empty($sms)){
            //return $this->myResponse([],'该号码已经请求过了，请稍后再请求',423);
        }
        Sms::where(['mobile'=>$request->phone,'type'=>$request->type])->delete();
        // 产生验证码
        $code = str_pad(mt_rand(10, 999999), 6, "0", STR_PAD_BOTH);

        // 发送验证码
        // 。。。。。 发送代码
        #================= 发送短信 start ========================
        $mobile=$request->phone;
        $url="http://www.ztsms.cn/sendNSms.do";
        $tKey = date('YmdHis');
        $password = md5(md5("Jin889%w") . $tKey);

        $data = array(
            'content' => "您的验证码是{$code},72小时内有效【Test】",
            'mobile' => $mobile,
            'productid' => '676767',//产品id
            'xh' => '',//小号
            'username' => "zzh_zzh",
            'tkey' => $tKey,
            'password' => $password
        );

//        $phoneCode=SmsCode::where([
//            'phone'=>$mobile,
//        ])->first();
//
//        if(!empty($phoneCode)){
//            if(time() > $phoneCode['expire_time']){
//                SmsCode::destroy($phoneCode['id']);
//            }else{
//                echo "稍后再发";die;
//            }
//        }

        $headers = array('Content-Type: application/x-www-form-urlencoded');
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data)); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($curl); // 执行操作
        $msg = '';
        $result_code = 200;
        if (curl_errno($curl)) {
            $result_code = 423;
            $msg = 'Errno'.curl_error($curl);
        }else{
            $msg = '短信发送成功';
        }
        curl_close($curl); // 关闭CURL会话
        #================= 发送短信 end   ========================


        // 发送成功 记录验证码
        if($result_code == 200){
            Sms::create([
                'code' => $code,
                'expire_time'=> time()+(60 * 60 * 72),
                'mobile' => $request->phone,
                'type' => $request->type
            ]);
            return $this->myResponse(['code'=>$code],$msg,$result_code);
        }
        return $this->myResponse([],$msg,$result_code);
    }
}
