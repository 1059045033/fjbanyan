<?php

namespace App\Http\Controllers\Admin;

use App\Exports\Export as EExport;
use App\Models\Company;
use App\Models\TaskLog;
use App\Http\Requests\StoreTaskLogRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TaskLogController extends Controller
{
    public function logs(Request $request)
    {

        $search = $request->query('name');
        $sort = 'desc';
        $fillter = [];


        $request->query('sort') == '+id' && $sort = 'asc';
        $page = $request->query('page') ?? 1;
        $limit = $request->query('limit') ?? 10;

        $day = empty($request->query('start_date')) ? date('Y-m-d'):$request->query('start_date');

        $start_date = Carbon::parse($day)->startOfDay()->timestamp;
        $end_date   = Carbon::parse($day)->endOfDay()->timestamp;

        $user_ids = [];
        if(!empty($search)){
            $user_ids = User::where('name','like','%'.$search.'%')->pluck('id')->toArray();
            empty($user_ids) && $user_ids = [-1];
        }


        $total = TaskLog::where($fillter)
            ->when(!empty($user_ids), function ($query) use($user_ids){
                $query->whereIn('user_id',$user_ids);
            })
            ->where('created_at','>',$start_date)
            ->where('created_at','<',$end_date)->count();


        $list = TaskLog::with(['userInfo:id,name,phone','workRegionInfo:id,name'])->where($fillter)
            ->when(!empty($user_ids), function ($query) use($user_ids){
                $query->whereIn('user_id',$user_ids);
            })
            ->where('created_at','>',$start_date)
            ->where('created_at','<',$end_date)
            ->orderBy('id',$sort)->forPage($page,$limit)->get();

        $result = [
            'total' => $total,
            'items' => $list
        ];

        return $this->myResponse($result,''.$start_date.' -- '.$end_date." == ".$day,200);
    }

    public function export(Request $request)
    {
        set_time_limit(3000);
        $companies = DB::table('companies')->pluck('name','id')->toArray();
        $regions = DB::table('work_regions')->pluck('name','id')->toArray();
        $time = time();
        $user_ids = [];
        if(!empty($request->name)){
            // $user_ids = DB::table('users')->whereIn('role',[10])->where('name','like','%'.$request->name.'%')->pluck('id')->toArray();
            $user_ids = User::where('name','like','%'.$request->name.'%')->pluck('id')->toArray();
            empty($user_ids) && $user_ids = [-1];
        }

        // ?????? ??????????????? ??????
        $users = DB::table('users')
            //->whereIn('role',[10])
            ->when(!empty($user_ids), function ($query) use($user_ids){
                $query->whereIn('id',$user_ids);
            })
            ->select('id','name','phone','company_id','region_id','work_region_id','role')
            ->get()
            ->each(function ($data,$key) use($companies,$regions){
                $data->user_company = empty($companies[$data->company_id]) ? null:$companies[$data->company_id];
                $data->user_region = empty($regions[$data->region_id]) ? null:$regions[$data->region_id];
                $data->user_work_region = empty($regions[$data->work_region_id]) ? null:$regions[$data->work_region_id];
            });

        $date_day = $request->start_date;
        // ?????????????????????
        $day = empty($date_day) ? date('Y-m-d'):$date_day;
        $start = Carbon::parse($day)->startOfDay()->timestamp;
        $end   = Carbon::parse($day)->endOfDay()->timestamp;



        $usersTaskDatas = [];

        foreach ($users as $k=>$v){
            $temp = [];//????????????????????????
            # ================  ?????????????????????????????? start  ============
            $task_logs =DB::table('task_logs')
                ->where(['user_id'=>$v->id])
                ->whereBetWeen('task_logs.created_at',[$start,$end])
                ->select('position','address','created_at','content','atlas')
                ->get()
                ->each(function ($data,$key){
                    $data->created_at = date('Y-m-d H:i:s',$data->created_at);
                    if(!empty($data->atlas)){
                        $temp = json_decode($data->atlas,1);
                        foreach ($temp as &$vvv)
                        {
                            $vvv = config('app.url').$vvv;
                        }
                        $data->atlas_arr = $temp;//json_decode($data->atlas,1);
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
                    $temp['atlas_arr']            = null;
                    $temp['atlas']    = implode(',',$vv->atlas_arr);
                    array_push($usersTaskDatas,$temp);
                }
            }
            # ================  ?????????????????????????????? end    ============
        }

        // ??????excle
        $task_res = $this->ExportTaskLogs($usersTaskDatas);
        return $this->myResponse($task_res,'',200);
    }


    // ??????????????????
    protected function ExportTaskLogs($usersTaskDatas=[],$filename='????????????')
    {
        //????????????
        $row = [[
            "name"=>'??????',
            "phone"=>'????????????',
            "company"=>'??????',
            "user_region"=>'????????????',
            "user_work_region"=>'????????????',
            "position" => "?????????",
            "address" => "??????",
            "created_at" => "??????",
            "content" => "??????",
            "atlas"=>"??????"
        ]];

        $rowHeights = [];
        $rowHeights[1] = 40;//???????????? 40

        for($i = 2;$i<=(count($usersTaskDatas)+1);$i++)
        {
            $rowHeights[$i] = 100;
        }

        $columnWidth = ['A'=>20,'B'=>20,'C'=>35,'D'=>20,'E'=>20,'F'=>55,'G'=>40,'H'=>30,'J'=>140];

        // ????????????
        $header = $row;//????????????
        $excel = new EExport($usersTaskDatas, $header,'??????????????????');
        $excel->setColumnWidth($columnWidth);
        $excel->setRowHeight($rowHeights);
        $excel->setBold(['A1:Z1' => true]);
        $path = 'public'.DIRECTORY_SEPARATOR.'task_excle_admin'.DIRECTORY_SEPARATOR.date('Ymd').DIRECTORY_SEPARATOR;
        $url  = config('app.url').DIRECTORY_SEPARATOR."storage".DIRECTORY_SEPARATOR.'task_excle_admin'.DIRECTORY_SEPARATOR.date('Ymd').DIRECTORY_SEPARATOR.$filename."_".date('Y_m_d_H').'.xlsx';
//        if(file_exists($path.$filename."_".date('Y_m_d_H').'.xlsx')){
//            $res  = true;
//        }else{
//            $res  = Excel::store($excel, $path.$filename."_".date('Y_m_d_H').'.xlsx');
//        }
        $res  = Excel::store($excel, $path.$filename."_".date('Y_m_d_H').'.xlsx');
        return ['state'=>$res,'url'=>$url];
    }
}
