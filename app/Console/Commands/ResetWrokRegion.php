<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\DashboardRegionNoBody;
use App\Models\RegionGroup;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetWrokRegion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:workRegion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重置工作区';

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

        // 记录前一天的三方未出勤人数 ------start
        $yesterday = Carbon::yesterday()->timestamp;
        // 出勤
        $chuqing_user_ids = DB::table('dashboard_attendances')->where(['type'=>1,'date_day'=>$yesterday])->pluck('user_id')->toArray();
        $users = DB::table('users')->whereNull('deleted_at')
//            ->whereIn('role',[20,10])
            ->whereNotIn('id',$chuqing_user_ids)->get();

        //$work_notices
        $t = time();
        foreach ($users as $k=>$v)
        {
            $company = Company::find($v->company_id);
            $work_notices[] = [
                'date_day'   => $yesterday,
                'user_id'    => $v->id,
                'user_name'  => $v->name,
                'user_phone' => $v->phone,
                'user_role'  => $v->role,
                'user_region'=> $v->region_id,
                'user_work_region' => $v->work_region_id,
                'company' => empty($company) ? '':$company->name,
                'company_id' => $v->company_id,
                'type' => 2,
                'created_at'=>$t,
                'updated_at'=>$t
            ];
        }
        $res = DB::table('dashboard_attendances')->insert($work_notices);
        // 记录前一天的三方未出勤人数 ------end


        DB::transaction(function () {
            DB::table('users')->where('role',10)->update([
                'work_region_id'=>null,
                'is_online'=>0
            ]);
        });


        // 清理完所有的工作区后  初始化每天的缺岗数据
        $regions = DB::table('work_regions')
            ->select(DB::raw('group_id,count(*) as nums'))
            ->whereNull('deleted_at')
            ->where('group_id','>',0)
            ->groupBy('group_id')
            ->get();

        $date_day = Carbon::now()->startOfDay()->timestamp;
        $groups = RegionGroup::pluck('name','id')->toArray();
        foreach ($regions as $k=>$v)
        {
            DashboardRegionNoBody::firstOrCreate(
                ['date_day'=>$date_day,'group_id'=>$v->group_id],
                [
                    'group_id'         => $v->group_id,
                    'group_name'       => empty($groups[$v->group_id]) ? '':$groups[$v->group_id],
                    'body_nums'        => $v->nums,
                    'date_day'         => $date_day,
                ]
            );
        }
        return 0;
    }
}
