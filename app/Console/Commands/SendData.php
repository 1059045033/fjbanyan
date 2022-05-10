<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\WorkRegion;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Export as EExport;



class SendData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendData:today';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发送当天的数据';

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

        DB::transaction(function (){
            $companies = DB::table('companies')->pluck('name','id')->toArray();
            $regions = DB::table('work_regions')->pluck('name','id')->toArray();
            $time = time();
            // 获取 所有的三级 人员
            $users = DB::table('users')
                ->whereIn('role',[10])
                ->select('id','name','phone','company_id','region_id','work_region_id','role')
                ->get()
                ->each(function ($data,$key) use($companies,$regions){
                    $data->user_company = empty($companies[$data->company_id]) ? null:$companies[$data->company_id];
                    $data->user_region = empty($regions[$data->region_id]) ? null:$regions[$data->region_id];
                    $data->user_work_region = empty($regions[$data->work_region_id]) ? null:$regions[$data->work_region_id];
                });

            // 获取当天时间戳
            $day = empty($date_day) ? date('Y-m-d'):$date_day;
            $start = Carbon::parse('2022-04-01')->startOfDay()->timestamp;
            $end   = Carbon::parse($day)->endOfDay()->timestamp;


            $usersTaskDatas = [];
            $usersTrackDatas = [];
            foreach ($users as $k=>$v){
                $temp = [];//任务轨迹临时存储

                # ================  查找该用户的任务轨迹 start  ============
                  $task_logs =DB::table('task_logs')
                      ->where(['user_id'=>$v->id])
                      ->whereBetWeen('task_logs.created_at',[$start,$end])
                      ->select('position','address','created_at','content','atlas')
                      ->get()
                      ->each(function ($data,$key){
                          $data->created_at = date('Y-m-d H:i:s',$data->created_at);
                          if(!empty($data->atlas)){
                              $data->atlas_arr = json_decode($data->atlas,1);
                          }

                      })
                      ->toArray();

                  if(empty($task_logs))
                  {
                      $temp['name']             = $v->name;
                      $temp['phone']            = $v->phone;
                      $temp['company']          = $v->user_company;
                      $temp['user_region']      = $v->user_region;
                      $temp['user_work_region'] = $v->user_work_region;
                      $temp['position']         = null;
                      $temp['address']          = null;
                      $temp['create_at']        = null;
                      $temp['content']          = null;
                      $temp['atlas']            = null;
                      $temp['atlas_arr']        = null;
                      array_push($usersTaskDatas,$temp);

                  }else{
                      foreach ($task_logs as $kk=>$vv)
                      {
                          $temp['name']             = $v->name;
                          $temp['phone']            = $v->phone;
                          $temp['company']          = $v->user_company;
                          $temp['user_region']      = $v->user_region;
                          $temp['user_work_region'] = $v->user_work_region;
                          $temp['position']         = $vv->position;
                          $temp['address']          = $vv->address;
                          $temp['created_at']       = $vv->created_at;
                          $temp['content']          = $vv->content;
                          $temp['atlas']            = null;
                          $temp['atlas_arr']        = $vv->atlas_arr;
                          array_push($usersTaskDatas,$temp);
                      }
                  }
                # ================  查找该用户的任务轨迹 end    ============

                $tmep_track = [];//行踪轨迹临时存储
                # ================  查找该用户的行踪轨迹 start  ============
                $track_logs =DB::table('tracks')
                    ->where(['user_id'=>$v->id])
                    ->whereBetWeen('created_at',[$start,$end])
                    ->select('position','address','created_at')
                    ->get()
                    ->each(function ($data,$key){
                        $data->created_at = date('Y-m-d H:i:s',$data->created_at);
                    })
                    ->toArray();

                if(empty($track_logs))
                {
                    $tmep_track['name']             = $v->name;
                    $tmep_track['phone']            = $v->phone;
                    $tmep_track['company']          = $v->user_company;
                    $tmep_track['user_region']      = $v->user_region;
                    $tmep_track['user_work_region'] = $v->user_work_region;
                    $tmep_track['position']         = null;
                    $tmep_track['address']          = null;
                    $tmep_track['create_at']        = null;
                    array_push($usersTrackDatas,$tmep_track);

                }else{
                    foreach ($task_logs as $kk=>$vv)
                    {
                        $tmep_track['name']             = $v->name;
                        $tmep_track['phone']            = $v->phone;
                        $tmep_track['company']          = $v->user_company;
                        $tmep_track['user_region']      = $v->user_region;
                        $tmep_track['user_work_region'] = $v->user_work_region;
                        $tmep_track['position']         = $vv->position;
                        $tmep_track['address']          = $vv->address;
                        $tmep_track['created_at']       = $vv->created_at;
                        array_push($usersTrackDatas,$tmep_track);
                    }
                }
                # ================  查找该用户的行踪轨迹 end    ============
            }

            // 生成excle
            $task_res = $this->ExportTaskLogs($usersTaskDatas);
            $track_res = $this->ExportTrackLogs($usersTrackDatas);

//            $task_url = config('app.url')."storage/".$task_res['url'];
//            $track_url = config('app.url')."storage/".$track_res['url'];
            // 发送邮件

            $params = [
                'to_name' => "尊敬的领导",
                'message' => date('Y-m-d').'的数据',
                'data' =>[
                    'task_url' => $task_res['url'],
                    'track_url' => $track_res['url'],
                ]
            ];
            Mail::send('email',['params'=>$params],function($message){
                $to = ['190507753@qq.com','1059045033@qq.com','359448144@qq.com'];//'190507753@qq.com;hui7893308@126.com';
                $message ->to($to)->subject(date('Y-m-d')."打包数据");
            });
            // 返回的一个错误数组，利用此可以判断是否发送成功
            dd(Mail::failures());
        });
        return 0;
    }

    // 导出日志任务
    protected function ExportTaskLogs($usersTaskDatas=[],$filename='任务列表')
    {
        //设置表头
        $row = [[
            "name"=>'姓名',
            "phone"=>'手机号码',
            "company"=>'公司',
            "user_region"=>'所属网格',
            "user_work_region"=>'工作网格',
            "position" => "经纬度",
            "address" => "地址",
            "created_at" => "时间",
            "content" => "备注",
            "atlas"=>"图集"
        ]];

        $rowHeights = [];
        $rowHeights[1] = 40;//标题栏高 40

        for($i = 2;$i<=(count($usersTaskDatas)+1);$i++)
        {
            $rowHeights[$i] = 100;
        }

        $columnWidth = ['A'=>20,'B'=>20,'C'=>35,'D'=>20,'E'=>20,'F'=>40,'G'=>40,'H'=>30,'J'=>40];

        // 执行导出
        $header = $row;//导出表头
        $excel = new EExport($usersTaskDatas, $header,'任务执行列表');
        $excel->setColumnWidth($columnWidth);
        $excel->setRowHeight($rowHeights);
        $excel->setBold(['A1:Z1' => true]);
        /*
        $excel->setFont(['A1:Z1265' => '宋体']);
        $excel->setFontSize(['A1:I1' => 14,'A2:Z1265' => 10]);
        $excel->setBold(['A1:Z2' => true]);
        $excel->setBackground(['A1:A1' => '808080','C1:C1' => '708080']);
        $excel->setMergeCells(['A1:I1']);
        $excel->setBorders(['A2:D5' => '#000000']);
        */
        $path = 'public'.DIRECTORY_SEPARATOR.'task_excle'.DIRECTORY_SEPARATOR.date('Ymd').DIRECTORY_SEPARATOR;
        $url  = config('app.url').DIRECTORY_SEPARATOR."storage".DIRECTORY_SEPARATOR.'task_excle'.DIRECTORY_SEPARATOR.date('Ymd').DIRECTORY_SEPARATOR.$filename."_".date('Y_m_d').'.xlsx';
        $res  = Excel::store($excel, $path.$filename."_".date('Y_m_d').'.xlsx');
        return ['state'=>$res,'url'=>$url];
    }
    // 导出日志行踪
    protected function ExportTrackLogs($datas=[],$filename='轨迹列表')
    {
        //设置表头
        $row = [[
            "name"=>'姓名',
            "phone"=>'手机号码',
            "company"=>'公司',
            "user_region"=>'所属网格',
            "user_work_region"=>'工作网格',
            "position" => "经纬度",
            "address" => "地址",
            "created_at" => "时间",
        ]];

        $rowHeights = [];
        $rowHeights[1] = 40;//标题栏高 40

        for($i = 2;$i<=(count($datas)+1);$i++)
        {
            $rowHeights[$i] = 100;
        }

        $columnWidth = ['A'=>20,'B'=>20,'C'=>35,'D'=>20,'E'=>20,'F'=>40,'G'=>40,'H'=>30];

        // 执行导出
        $header = $row;//导出表头
        $excel = new EExport($datas, $header,'轨迹');
        $excel->setColumnWidth($columnWidth);
        $excel->setRowHeight($rowHeights);
        $excel->setBold(['A1:Z1' => true]);
        $path = 'public'.DIRECTORY_SEPARATOR.'task_excle'.DIRECTORY_SEPARATOR.date('Ymd').DIRECTORY_SEPARATOR;

        $url  = config('app.url').DIRECTORY_SEPARATOR."storage".DIRECTORY_SEPARATOR.'task_excle'.DIRECTORY_SEPARATOR.date('Ymd').DIRECTORY_SEPARATOR.$filename."_".date('Y_m_d').'.xlsx';
        $res  = Excel::store($excel, $path.$filename."_".date('Y_m_d').'.xlsx');
        return ['state'=>$res,'url'=>$url];
    }
}
