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

//        $temp = date('Y-m-d '.'00:00'.':00',1652498048);
//        echo  $temp." \n";
//        echo strtotime($temp);
//        die;
        DB::transaction(function () use ($date_day){
            # 获取公司数据
            $companies = DB::table('companies')->pluck('name','id')->toArray();
            # 获取网格数据
            $regions   = DB::table('work_regions')->pluck('name','id')->toArray();
            # 当前时间戳
            $time = time();
            # 查找所有的三级和二级人员
            $users = DB::table('users')
                ->whereIn('role',[10,20])
                ->select('id','name','phone','company_id','region_id','work_region_id','role')
                ->get()
                ->each(function ($data,$key) use($companies,$regions){
                    $data->user_company = empty($companies[$data->company_id]) ? null:$companies[$data->company_id];
                    $data->user_region = empty($regions[$data->region_id]) ? null:$regions[$data->region_id];
                    $data->user_work_region = empty($regions[$data->work_region_id]) ? null:$regions[$data->work_region_id];
                });

            # 日期的起止时间戳
            $day = empty($date_day) ? date('Y-m-d'):$date_day;
            $start = Carbon::parse($day)->startOfDay()->timestamp;
            $end   = Carbon::parse($day)->endOfDay()->timestamp;

            # 用户的考勤数据集合
            $usersDatas = [];
            foreach ($users as $k=>$v){
                $money   = 0;
                $money_desc = '';
                # 用户的基础数据集合
                $usersDatas[$v->id] = [
                    'user_id' => $v->id,
                    'user_name' => $v->name,
                    'user_phone' => $v->phone,
                    'user_company' => empty($v->company_id) ? null:$v->user_company."(ID:{$v->company_id})",
                    'user_role' => $v->role,
                    'user_region' => empty($v->region_id) ? null: $v->user_region."(ID:{$v->region_id})",
                    'user_work_region' => empty($v->work_region_id) ? null: $v->user_work_region."(ID:{$v->work_region_id})",
                    'created_at'=>$time,
                    'updated_at' => $time
                ];

                # ================  考勤的人员是 二级人员 start ============
                $region_not_user_nums  = 0; // 无网格出勤次数
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

                        $usersDatas[$v->id]['online_times'] = null;
                        $usersDatas[$v->id]['offline_times'] = null;
                        $usersDatas[$v->id]['task_complete_nums'] = null;
                        $usersDatas[$v->id]['task_progress'] = null;
                        $usersDatas[$v->id]['late_nums'] = null;
                        $usersDatas[$v->id]['early_nums'] = null;
                        $usersDatas[$v->id]['task_dd_nums'] = null;

                        $money += $region_not_user_nums * $region_not_user_money;
                        $money_desc .= "网格无人员出勤{$region_not_user_nums}次共扣".($region_not_user_nums * $region_not_user_money)."元|";
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

                    // 迟到
                    $temp_late =$this->countLateOrEarly($online_times,$v->id,$start,'late');
                    // 早退
                    $temp_early = $this->countLateOrEarly($offline_times,$v->id,$start,'early');

                    if(empty($online_times) && empty($offline_times)){
                        // 迟到次数 // 早退次数
                        $usersDatas[$v->id]['late_nums']  = null;
                        $usersDatas[$v->id]['early_nums'] = null;

                        $money += 140 ;
                        $money_desc .= "没有上线和下线数据,旷工一天|";

                    }else{
                        // 迟到次数
                        //$temp_late= $this->countLate($online_times);
                        $usersDatas[$v->id]['late_nums'] = round($temp_late['nums']);
                        $money +=$temp_late['money'];
                        $money_desc .= $temp_late['desc']."|";

                        // 早退次数
                        //$temp_early= $this->countEarly($offline_times);
                        $usersDatas[$v->id]['early_nums'] = round($temp_early['nums']);
                        $money +=$temp_early['money'];
                        $money_desc .= $temp_early['desc']."|";
                    }

                    //任务段档次数
                    $dd = 0 ; //断档次数
                    if(!empty($task_complete_nums))
                    {
                        $old_time = 0 ;
                        // 1. 所有的任务列表 时间从早到晚
                        foreach ($task_complete_nums as $vv)
                        {
                            /*
                            if(empty($old_time))
                            {
                                $old_time = $vv->created_at;
                                continue;
                            }else{
                                $diff = $vv->created_at - $old_time;
                                // 2.如果前后两个任务的时间大于 1 小时 则计一次断档
                                if($diff > 3600){
                                    $dd ++;
                                }
                                $old_time = $vv->created_at;
                            }
                            */
                            if(!empty($vv->is_effective) && $vv->is_effective == 1)
                            {
                                $dd ++;
                            }
                        }
                        $dd > 8 && $dd = 8;
                        $dd = 8 - $dd;
                    }else{
                        $dd = 8;//如果一天都做任务最多断档8次 24/3=8
                    }
                    $money += $dd * 30;
                    $money_desc .= "断档{$dd}次共扣".($dd * 30)."元|";
                    $usersDatas[$v->id]['task_dd_nums'] = $dd;
                    $usersDatas[$v->id]['region_not_user_nums'] = null;
                    $usersDatas[$v->id]['money'] = $money;
                    $usersDatas[$v->id]['money_details'] = $money_desc;
                //}
                # ================  考勤的人员是 三级人员 end   ============
            }

            // 插入考勤记录
            $res = DB::table('attendances')->insert($usersDatas);

        });
        return 0;
    }

    // 计算迟到
    public function countLateOrEarly($line_times=[],$user_id,$start,$type='late')
    {
        # 获取当前人员的上班时间集合
        $working_times = DB::table('working_times')->where('uesr_id',$user_id)->get()->toArray();

        # 判断这一天是否存在上线数据
        if(!empty($line_times))
        {

            # 上线迟到的时间集合
            $late_or_eary_array = [];
            foreach ($line_times as $k=>$v)
            {
                foreach ($working_times as $kk=>$vv)
                {
                    if($v > $vv['start_time'] && $v < $vv['end_time'])
                    {
                        $late_or_eary_array[$vv['id']][] = $v;
                    }
                }
            }

            # 一天总的迟到/早退次数
            $nums = count($late_or_eary_array);

            # 迟到/早退 集合的统计
            $desc = '';
            $money_total = 0;
            # 每种班的最长迟到/早退时间(早班,午班,晚班)
            foreach ($working_times as $kk=>$vv)
            {
                // 当前班次是否存在迟到/早退
                if(!empty($late_or_eary_array[$vv['id']])){

                    $temp = $type == 'late' ? strtotime(date('Y-m-d '.'00:00'.':00',$start))  : strtotime(date('Y-m-d '.'23:59'.':00',$start));
                    foreach ($late_or_eary_array[$vv['id']] as $kkk=>$vvv)
                    {
                        if($type == 'late')
                        {
                            if($temp < $vvv)
                            {
                                $temp = $vvv;
                            }
                        }else{
                            if($temp > $vvv)
                            {
                                $temp = $vvv;
                            }
                        }
                    }

                    if($type == 'late'){
                        // 最大的迟到时间 - 上班时间 = 迟到时间
                        $start_time =strtotime(date('Y-m-d '.$vv['start_time'].':00',$start));
                        $diff_second = ($temp - $start_time);
                    }else{
                        // 下班时间 - 当前班次最大早退时间 = 早退时间
                        $end_time =strtotime(date('Y-m-d '.$vv['end_time'].':00',$start));
                        $diff_second = ($end_time - $temp);
                    }

                    if($diff_second > 0)
                    {
                        if($diff_second >= 1800 && $diff_second < 3600)
                        {
                            // 30分钟 ~ 1小时 扣15
                            $_money = 15;
                        }elseif ($diff_second >= 3600 && $diff_second < (3600*3))
                        {
                            // 1~3 小时 扣30
                            $_money = 30;
                        }elseif($diff_second >= (3600*3)){
                            // 大于 3 小时 扣70
                            $_money = 70;
                        }

                        $t = '';
                        if($diff_second < 60)
                        {
                            $t = $diff_second."秒";
                        }else{
                            $t = intval($diff_second/60)."分钟";
                        }

                        $money_total +=$_money;
                        $ttt = $type == 'late' ? "迟到" : "早退";
                        $desc .= "{$ttt}({$vv['name']})".$t."扣款{$_money}元,";
                    }
                }

            }

            return [
                'nums'=>$nums,
                'money'=>$money_total,
                'desc'=>$desc,
            ];
        }else{
            $nums = count($working_times);
            $t = $type=='late' ? "上线":"下线";
            $money = $nums * 30; // 如果每个工作时间段都没有
            return [
                'nums'=>$nums,
                'money'=>$money,
                'desc'=>"全天没有{$t}数据，扣款{$money}元",
            ];
        }
    }

    // 计算迟到
    public function countLate($online_times=[])
    {
        if(!empty($online_times))
        {
            $time_07 = strtotime(date('Y-m-d 07:00:00')); //07:00 的时间戳
            $time_15 = strtotime(date('Y-m-d 15:00:00')); //15:00 的时间戳
            $late_num = 0;//迟到次数
            $online_times_00_11 = [];// 取出 00:00 ~ 11:00 点的上线数据
            $online_times_11_19 = [];// 取出 11:00 ~ 19:00 点的上线数据
            // 所有的上线时间循环
            foreach ($online_times as $vv)
            {
                // 打卡时间
                $Hi = date('H:i',$vv);
                if($Hi < '11:00'){
                    $online_times_00_11[] = $vv;
                }elseif ($Hi >= '11:00' && $Hi < '19:00'){
                    $online_times_11_19[] = $vv;
                }
            }

            // 早上上线情况
            $zaoshang = $this->countLateNumsAndMoney($online_times_00_11,$time_07);
            // 下午上线情况
            $xiawu = $this->countLateNumsAndMoney($online_times_11_19,$time_15);

            return [
                'nums'=>$zaoshang['nums']+$xiawu['nums'],
                'money'=>$zaoshang['money']+$xiawu['money'],
                'desc'=>"早上".$zaoshang['desc']."下午".$xiawu['desc'],
            ];
        }else{
            return [
                'nums'=>2,
                'money'=>60,
                'desc'=>"全天没有上线数据，扣款60元",
            ];
        }
    }
    // 计算迟到的次数和扣款的钱
    public function countLateNumsAndMoney($online_times_spoce,$start_time)
    {
        if(empty($online_times_spoce)){
            return ['nums'=>1,'money'=>30,'desc'=>"没有上线数据,扣款30元"];
        }

        // 迟到次数
        $late_num = 0;
        // 迟到扣的钱
        $_money = 0 ;
        $desc = "没迟到";
        // 对数组进行升序排列
        sort($online_times_spoce);
        // 取出最大的时间
        $max_times = array_pop($online_times_spoce);
        // 最大的打卡时间 - 上班时间 = 迟到时间
        $diff_second = ($max_times - $start_time);
        if($diff_second > 0)
        {
            $late_num = 1;
            if($diff_second >= 1800 && $diff_second < 3600)
            {
                $_money = 15;
            }elseif ($diff_second > 3600)
            {
                $_money = 30;
            }

            $t = '';
            if($diff_second < 60)
            {
                $t = $diff_second."秒";
            }else{
                $t = intval($diff_second/60)."分钟";
            }
            $desc = "迟到".$t."扣款{$_money}元";
        }
        return ['nums'=>$late_num,'money'=>$_money,'desc'=>$desc];
    }

    // 计算早退
    public function countEarly($offline_times=[])
    {
        if(!empty($offline_times))
        {
            $time_11 = strtotime(date('Y-m-d 11:00:00')); //07:00 的时间戳
            $time_19 = strtotime(date('Y-m-d 19:00:00')); //15:00 的时间戳
            $late_num = 0;//迟到次数
            $online_times_07_11 = []; //取出 07:00 ~ 11:00 点的下线数据
            $online_times_15_19 = []; //取出 15:00 ~ 19:00 点的下线数据
            // 所有的上线时间循环
            foreach ($offline_times as $vv)
            {
                // 打卡时间
                $Hi = date('H:i',$vv);
                if( $Hi >= '07:00' && $Hi < '11:00'){
                    $online_times_07_11[] = $vv;
                }elseif ($Hi >= '15:00' && $Hi < '19:00'){
                    $online_times_15_19[] = $vv;
                }
            }

            // 早上下线情况
            $zaoshang = $this->countEarlyNumsAndMoney($online_times_07_11,$time_11);
            // 下午下线情况
            $xiawu = $this->countEarlyNumsAndMoney($online_times_15_19,$time_19);

            return [
                'nums'=>$zaoshang['nums']+$xiawu['nums'],
                'money'=>$zaoshang['money']+$xiawu['money'],
                'desc'=>"早上".$zaoshang['desc']."下午".$xiawu['desc'],
            ];
        }else{
            return [
                'nums'=>2,
                'money'=>60,
                'desc'=>"全天没有下线数据，扣款60元",
            ];
        }
    }
    // 计算早退的次数和扣款的钱
    public function countEarlyNumsAndMoney($online_times_spoce,$start_time)
    {
        if(empty($online_times_spoce)){
            return ['nums'=>1,'money'=>30,'desc'=>"没有下线数据,扣款30元"];
        }

        // 迟到次数
        $late_num = 0;
        // 迟到扣的钱
        $_money = 0 ;
        $desc = "没早退";
        // 对数组进行降序排列
        rsort($online_times_spoce);
        // 取出最大的时间
        $last_times = array_pop($online_times_spoce);
        // 最大的打卡时间 - 上班时间 = 迟到时间
        $diff_second = ($start_time - $last_times);
        if($diff_second > 0)
        {
            $late_num = 1;
            if($diff_second >= 1800 && $diff_second < 3600)
            {
                $_money = 15;
            }elseif ($diff_second > 3600)
            {
                $_money = 30;
            }

            $t = '';
            if($diff_second < 60)
            {
                $t = $diff_second."秒";
            }else{
                $t = intval($diff_second/60)."分钟";
            }
            $desc = "早退".$t."扣款{$_money}元";
        }
        return ['nums'=>$late_num,'money'=>$_money,'desc'=>$desc];
    }

    public function onOrOffLineTimes($user_id=0,$type,$start,$end){
        return DB::table('online_offlines')
            ->where(['user_id'=>$user_id,'type'=>$type])
            ->whereBetWeen('created_at',[$start,$end])
            ->pluck('created_at')->toArray();
    }

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
