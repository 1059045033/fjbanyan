<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivityMsgRequest;
use App\Http\Requests\UpdateActivityMsgRequest;
use App\Models\ActivityMsg;
use App\Models\User;
use App\Models\WorkingTime;
use App\Models\WorkRegion;
use App\Services\JPushService;
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
        $user = User::with(['company','region:id,name','workRegion:id,name'])->find($request->user()->id);
        if(!empty($request->user_id) && in_array($user['role'],[20,30])){
            $user = User::with(['company','region:id,name','workRegion:id,name'])->find($request->user_id);
        }
        $user['manager_regions'] = null;
        if(!empty($user) && $user['role'] == 20)
        {
            $regions = WorkRegion::where('region_manager',$user['id'])->get()->toArray();
            $user['manager_regions'] = $regions;
        }


        $workingTime = WorkingTime::where('user_id',$user['id'])->select('start_time','end_time')->get()->toArray();

        $user['working_time'] = empty($workingTime) ? null:$workingTime;

        return $this->myResponse($user,'得到用户信息'.$user['id'],200);
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

        $request->image->move(public_path('task_atlas').DIRECTORY_SEPARATOR.date('Ymd'),$imageName);
        $r_path = public_path('task_atlas').date('Ymd').'/'.$imageName;

        $face_url = '/task_atlas/'.date('Ymd').'/'.$imageName;

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

        if(!in_array($user['role'],[20,30])){
            return $this->myResponse([],'只有管理理员才有团队列表',423);
        }

        $region_ids = [];
        if($user['role'] == 20)
        {
            $region_ids = WorkRegion::where('region_manager',$user['id'])->pluck('id')->toArray();
            empty($region_ids) && $region_ids = [-1];
            //

        }

        $res = [];
        $list = User::with(['company','Region:id,name'])
            ->when(!empty($region_ids), function ($query) use($region_ids){
                $query->whereIn('region_id',$region_ids);
            })
            ->whereNotNull('region_id')
            ->where('role',10)
            ->where('id','<>',$user['id'])
            ->select('id as user_id','name','avator','created_at','phone','image_base64','company_id','region_id','role')
            ->get();
        $res['belonging'] = $list;

        $list = User::with(['company','Region:id,name'])
            ->whereNull('region_id')
            ->where('role',10)
            ->select('id as user_id','name','avator','created_at','phone','image_base64','company_id','region_id','role')->get();

        $res['un_belonging'] = $list;

        return $this->myResponse($res,'获取团队列表',200);

    }

    // 安排工作区域
    public function teamJoinWorkRegion(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'region_id' => 'required|exists:work_regions,id',
            'user_id'   => 'required|exists:users,id'
        ]);

        if(!in_array($user['role'],[20,30])){
            return $this->myResponse([],'只有二级或三级才能设置归属区域',423);
        }

        $o_user = User::find($request->user_id);
        if(!empty($o_user->region_id)){
            return $this->myResponse([],'该对象已经设置了归属区域',423);
        }

