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
    protected $signature = 'attendance:record';

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
        DB::transaction(function () {
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
            $start = Carbon::now()->startOfDay()->timestamp;
            $end   = Carbon::now()->endOfDay()->timestamp;



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

                //上线时间集合
                $online_times = DB::table('online_offlines')
                    ->where(['user_id'=>$v->id,'type'=>1])
                    ->whereBetWeen('created_at',[$start,$end])
                    ->pluck('created_at')->toArray();
                $usersDatas[$v->id]['online_times'] = empty($online_times) ? null:json_encode($online_times);

                //下线时间集合
                $offline_times = DB::table('online_offlines')
                    ->where(['user_id'=>$v->id,'type'=>1])
                    ->whereBetWeen('created_at',[$start,$end])
                    ->pluck('created_at')->toArray();
                $usersDatas[$v->id]['offline_times'] = empty($offline_times) ? null:json_encode($offline_times);

                //任务完成量/任务完进度
                $total = 24;
                $effective = DB::table('task_logs')->where(['user_id'=>$v->id])
                    ->where('is_effective','>',0)->whereBetWeen('created_at',[$start,$end])
                    ->get()->count();
                $task_complete_nums   = DB::table('task_logs')
                    ->where(['user_id'=>$v->id])
                    //->whereBetWeen('created_at',[$start,$end])
                    ->select('id','created_at')
                    ->get()->toArray();
                $usersDatas[$v->id]['task_complete_nums'] = count($task_complete_nums);
                $task_progress = (round($effective/$total,2)*100).'%';
                $usersDatas[$v->id]['task_progress'] = $task_progress;

                //迟到次数 半小时15元，1小时30元  上班时间 早 7:03  下午 15:03
                $late_num = 0;
                $online_temp = ['z_1'=>0,'z_2'=>0,'x_1'=>0,'x_2'=>0,'z'=>0,'x'=>0];
                if(!empty($online_times))
                {
                    // 所有的上线时间循环
                    foreach ($online_times as $vv)
                    {
                        // 打卡时间
                        $Hi = date('H:i',$vv);
                        //  ------------ 早上 -----------
                        // 迟到 半小时未满一小时
                        if(($Hi > '07:30' && $Hi < '8:00'))
                        {
                            $online_temp['z_1']++ ; //早上 迟到半小时到一小时内的次数
                        }
                        // 迟到 一小时及以上
                        if(($Hi >= '08:00' && $Hi < '11:00'))
                        {
                            $online_temp['z_2']++ ; //早上 迟到一小时以上的次数
                        }
                        //  ------------ 下午 -----------
                        // 迟到 半小时未满一小时
                        if(($Hi > '15:30' && $Hi < '16:00'))
                        {
                            $online_temp['x_1']++ ; //下午 迟到半小时到一小时内的次数
                        }
                        // 迟到 一小时及以上
                        if(($Hi >= '16:00' && $Hi < '19:00'))
                        {
                            $online_temp['x_2']++ ; //下午 迟到一小时以上的次数
                        }
                    }

                    if($online_temp['z_1'] > 0){
                        $online_temp['z'] = 0.5;
                    }
                    if($online_temp['z_2'] > 0){
                        $online_temp['z'] = 1;
                    }
                    if($online_temp['x_1'] > 0){
                        $online_temp['x'] = 0.5;
                    }
                    if($online_temp['x_2'] > 0){
                        $online_temp['x'] = 1;
                    }
                    $late_num = $online_temp['z'] +  $online_temp['x'] ;

                    $money_desc .= "迟到{$late_num}次共扣".($late_num * 30)."元(早上{$online_temp['z']}*30，下午{$online_temp['x']}*30)|";
                }else{
                    $late_num = 2 ;
                    $money_desc .= "迟到{$late_num}次共扣".($late_num * 15)."元(没有上线记录)|";
                }
                $money += $late_num * 30;

                $usersDatas[$v->id]['late_nums'] = round($late_num);

                //早退次数
                $early_num = 0;
                $_temp = ['z_1'=>0,'z_2'=>0,'x_1'=>0,'x_2'=>0,'z'=>0,'x'=>0];
                if(!empty($offline_times))
                {
                    foreach ($offline_times as $vv)
                    {
                        // 打卡时间
                        $Hi = date('H:i',$vv);
                        //  ------------ 早上 -----------
                        // 早退 半小时未满一小时
                        if(($Hi > '07:30' && $Hi < '8:00'))
                        {
                            $_temp['z_1']++ ;
                        }
                        // 早退 一小时及以上
                        if(($Hi >= '08:00' && $Hi < '11:00'))
                        {
                            $_temp['z_2']++ ;
                        }
                        //  ------------ 下午 -----------
                        // 早退 半小时未满一小时
                        if(($Hi > '15:30' && $Hi < '16:00'))
                        {
                            $_temp['x_1']++ ;
                        }
                        // 早退 一小时及以上
                        if(($Hi >= '16:00' && $Hi < '19:00'))
                        {
                            $_temp['x_2']++ ;
                        }
                    }
                    if($_temp['z_1'] > 0){
                        $_temp['z'] = 0.5;
                    }
                    if($_temp['z_2'] > 0){
                        $_temp['z'] = 1;
                    }
                    if($_temp['x_1'] > 0){
                        $_temp['x'] = 0.5;
                    }
                    if($_temp['x_2'] > 0){
                        $_temp['x'] = 1;
                    }
                    $early_num = $_temp['z'] +  $_temp['x'] ;
                    $money_desc .= "早退{$early_num}次共扣".($early_num * 30)."元(早上{$_temp['z']}*30，下午{$_temp['x']}*30)|";
                }else{
                    $early_num = 2;
                    $money_desc .= "早退{$early_num}次共扣".($early_num * 30)."元(没有下线记录)|";
                }
                $money += $early_num * 30;
                $usersDatas[$v->id]['early_nums'] = round($early_num);


                //任务段档次数
                $dd = 0 ; //断档次数
                if(!empty($task_complete_nums))
                {
                    $old_time = 0 ;
                    // 1. 所有的任务列表 时间从早到晚
                    foreach ($task_complete_nums as $vv)
                    {
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
                    }
                }else{
                    $dd = 8;//如果一天都做任务最多断档8次 24/3=8
                }
                $money += $dd * 30;
                $money_desc .= "断档{$dd}次共扣".($dd * 30)."元|";
                $usersDatas[$v->id]['task_dd_nums'] = $dd;

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
                    }
                }
                $money += $region_not_user_nums * 140;
                $money_desc .= "网格无人员出勤{$region_not_user_nums}次共扣".($region_not_user_nums * 140)."元|";
                $usersDatas[$v->id]['region_not_user_nums'] = $region_not_user_nums;
                $usersDatas[$v->id]['money'] = $money;
                $usersDatas[$v->id]['money_details'] = $money_desc;
            }

            // 插入考勤记录
            $res = DB::table('attendances')->insert($usersDatas);



        });
        return 0;
    }
}
