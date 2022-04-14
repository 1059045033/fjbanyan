<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Http\Requests\StoreTrackRequest;
use App\Http\Requests\UpdateTrackRequest;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function __construct()
    {
        // 对数据进行处理 处理完就可以拿到用户信息
        $this->middleware('auth:api');//->except(['show','index']);;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'position'=> 'required|array',
            'address' => 'required'
        ]);

        $track_id = Track::create([
                'user_id'           => $user['id'],
                'position'          => json_encode($request->position),
                'address'           => $request->address,
            ])->id;

        return $this->myResponse(['track_id'=>$track_id],'轨迹创建成功',200);
    }


    public function userHistory(Request $request)
    {
        $user = $request->user();
        $list = Track::getlist($request->all(),$user['id']);
        return $this->myResponse($list,'轨迹列表',200);
    }

}