//        $region = WorkRegion::where('id',$request->region_id)->first();
//        if(empty($region['region_manager'])){
//            return $this->myResponse([],'传的区域还未安排管理人员',423);
//        }
//
//        if(!empty($region['region_manager']) && $region['region_manager']!=$user['id']){
//            return $this->myResponse([],'传的区域管理人员不是你',423);
//        }

        $o_user->region_id = $request->region_id;
        $o_user->save();

        return $this->myResponse([],'归属区域设置成功',200);
    }

    # ===================== 作业安排
    // 作业队伍列表
    // 一级账号: 只返回三级账号人员
    public function workTeams(Request $request)
    {
        $user = $request->user();
        $request->validate([
           // 'region_id' => $user['role'] == 30 ? 'required|exists:work_regions,id':'nullable'
        ]);


        if(!in_array($user['role'],[20,30])){
            return $this->myResponse([],'只有区域管理员/总管理员才能获取对应的工作人员',423);
        }


        //if($user['role']==20 && empty($user['region_id'])){
            //return $this->myResponse([],'区域管理员需先配置所属区域',423);
        //}

        $region_id = [];
        if($user['role']==20){
            // $region_id = $user['region_id'];
            // 获取该区域管理人员的所有区域
            $region_id = WorkRegion::where('region_manager',$user['id'])->pluck('id')->toArray();
        }

        $list = User::with(['company','region:id,name','workRegion:id,name'])
            ->when(!empty($region_id), function ($query) use($region_id){
                $query->whereIn('region_id',$region_id);
            })
            ->where('id','<>',$user['id'])
            ->whereIn('role',[10])
            ->select('id as user_id','name','avator','created_at','phone','company_id','region_id','work_region_id','image_base64')->get();

        return $this->myResponse($list,'获取作业队伍列表',200);
    }

    // 设置工作人员的工作区域
    public function workRegionSet(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'region_id' => 'required|exists:work_regions,id',
            'user_id'   => 'required|exists:users,id'
        ]);


        if(!in_array($user['role'],[20,30])){
            return $this->myResponse([],'只有超级管理员和区域管理员才能操作',423);
        }


        if($user['role'] == 20 && empty($user['region_id'])){
            return $this->myResponse([],'区域管理员要先配置自己的所属区域',423);
        }

        $work_user = User::find($request->user_id);

        if(!empty($work_user->work_region_id)){
            //return $this->myResponse([],'该工作人员已经有工作区域了',423);
        }
        // 如果是区域管理员只能将自己当前得区域设置给工作人员
        $work_region_id = $request->region_id;
        /*if($user['role'] == 20){
            if($request->region_id != $user['region_id']){
                return $this->myResponse([],'区域管理员只能将工作人员安排到自己的区域里',423);
            }
        }*/
        // 超级管理员可以给工作人员设置其他得工作区域

        $work_user->work_region_id = $work_region_id;
        $work_user->save();

        #=========== 设置工作区域 start =========
        if(!empty($work_user->jpush_reg_id))
        {
            JPushService::pushInApp([
                'reg_id' =>  $work_user->jpush_reg_id,
                'extras' =>  [
                    'type' => 1,
                ],
                'type' =>  JPushService::PUSH_TYPE_REG_ID,
            ]);
        }
        #=========== 设置工作区域   end =========

        return $this->myResponse([],'工作人员工作区域设置成功',200);


    }

    // 添加工作人员到区域
    public function usersToWorkRegion(Request $request)
    {
        $user = $request->user();
//        $request->validate([
//            'region_id' => $user['role'] == 30 ? 'required|exists:work_regions,id':'nullable',
//            'user_ids'  => 'required|array'
//        ]);
        $request->validate([
            'region_id' => 'required|exists:work_regions,id',
            'user_ids'  => 'required|array'
        ]);

        if(!in_array($user['role'],[20,30])){
            return $this->myResponse([],'只有区域管理员/总管理员才能设置工作人员工作区域',423);
        }

        if($user['role'] == 20 && empty($user['region_id'])){
            //return $this->myResponse([],'区域管理员要先配置自己的所属区域',423);
        }

        //$user['role']==20 && $region_id = $user['region_id'];
        //$user['role']==30 && $region_id = $request->region_id;

        $region_id = $request->region_id;
        $jpush_reg_ids = [];
        // 找到说有的用户
        foreach ($request->user_ids as $v)
        {
            $temp_user = User::find($v);
//            if($user['role'] == 20 && $temp_user['region_id'] != $region_id)
//            {
//                // 如果当前操作的人是区域管理员 而选择的用户里含有其他区域的工作人 那会跳过针对这些工作人员的工作区域设置
//                continue;
//            }
//            if(!empty($temp_user->work_region_id)){
//                continue;
//            }
            $temp_user->work_region_id = $region_id;
            $temp_user->save();
            !empty($temp_user->jpush_reg_id) && $jpush_reg_ids[] = $temp_user->jpush_reg_id;
        }
        #=========== 设置工作区域 start =========
        if(!empty($jpush_reg_ids->jpush_reg_id)) {
            JPushService::pushInApp([
                'reg_id' => $jpush_reg_ids,
                'extras' => [
                    'type' => 1,
                ],
                'type' => JPushService::PUSH_TYPE_REG_ID,
            ]);
        }
        #=========== 设置工作区域   end =========


        return $this->myResponse([],'工作区域全部设置完成',200);

    }

    // 移除指定区域的工作人员
    public function removeUser(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'region_id' => 'required|exists:work_regions,id',
            'user_ids'  => $request->type == 'all' ? 'nullable':'required|array',
            'type'      => 'required|in:all,assign'
        ]);

        if(!in_array($user['role'],[20,30])){
            return $this->myResponse([],'只有区域管理员/总管理员才能操作',423);
        }
        $msg = '';
        if($request->type == 'all')
        {
            $msg = '清除指定区域下的所有工作人员成功';
            User::where('work_region_id',$request->region_id)->update([
                'work_region_id' => null
            ]);
        }else{
            User::where('work_region_id',$request->region_id)->whereIn('id',$request->user_ids)->update([
                'work_region_id' => null
            ]);
            $msg = '清除指定区域下的指定工作人员成功';
        }


        return $this->myResponse([],$msg,200);
    }

    public function helper()
    {
        $str = '&lt;p style=&quot;text-align:center&quot;&gt;&lt;span style=&quot;font-size:29px;font-family:方正小标宋_GBK&quot;&gt;福州共享单车三方运维人员违规处罚标准&lt;/span&gt;&lt;/p&gt;&lt;p style=&quot;text-align:center&quot;&gt;&lt;span style=&quot;font-size:20px;font-family:仿宋&quot;&gt;（共享单车企业对第三方公司监督）&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;font-size:16px;font-family:楷体_GB2312&quot;&gt;&amp;nbsp;&lt;/span&gt;&lt;/p&gt;&lt;p style=&quot;text-indent:43px&quot;&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;(1)&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;发现车辆摆放不整齐，车辆倾倒情况&lt;span&gt;;&lt;/span&gt;通知第三方人员处理后，&lt;span&gt;15&lt;/span&gt;分钟内未到现场&lt;span&gt;,&lt;/span&gt;一次扣&lt;span&gt;20&lt;/span&gt;元，&lt;span&gt;30&lt;/span&gt;分钟未到扣&lt;span&gt;50&lt;/span&gt;元。&lt;/span&gt;&lt;/p&gt;&lt;p style=&quot;text-indent:43px&quot;&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;(2)&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;三方队员在工作时间内未按规定着装或配备相应清洁用品的，扣&lt;span&gt;100&lt;/span&gt;元&lt;span&gt;/&lt;/span&gt;人&lt;span&gt;/&lt;/span&gt;次。&lt;/span&gt;&lt;/p&gt;&lt;p style=&quot;text-indent:43px&quot;&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;(3)&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;三方队员在工作时间内未按规定配备电动车的，扣&lt;span&gt;30&lt;/span&gt;元&lt;span&gt;/&lt;/span&gt;人&lt;span&gt;/&lt;/span&gt;次。&lt;/span&gt;&lt;/p&gt;&lt;p style=&quot;text-indent:43px&quot;&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;(4)&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;凡是照片无水印的每次&lt;span&gt;50&lt;/span&gt;元扣费，当一工作日上限&lt;span&gt;200&lt;/span&gt;元。手机未按照要求打卡（拍照）或打卡（拍照）不在责任网格区域内的，&lt;span&gt;50&lt;/span&gt;元&lt;span&gt;/&lt;/span&gt;人次。&lt;/span&gt;&lt;/p&gt;&lt;p style=&quot;text-indent:43px&quot;&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;(5)&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;只拍照不整理的，发现一次扣&lt;span&gt;50&lt;/span&gt;元。（故意远离乱象地，找车少地方拍照&lt;span&gt;100&lt;/span&gt;元一次）。&lt;/span&gt;&lt;/p&gt;&lt;p style=&quot;text-indent:43px&quot;&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;(6)&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;对乱象视而不见，看见车辆不规整而故意不进行处理的，其工作态度极其恶劣，扣款&lt;span&gt;100&lt;/span&gt;元&lt;span&gt;/&lt;/span&gt;人&lt;span&gt;/&lt;/span&gt;次。&lt;/span&gt;&lt;/p&gt;&lt;p style=&quot;text-indent:43px&quot;&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;(7)&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;三方工作人员在工作期间每&lt;span&gt;20&lt;/span&gt;分钟至少反馈一组照片&lt;span&gt;(&lt;/span&gt;前后对比照片&lt;span&gt;)&lt;/span&gt;，未按照要求每次扣罚&lt;span&gt;30&lt;/span&gt;元，间隔超过&lt;span&gt;1&lt;/span&gt;小时没有反馈照片的且未提前报备，扣&lt;span&gt;30&lt;/span&gt;元。网格空岗&lt;span&gt;100&lt;/span&gt;元&lt;span&gt;/&lt;/span&gt;天，重点网格空岗&lt;span&gt;140&lt;/span&gt;元&lt;span&gt;/&lt;/span&gt;天。&lt;/span&gt;&lt;/p&gt;&lt;p style=&quot;text-indent:43px&quot;&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;(8)&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;造假，包含制造违规假现场、有照片无轨迹、假水印照片等行为，行为极其恶劣，扣款&lt;span&gt;500&lt;/span&gt;元&lt;span&gt;/&lt;/span&gt;人&lt;span&gt;/&lt;/span&gt;次。&lt;/span&gt;&lt;/p&gt;&lt;p style=&quot;text-indent:43px&quot;&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;(9)90&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;分钟及以上未发送任何照片的，按半天网格空岗处理。&lt;/span&gt;&lt;/p&gt;&lt;p style=&quot;text-indent:43px&quot;&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;(10)&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;三方管理企业未每日提前报备出勤人员情况&lt;span&gt;(&lt;/span&gt;当日早上&lt;span&gt;8&lt;/span&gt;点前），缺一次扣&lt;span&gt;100&lt;/span&gt;元。&lt;/span&gt;&lt;/p&gt;&lt;p style=&quot;text-indent:43px&quot;&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;(11)&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;三方管理企业未每日统计收集其管理对象迟到早退情况、工作照片、工作轨迹等工作内容及违规情况，缺一次扣款&lt;span&gt;100&lt;/span&gt;元，迟于管理公司发现违规情况的按照上述规定扣款。&lt;/span&gt;&lt;/p&gt;&lt;p style=&quot;text-indent:43px&quot;&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;(12)&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;发现违规应第一时间在相关群内汇报。&lt;/span&gt;&lt;/p&gt;&lt;p style=&quot;text-indent:21px&quot;&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;&amp;nbsp;&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;（&lt;span&gt;13&lt;/span&gt;）岗位人员调整必须提前一天，当天调整人员必须有极其特殊情况，如无特殊情况当日调整人员扣&lt;span&gt;50&lt;/span&gt;元一次。&lt;/span&gt;&lt;/p&gt;&lt;p style=&quot;text-indent:43px&quot;&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;(14)&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;以上所产生的扣款费用第三方企业服务费用中扣除。&lt;/span&gt;&lt;/p&gt;&lt;p style=&quot;text-indent:43px&quot;&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;(15)&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;由主管部门发现的问题，按照三倍处罚标准处罚。&lt;/span&gt;&lt;/p&gt;&lt;p style=&quot;text-indent:43px&quot;&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;(16)&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:仿宋&quot;&gt;若在第三方维护区域因第三方工作人员未尽责而导致单车企业被辖区街道罚款，该区域管理的第三方企业承担该罚单的&lt;span&gt;50%&lt;/span&gt;。&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;';
        $str = htmlspecialchars_decode($str);
        return $this->myResponse([
            'content' => $str
        ],"使用帮助",200);
    }

    public function bicycle()
    {
        $str = '&lt;p style=&quot;text-align:center&quot;&gt;&lt;span style=&quot;font-size:29px;font-family:宋体&quot;&gt;福州市&lt;/span&gt;&lt;span style=&quot;font-size:29px;font-family: 宋体&quot;&gt;三方摆放规章&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;font-size:21px&quot;&gt;&amp;nbsp;&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;font-size:21px&quot;&gt;1&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;、负责服务范围内，&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;将&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family: 宋体&quot;&gt;路面上的共享单车摆放整齐至非机动车停车位、并将小范围内的车辆归置在一起。&lt;/span&gt;&lt;span style=&quot;font-size:21px&quot;&gt;(&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;具体摆放标准&lt;/span&gt; &lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;参考市城管委下发的福州市共享单车经营企业经营服务考&lt;/span&gt; &lt;span style=&quot;font-size:21px;font-family: 宋体&quot;&gt;核标准或根据街道办要求酌情处理&lt;/span&gt;&lt;span style=&quot;font-size:21px&quot;&gt;)&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family: 宋体&quot;&gt;。&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;font-size:21px&quot;&gt;2&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;、在进行车辆摆放的同时&lt;/span&gt;&lt;span style=&quot;font-size:21px&quot;&gt;,&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;需将车座和车把龙头上的灰尘、污垢等清洁干净&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;等&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family: 宋体&quot;&gt;。&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;font-size:21px&quot;&gt;3&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;、进行车辆秩序维护的同时如遇到车辆淤积的情况&lt;/span&gt;&lt;span style=&quot;font-size:21px&quot;&gt;,&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;需及时通知&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;在榕&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family: 宋体&quot;&gt;共享单车&lt;/span&gt;&lt;span style=&quot;font-size: 21px;font-family:宋体&quot;&gt;企业将车辆&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;进行转运，&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;font-size:21px&quot;&gt;4&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;、第三方合作企业参与摆车人员统一着装&lt;/span&gt;&lt;span style=&quot;font-size:21px&quot;&gt;,&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;或佩戴明显标&lt;/span&gt; &lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;识&lt;/span&gt;&lt;span style=&quot;font-size:21px&quot;&gt;(&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;如&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;工服或&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family: 宋体&quot;&gt;马甲&lt;/span&gt;&lt;span style=&quot;font-size: 21px&quot;&gt;)&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;。&lt;/span&gt;&lt;span style=&quot;font-size:21px&quot;&gt; &lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;font-size:21px&quot;&gt;5&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;、运营过程中&lt;/span&gt;&lt;span style=&quot;font-size:21px&quot;&gt;,&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;车辆停放朝向为放置在非机动车停车位内&lt;/span&gt;&lt;span style=&quot;font-size:21px&quot;&gt;, &lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;车头统一朝向车道。若停车区紧贴于建筑立面的&lt;/span&gt;&lt;span style=&quot;font-size:21px&quot;&gt;,&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;车头统&lt;/span&gt; &lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;一朝向建筑立面。&lt;/span&gt;&lt;span style=&quot;font-size:21px&quot;&gt; &lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;font-size:21px&quot;&gt;6&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;、每日&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;上边工作&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family: 宋体&quot;&gt;时间为&lt;/span&gt;&lt;span style=&quot;font-size: 21px&quot;&gt; 7 &lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;点至&lt;/span&gt;&lt;span style=&quot;font-size:21px&quot;&gt; 11&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;点，&lt;/span&gt;&lt;span style=&quot;font-size:21px&quot;&gt;15&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family: 宋体&quot;&gt;点至&lt;/span&gt;&lt;span style=&quot;font-size: 21px&quot;&gt; 19 &lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;点。具体点位&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;如有需求&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family: 宋体&quot;&gt;具体调整。&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;font-size:21px&quot;&gt;7&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;、第三方合作企业参与摆车人员留意所在区域地铁&lt;/span&gt;&lt;span style=&quot;font-size:21px&quot;&gt; 1 &lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;号&lt;/span&gt; &lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;线各出入口外侧&lt;/span&gt;&lt;span style=&quot;font-size:21px&quot;&gt; 10 &lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;米范围内&lt;/span&gt;&lt;span style=&quot;font-size:21px&quot;&gt;,&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family: 宋体&quot;&gt;不得停放共享单车。（&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;共享单车专用停车点为除外）&lt;/span&gt; &lt;/p&gt;&lt;p&gt;&lt;span style=&quot;font-size:21px&quot;&gt;8&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;、拍照规范：中场景照片，整理前拍照，整理后往回拍，即拍即上传，形成闭环，每三十分钟必须反馈本人现场工作照片一组，背景应为标志性建筑物。&lt;/span&gt; &lt;span style=&quot;font-size:21px;font-family: 宋体&quot;&gt;如有不规范照片，每张扣罚&lt;/span&gt;&lt;span style=&quot;font-size:21px&quot;&gt;1&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family: 宋体&quot;&gt;元，不设上限；&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span style=&quot;font-size:21px&quot;&gt;9&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;、发现有车辆堆积现象（在四城区范围），如能拍照核实当事人，予以奖励&lt;/span&gt;&lt;span style=&quot;font-size:21px&quot;&gt;50&lt;/span&gt;&lt;span style=&quot;font-size:21px;font-family:宋体&quot;&gt;元。&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;span&gt;&amp;nbsp;&lt;/span&gt;&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;';
        $str = htmlspecialchars_decode($str);
        return $this->myResponse([
            'content' => $str
        ],"单车课堂",200);
    }

    public function safeEducation()
    {
        $res = [
            ['id'=>1,'video_cover'=>config('app.url')."/safe/safe.png",'name'=>'消防安全警示片','content'=>'消防教育培训','url'=>config('app.url')."/safe/safe.mp4"],
            ['id'=>2,'video_cover'=>config('app.url')."/safe/zhudun.png",'name'=>'交通安全警示片','content'=>'安全教育培训','url'=>config('app.url')."/safe/zhudun.mp4"],
        ];

        return $this->myResponse($res,"安全课堂",200);
    }
}
