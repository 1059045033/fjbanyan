<?php

namespace App\Console\Commands;

use App\Models\WorkNotice as WorkNoticeModel;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class Test2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:hy {--day=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '还原数据';

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
        # 获取参数 日期参数
        $day = empty($this->option('day')) ? null:$this->option('day');
        if(empty($day))
        {
            return 0;
        }

        $day2 = date('Ymd',strtotime($day));

        #获取目录中的所有图片
        $ppp = 'task_atlas_no_sy';
        # 日期的起止时间戳

        $start = Carbon::parse($day)->startOfDay()->timestamp;
        $end   = Carbon::parse($day)->endOfDay()->timestamp;

        $logs = DB::table('task_log_no_sies_bak')
            ->whereBetWeen('created_at',[$start,$end])
            ->select('id','atlas')
            ->get()->toArray();

        foreach ($logs as $k=>$v)
        {
            DB::table('task_log_no_sies')
                ->where(['id'=>$v['id']])->update([
                    'atlas' =>$v['atlas']
                ]);
        }
        return 0;
    }




}
