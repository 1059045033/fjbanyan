<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\WorkRegion;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AttendanceLog extends Command
{

    protected $signature = 'attendance:record {--day=}';

    protected $description = '记录当天的出勤记录';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        # 获取参数 日期参数
        $date_day = empty($this->option('day')) ? date('Y-m-d'):$this->option('day');

        DB::transaction(function () use ($date_day){
            # 获取公司数据
            $companies = DB::table('companies')->pluck('name','id')->toArray();
            # 获取网格数据
            $regions   = DB::table('work_regions')->pluck('name','id')->toArray();
            # 当前时间戳
            $time      = time();
            # 查找所有的三级和二级人员
            $users = DB::table('users')
                ->whereIn('role',[10,20])
                ->where('id',14)
                ->select('id','name','phone','company_id','region_id','work_region_id','role')
                ->get()
                ->each(function ($data,$key) use($companies,$regions){
                    $data->user_company = empty($companies[$data->company_id]) ? null:$companies[$data->company_id];
                    $data->user_region = empty($regions[$data->region_id]) ? null:$regions[$data->region_id];
                    $data->user_work_region = empty($regions[$data->work_region_id]) ? null:$regions[$data->work_region_id];
                });

            # 日期的起止时间戳
            $day   = empty($date_day) ? date('Y-m-d'):$date_day;
            $start = Carbon::parse($day)->startOfDay()->timestamp;
            $end   = Carbon::parse($day)->endOfDay()->timestamp;

            # 用户的考勤数据集合
            $usersDatas = [];
            foreach ($users as $k=>$v){
                $money      = 0;
                $money_desc = '';
                # 用户的基础数据集合
                $usersDatas[$v->id] = [
                    'user_id'      => $v->id,
                    'user_name'    => $v->name,
                    'user_phone'   => $v->phone,
                    'user_company' => empty($v->company_id) ? null:$v->user_company."(ID:{$v->company_id})",
                    'user_role'    => $v->role,
                    'user_region'  => empty($v->region_id) ? null: $v->user_region."(ID:{$v->region_id})",
                    'user_work_region' => empty($v->work_region_id) ? null: $v->user_work_region."(ID:{$v->work_region_id})",
                    'created_at'   => $time,
                    'updated_at'   => $time
                ];

                # ================  考勤的人员是 二级人员 start ============
                $region_not_user_nums  = 0;   // 无网格出勤次数
                $region_not_user_money = 140; // 每次扣款的金额
                if($v->role == 20){
                    // 1.找到这个 管理人员下所有的 区域
                    $region_ids = WorkRegion::where('region_manager',$v->id)->pluck('id');
                    if(!empty($region_ids))
                    {
                        // 2. 看看每个区域是否都作为工作区域安排给了人员
                        foreach ($region_ids as $vvv)
                        {
                            $temp = User::where('work_region_id',$vvv)->count();
                            // 3. 每存在一个区域没有没有作为工作区域安排给人员 计数就 +1
                            empty($temp) && $region_not_user_nums++;
                        }

                        $usersDatas[$v->id]['online_times']       = null;
                        $usersDatas[$v->id]['offline_times']      = null;
                        $usersDatas[$v->id]['task_complete_nums'] = null;
                        $usersDatas[$v->id]['task_progress']      = null;
                        $usersDatas[$v->id]['late_nums']          = null;
                        $usersDatas[$v->id]['early_nums']         = null;
                        $usersDatas[$v->id]['task_dd_nums']       = null;

                        $money += $region_not_user_nums * $region_not_user_money;
                        $money_desc .= "【网格无人员出勤{$region_not_user_nums}次共扣".($region_not_user_nums * $region_not_user_money)."元】";
                        $usersDatas[$v->id]['region_not_user_nums'] = $region_not_user_nums;
                        $usersDatas[$v->id]['money'] = $money;
                        $usersDatas[$v->id]['money_details'] = $money_desc;
                    }
                    continue;
                }
                # ================  考勤的人员是 二级人员 start ============
                # ================  考勤的人员是 三级人员 start ============
                /*if(empty($v->work_region_id)){
                    // 没有安排工作区

                    $usersDatas[$v->id]['online_times'] = null;
                    $usersDatas[$v->id]['offline_times'] = null;
                    $usersDatas[$v->id]['task_complete_nums'] = null;
                    $usersDatas[$v->id]['task_progress'] = null;

                    $usersDatas[$v->id]['late_nums'] = 2;
                    $usersDatas[$v->id]['early_nums'] = 2;
                    $usersDatas[$v->id]['task_dd_nums'] = 8;
                    $usersDatas[$v->id]['region_not_user_nums'] = null;
                    $usersDatas[$v->id]['money'] = (4*30) + 8*30;
                    $money_desc = "没有安排工作区域:算迟到2次(2x30)|算早退2次(2x30)|算断档8次(8x30)";
                    $usersDatas[$v->id]['money_details'] = $money_desc;
                }else{*/
                    // 当天所有的上线时间集合
                    $online_times = $this->onOrOffLineTimes($v->id,1,$start,$end);
                    $usersDatas[$v->id]['online_times'] = empty($online_times) ? null:json_encode($online_times);

                    // 当天所有的下线时间集合
                    $offline_times = $this->onOrOffLineTimes($v->id,2,$start,$end);
                    $usersDatas[$v->id]['offline_times'] = empty($offline_times) ? null:json_encode($offline_times);

                    // 任务完成量/任务完进度
                    $this->taskDatas($usersDatas,$v->id,$start,$end);

                    // 不算这这个
                    $usersDatas[$v->id]['late_nums']  = null;
                    $usersDatas[$v->id]['early_nums'] = null;

                    //
                    $kaoqin = $this->countQueqinAndDuandang($v->id,$start,$end);
                    dd($kaoqin);
//                    $money += $kaoqin['money'];
//                    $money_desc .= '【'.$kaoqin['desc']."】";
//
//                    if(empty($kaoqin['code'])){
//                        $duandang = $this->countDuandang($v->id,$start,$end);
//                        $money += $duandang['money'];
//                        $money_desc .= '【'.$duandang['desc']."】";
//                        $usersDatas[$v->id]['task_dd_nums'] = $duandang['nums'];
//                    }else{
//                        $usersDatas[$v->id]['task_dd_nums'] = null;
//                    }

                    $usersDatas[$v->id]['region_not_user_nums'] = null;
                    $usersDatas[$v->id]['money'] = $money;
                    $usersDatas[$v->id]['money_details'] = $money_desc;
                //}
                # ================  考勤的人员是 三级人员 end   ============

            }

            dd($usersDatas);

            // 插入考勤记录
            //$res = DB::table('attendances')->insert($usersDatas);

        });
        return 0;
    }

    // 计算有效上班时间
    public function countWorkingtimes($user_id,$start,$end){

        # 1. 获取这个用户所有的考勤时间列表
        $kaoqing_times = DB::table('online_offlines')->where(['user_id'=>$user_id])
            ->whereBetWeen('created_at',[$start,$end])->select('id','type','created_at')
            ->orderBy('created_at','asc')->get()->toArray();

        # 2. 获取当前人员的上班时间集合
        $working_times = DB::table('working_times')->where('user_id',$user_id)->get()->toArray();

        # 3. 该人员没有安排班
        if(empty($working_times))
        {
            // 该工作人员没有排班信息 -- 这个要怎么处理
            return [
                'code' => 400,
                'nums'=>null,
                'money'=> 0,
                'desc'=>"没有安排上下班时间",
            ];
        }

        # 4. 是否没有考勤信息
        if(empty($kaoqing_times)){
            $n = count($working_times);
            return [
                'code' => 400,
                'nums'=>null,
                'money'=> $n * 70,
                'desc'=>"一天无考勤,扣款".($n * 70)."元",
            ];
        }

        # 班次的时间集合
        $banci_data_array = [];
        foreach ($working_times as $k=>$v)
        {
            # 获取上一个班次的结束时间
            if($k == 0)
            {
                $pre_end_time = strtotime(date('Y-m-d 00:00:00',$start));
            }else{
                $pre_end_time = strtotime(date('Y-m-d '.$working_times[($k-1)]->end_time.':00',$start));
            }

            # 当前班次的上班时间
            $start_time   = strtotime(date('Y-m-d '.$v->start_time.':00',$start));
            # 当前班次的下班时间
            $end_time     = strtotime(date('Y-m-d '.$v->end_time.':00',$start));

            # 获取 [上个班次的下班时间 ~ 当前班次的上班时间] 最接近[当前班次的上班时间]近的一个 [上班时间]
            $temp_nearest_left_online = $this->countNearestForLeft($kaoqing_times,$start_time,$pre_end_time);

            foreach ($kaoqing_times as $kk=>$vv)
            {

                $item = $vv;
                if(empty($temp_nearest_left_online))
                {
                    if($item->created_at > $start_time && $item->created_at <= $end_time){
                        $item->online_time = $start_time;
                        $item->offline_time = $end_time;
                        $banci_data_array[$k][]=$item;
                    }
                }else{
                    if($item->created_at >= $temp_nearest_left_online->created_at && $item->created_at <= $end_time){
                        if($item->created_at == $temp_nearest_left_online->created_at)
                        {
                            $item->created_at = $start_time;
                            $item->online_time = $start_time;
                            $item->offline_time = $end_time;
                            $banci_data_array[$k][]=$item;
                        }else{
                            $item->online_time = $start_time;
                            $item->offline_time = $end_time;
                            $banci_data_array[$k][]=$item;
                        }
                    }
                }
            }
        }
        # 从得到的时间集合中计算每个班次的有效工作时长
        $workting_time_start_end = [];


        foreach ($banci_data_array as $k=>$v)
        {
            $online_time = 0;// 上线时间
            $name = $working_times[$k]->name;
            $working_time_id = $working_times[$k]->id;
            foreach ($v as $kk=>$vv)
            {

                if(empty($online_time)){
                    if($vv->type == 1)
                    {
                        $online_time = $vv->created_at;
                    }
                }else{
                    if($vv->type == 2)
                    {
                        if(!empty($online_time))
                        {
                            $workting_time_start_end[] = [
                                'online_time'=>$online_time,
                                'offline_time'=>$vv->created_at,
                                'name'=>$name,
                                'working_time_id'=>$working_time_id,
                                'start_time'=>$vv->online_time,
                                'end_time'=>$vv->offline_time,
                            ];
                            $online_time = 0;
                        }
                    }
                }
            }
            $last_time = array_pop($v);
            if($last_time->created_at <= $last_time->offline_time && $last_time->type==1){
                $workting_time_start_end[] = [
                    'online_time'=>$online_time,
                    'offline_time'=>$last_time->offline_time,
                    'name'=>$name,
                    'working_time_id'=>$working_time_id,
                    'start_time'=>strtotime(date('Y-m-d '.$working_times[$k]->start_time.':00',$start)), //$working_times[$k]->start_time,
                    'end_time'=>strtotime(date('Y-m-d '.$working_times[$k]->end_time.':00',$start)),//$working_times[$k]->end_time,
                ];
            }
        }

        $new_data = [];
        $effective_ids = [];
        foreach ($workting_time_start_end as $k=>$v)
        {
            $new_data['effective'][$v['working_time_id']][] = $v;
            if(!in_array($v['working_time_id'],$effective_ids))
            {
                $effective_ids[]=$v['working_time_id'];// 把存在有效上下班时间的先记录下来  用于后的使用
            }
        }

        empty($new_data['effective']) &&  $new_data['effective'] = [];

        if(count($new_data['effective']) == count($working_times)){
            $new_data['un_effective'] = [];
        }else{
            foreach ($working_times as $k=>$v){
                if(!in_array($v->id,$effective_ids)){
                    # 当前班次的上班时间
                    $start_time   = strtotime(date('Y-m-d '.$v->start_time.':00',$start));
                    # 当前班次的下班时间
                    $end_time     = strtotime(date('Y-m-d '.$v->end_time.':00',$start));
                    $diff_second = $end_time - $start_time;
                    if($diff_second > (5 * 60) && $diff_second <= (30*60))
                    {
                        $money = 15;
                    }elseif ($diff_second > (30*60) && $diff_second <= (60*60))
                    {
                        $money = 30;
                    }elseif ($diff_second > (60*60) && $diff_second <= (90*60))
                    {
                        $money = 45;
                    }elseif ($diff_second > (90*60) && $diff_second <= (180*60))
                    {
                        $money = 60;
                    }elseif ($diff_second > (180*60)){
                        $money = 70;
                    }

                    $tt = $this->descTime($diff_second);
                    $desc = "{$v->name}缺勤 :{$tt}扣款{$money}元,";
                    $new_data['un_effective'][] = [
                            'nums' => null,
                            'money' => $money,
                            'desc' => $desc
                    ];
                }
            }
        }


        return $new_data;

    }

    // 计算缺勤和断档
    public function countQueqinAndDuandang($user_id,$start,$end)
    {
        $datas = $this->countWorkingtimes($user_id,$start,$end);
        if(!empty($datas['code']) && $datas['code'] == 400)
        {
            return $datas;
        }

        $new_data = $datas['effective'];
        $total_money = 0;
        $desc = "";
        $count = 0;

        foreach ($new_data as $k=>$v)
        {
            $currt_working_time_total_long = 0;      // 当前班次总时长
            $currt_working_time_effective_long = 0;  // 当前班次有效工作时长
            $currt_working_time_qq_total_long = 0;   // 当前班次缺勤总时长
            $currt_working_time_name = '';           // 当前班次名称
            $currt_working_time_dd_total_long = '';  // 当前班次断档总时长

            // 考勤 计算
            foreach ($v as $k_k=>$v_v)
            {
                if($k_k == 0){
                    $currt_working_time_total_long = ($v_v['end_time'] - $v_v['start_time']); // 上班总时长
                    $currt_working_time_name = $v_v['name'];     //班次名称
                }

                $currt_working_time_effective_long += ($v_v['offline_time'] - $v_v['online_time']); //班次的有效时间
            }
            $diff_second = $currt_working_time_total_long - $currt_working_time_effective_long;
            $currt_working_time_qq_total_long = $diff_second;
            $desc .= "【".$currt_working_time_name."缺勤:".$this->descTime($diff_second).",";

            // 任务断档计算
            foreach ($v as $kk=>$vv)
            {

                $d = DB::table('task_logs')->where(['user_id'=>$user_id])
                    ->whereBetWeen('created_at',[$vv['online_time'],$vv['offline_time']])
                    ->orderBy('created_at','asc')->pluck('created_at')->toArray();

                if(!in_array($vv['online_time'],$d)){
                    array_unshift($d, $vv['online_time']);
                }
                if(!in_array($vv['offline_time'],$d)){
                    array_push($d,$vv['offline_time']);
                }
                $old_times = 0;
                foreach ($d as $kkk=>$vvv)
                {
                    if($old_times == 0 ){
                        $old_times = $vvv;
                    }else{
                        $diff_second = $vvv - $old_times;
                        if($diff_second > (30*60))
                        {
                            $currt_working_time_dd_total_long += $diff_second;
                            $desc .= "断档:".$this->descTime($diff_second)."(".date('Y-m-d H:i:s',$old_times)."~".date('Y-m-d H:i:s',$vvv)."),";
                        }
                        $old_times = $vvv;
                    }
                }
            }

            $money1 = $this->countQqMoney($currt_working_time_qq_total_long);
            $money2 = $this->countDdMoney($currt_working_time_dd_total_long);
            $money  = $money1 + $money2;
            if($money > 70 ){
                $money = 70;
            }
            $total_money += $money;
            $desc .= "共扣款".$money."】";
        }


        #======= 补充没有打卡的班次 ==========
        if(!empty($datas['un_effective']))
        {
            foreach ($datas['un_effective'] as $k=>$v)
            {
                $total_money += $v['money'];
                $desc .= "【".$v['desc']."】";
            }
        }
        #======= 补充没有打卡的班次 ==========

        return [
            'nums'=>null,
            'money'=>$total_money,
            'desc'=>$desc,
        ];

    }

    // 缺勤计算
    public function countQqMoney($diff_second)
    {
        $money = 0;
        if($diff_second > (5 * 60) && $diff_second <= (30*60))
        {
            $money = 15;
        }elseif ($diff_second > (30*60) && $diff_second <= (60*60))
        {
            $money = 30;
        }elseif ($diff_second > (60*60) && $diff_second <= (90*60))
        {
            $money = 45;
        }elseif ($diff_second > (90*60) && $diff_second <= (180*60))
        {
            $money = 60;
        }elseif ($diff_second > (180*60)){
            $money = 70;
        }
        return $money;
    }
    // 断档计算
    public function countDdMoney($diff_second)
    {
        $money = 0;
        if($diff_second > (30*60))
        {
            $money = 0;
            if ($diff_second <= (60*60))
            {
                $money = 15;
            }elseif ($diff_second > (60*60) && $diff_second <= (90*60))
            {
                $money = 30;
            }elseif ($diff_second > (90*60) && $diff_second <= (180*60))
            {
                $money = 60;
            }elseif ($diff_second > (180*60)){
                $money = 70;
            }
        }
        return $money;
    }


    public function countDuandang($user_id,$start,$end){
        $datas = $this->countWorkingtimes($user_id,$start,$end);
        if(!empty($datas['code']) && $datas['code'] == 400)
        {
            return $datas;
        }

        $new_data = $datas['effective'];

        $total_money = 0;
        $desc = "";
        $count = 0;
        foreach ($new_data as $k=>$v)
        {
            $money = 0;
            $time  = 0;
            $name  = empty($v[0]['name']) ? "":$v[0]['name'];
            echo "================{$k} ======================\n";
            foreach ($v as $kk=>$vv)
            {
                echo "================{$name}======================\n";
                $d = DB::table('task_logs')->where(['user_id'=>$user_id])
                    ->whereBetWeen('created_at',[$vv['online_time'],$vv['offline_time']])
                    ->orderBy('created_at','asc')->pluck('created_at')->toArray();

                if(!in_array($vv['online_time'],$d)){
                    array_unshift($d, $vv['online_time']);
                }
                if(!in_array($vv['offline_time'],$d)){
                    array_push($d,$vv['offline_time']);
                }
                $old_times = 0;
                $nums = 0;
                foreach ($d as $kkk=>$vvv)
                {
                    if($old_times == 0 ){
                        $old_times = $vvv;
                    }else{
                        $diff_second = $vvv - $old_times;
                        if($diff_second > (30*60))
                        {
                            $money = 0;
                            if ($diff_second <= (60*60))
                            {
                                $money = 15;
                            }elseif ($diff_second > (60*60) && $diff_second <= (90*60))
                            {
                                $money = 30;
                            }elseif ($diff_second > (90*60) && $diff_second <= (180*60))
                            {
                                $money = 60;
                            }elseif ($diff_second > (180*60)){
                                $money = 70;
                            }

                            $total_money += $money;
                            $tt = $this->descTime($diff_second);
                            $count ++;
                            $desc .= "{$name}断档{$tt}(".date('Y-m-d H:i:s',$old_times)."~".date('Y-m-d H:i:s',$vvv)."),扣款{$money}元,";
                        }
                        $old_times = $vvv;
                    }
                }
            }
        }



        return [
            'nums'=>$count,
            'money'=>$total_money,
            'desc'=>$desc,
        ];
    }
    // 缺勤计算
    public function countQueqin($user_id,$start,$end){

        $datas = $this->countWorkingtimes($user_id,$start,$end);

        if(!empty($datas['code']) && $datas['code'] == 400)
        {
            return $datas;
        }

        $new_data = $datas['effective'];

        $total_money = 0;
        $desc = "";
        foreach ($new_data as $k=>$v)
        {
            $money = 0;
            $time  = 0;
            $time_total =0;
            $name  = "";

            foreach ($v as $k_k=>$v_v)
            {
                if($k_k == 0){
                    $time_total = ($v_v['end_time'] - $v_v['start_time']); // 上班总时长
                    $name = $v_v['name']; //班次名称
                }

                $time += ($v_v['offline_time'] - $v_v['online_time']); //班次的有效时间
            }
            $diff_second = $time_total - $time;
            if($diff_second > (5 * 60) && $diff_second <= (30*60))
            {
                $money = 15;
            }elseif ($diff_second > (30*60) && $diff_second <= (60*60))
            {
                $money = 30;
            }elseif ($diff_second > (60*60) && $diff_second <= (90*60))
            {
                $money = 45;
            }elseif ($diff_second > (90*60) && $diff_second <= (180*60))
            {
                $money = 60;
            }elseif ($diff_second > (180*60)){
                $money = 70;
            }

            $total_money += $money;
            $tt = $this->descTime($diff_second);
            $desc .= "{$name}缺勤:{$tt}扣款{$money}元,";
        }


        #======= 补充没有打卡的班次 ==========
        if(!empty($datas['un_effective']))
        {
            foreach ($datas['un_effective'] as $k=>$v)
            {
                $total_money += $v['money'];
                $desc .= $v['desc'];
            }
        }
        #======= 补充没有打卡的班次 ==========

        return [
            'nums'=>null,
            'money'=>$total_money,
            'desc'=>$desc,
        ];
    }

    public function descTime($diff_second)
    {
        if($diff_second < 60)
        {
            $t = $diff_second."秒";
        }else{
            $t = intval($diff_second/60)."分钟";
        }

        return $t;
    }

    // 找寻左侧集合中离 指定时间最近的时间  6:20  6:30  6:55  7:00  这时如果指定时间为7点 取到的值则为6:55
    public function countNearestForLeft($times,$start_time,$pre_end_time)
    {
        $temps = [];
        foreach ($times as $k=>$v){
            if($v->created_at > $pre_end_time && $v->created_at <= $start_time)
            {
                $temps = $v;
            }
        }
        // 判断最接近的这个时间是不是上线时间
        if(!empty($temps) && $temps->type == 1){
            return $temps;
        }else{
            // 迟到
            return [];
        }

    }

    // 获取上线或下线的时间集合
    public function onOrOffLineTimes($user_id=0,$type,$start,$end){
        return DB::table('online_offlines')
            ->where(['user_id'=>$user_id,'type'=>$type])
            ->whereBetWeen('created_at',[$start,$end])
            ->pluck('created_at')->toArray();
    }

    // 任务完成量和完成进度
    public function taskDatas(&$usersDatas,$user_id,$start,$end)
    {
        $total = 24;
        $effective = DB::table('task_logs')->where(['user_id'=>$user_id])
            ->where('is_effective','>',0)
            ->whereBetWeen('created_at',[$start,$end])
            ->get()->count();
        $task_complete_nums   = DB::table('task_logs')
            ->where(['user_id'=>$user_id])
            ->whereBetWeen('created_at',[$start,$end])
            ->select('id','created_at','is_effective')
            ->get()->toArray();
        $usersDatas[$user_id]['task_complete_nums'] = count($task_complete_nums);
        $task_progress = (round($effective/$total,2)*100).'%';
        $usersDatas[$user_id]['task_progress'] = $task_progress;
    }
}
