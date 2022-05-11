<?php

namespace App\Http\Controllers;

use App\Models\OptRecord;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function myResponse($data = null ,$message = '' , $code){
        return response()->json([
            'code'   => $code,
            'message'=> $message,
            'data'=>$data
        ]);
    }


    public function recordLogs($requert,$type=null,$admin,$desc=''){
        return OptRecord::create([
            'opt_user_id'   => $admin['id'],
            'opt_user_name' => $admin['name'],
            'ip' => empty($requert->ip()) ? null:$requert->ip(),
            'agent' => $requert->header('User-Agent'),
            'type' => $type,
            'desc' => $desc
        ])->id;
    }
}
