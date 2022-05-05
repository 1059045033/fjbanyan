<?php


namespace App\Services;
use Log;

class SmsFgService
{

    public function sendsms($mobile,$content,$sign_id,$template_id) {
        $postData = array (
            'apikey'  => config('sms.fg.key'),
            'secret' => config('sms.fg.secret'),
            'content' => $content,
            'mobile' => $mobile,
            'sign_id' => $sign_id,
            'template_id' => $template_id
        );
        $result = $this->curlPost( config('sms.fg.url') ,$postData);
        Log::info('duanxin ==============  '.$result);
        return json_decode($result,1);
    }

    private function curlPost($url,$postFields){
        $postFields = json_encode($postFields);
        $ch = curl_init ();
        curl_setopt( $ch,CURLOPT_URL, $url);
        curl_setopt( $ch,CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8'
            )
        );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt( $ch, CURLOPT_TIMEOUT,1);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec ( $ch );
        curl_close ( $ch );
        return $ret;
    }
}
