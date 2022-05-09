<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\WorkRegion;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AttendanceLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:record {--day=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '记录当天的出勤记录';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date_day = empty($this->option('day')) ? date('Y-m-d'):$this->option('day');


        DB::transaction(function () use ($date_day){
            $companies = DB::table('companies')->pluck('name','id')->toArray();
            $regions = DB::table('work_regions')->pluck('name','id')->toArray();
            $time = time();
            // 获取 所有的三级和二级 用户
            $users = DB::table('users')
                ->whereIn('role',[10,20])
                ->select('id','name','phone','company_id','region_id','work_region_id','role')
                ->get()
                ->each(function ($data,$key) use($companies,$regions){
                    $data->user_company = empty($companies[$data->company_id]) ? null:$companies[$data->company_id];
                    $data->user_region = empty($regions[$data->region_id]) ? null:$regions[$data->region_id];
                    $data->user_work_region = empty($regions[$data->work_region_id]) ? null:$regions[$data->work_region_id];
                });

            // 获取当天时间戳
            //$start = Carbon::now()->startOfDay()->timestamp;
            //$end   = Carbon::now()->endOfDay()->timestamp;

            $day = empty($date_day) ? date('Y-m-d'):$date_day;
            $start = Carbon::parse($day)->startOfDay()->timestamp;
            $end   = Carbon::parse($day)->endOfDay()->timestamp;


            $usersDatas = [];
            foreach ($users as $k=>$v){
                $money   = 0;
                $money_desc = '';
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
                //网格无人员出勤
                $region_not_user_nums = 0;
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

                        $money += $region_not_user_nums * 140;
                        $money_desc .= "网格无人员出勤{$region_not_user_nums}次共扣".($region_not_user_nums * 140)."元|";
                        $usersDatas[$v->id]['region_not_user_nums'] = $region_not_user_nums;
                        $usersDatas[$v->id]['money'] = $money;
                        $usersDatas[$v->id]['money_details'] = $money_desc;
                    }
                    continue;
                }
                # ================  考勤的人员是 二级人员 start ============
                # ================  考勤的人员是 三级人员 start ============
                if(empty($v->work_region_id)){
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
                }else{
                    // 当天所有的上线时间集合
                    $online_times = DB::table('online_offlines')
                        ->where(['user_id'=>$v->id,'type'=>1])
                        ->whereBetWeen('created_at',[$start,$end])
                        ->pluck('created_at')->toArray();
                    $usersDatas[$v->id]['online_times'] = empty($online_times) ? null:json_encode($online_times);

                    // 当天所有的下线时间集合
                    $offline_times = DB::table('online_offlines')
                        ->where(['user_id'=>$v->id,'type'=>1])
                        ->whereBetWeen('created_at',[$start,$end])
                        ->pluck('created_at')->toArray();
                    $usersDatas[$v->id]['offline_times'] = empty($offline_times) ? null:json_encode($offline_times);

                    //任务完成量/任务完进度
                    $total = 24;
                    $effective = DB::table('task_logs')->where(['user_id'=>$v->id])
                        ->where('is_effective','>',0)
                        ->whereBetWeen('created_at',[$start,$end])
                        ->get()->count();
                    $task_complete_nums   = DB::table('task_logs')
                        ->where(['user_id'=>$v->id])
                        ->whereBetWeen('created_at',[$start,$end])
                        ->select('id','created_at','is_effective')
                        ->get()->toArray();
                    $usersDatas[$v->id]['task_complete_nums'] = count($task_complete_nums);
                    $task_progress = (round($effective/$total,2)*100).'%';
                    $usersDatas[$v->id]['task_progress'] = $task_progress;

                    //迟到次数
                    $temp_late= $this->countLate($online_times);
                    $usersDatas[$v->id]['late_nums'] = round($temp_late['nums']);
                    $money +=$temp_late['money'];
                    $money_desc .= $temp_late['desc']."|";
                    //早退次数
                    $temp_early= $this->countEarly($offline_times);
                    $usersDatas[$v->id]['early_nums'] = round($temp_early['nums']);
                    $money +=$temp_early['money'];
                    $money_desc .= $temp_early['desc']."|";


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
                }
                # ================  考勤的人员是 三级人员 end   ============
            }

            // 插入考勤记录
            $res = DB::table('attendances')->insert($usersDatas);

        });
        return 0;
    }

    // 计算迟到
    public function countLate($online_times=[])
    {
        if(!empty($online_times))
        {
            $time_07 = strtotime(date('Y-m-d 07:00:00')); //07:00 的时间戳
            $time_15 = strtotime(date('Y-m-d 15:00:00')); //15:00 的时间戳
            $late_num = 0;//迟到次数
            $online_times_00_11 = [];//取出 00:00 ~ 11:00 点的上线数据
            $online_times_11_19 = [];//取出 11:00 ~ 19:00 点的上线数据
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
}
