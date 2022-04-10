<?php

namespace App\Console\Commands;

use App\Models\WorkNotice as WorkNoticeModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class WorkNotice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notice:insert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '产生日常任务通知';

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
        Log::info('worknotices start');


        DB::transaction(function () {
            $users = DB::table('users')->select('id')->get();
            $work_notices = [];
            $time = time();
            foreach ($users as $k=>$v)
            {
                $work_notices[] = [
                        'name'  => '日常任务',
                        'type' => 1,
                        'content' => '请使用首页-车辆摆放归还，进行今日摆放任务上报，如已经完成作业请忽略~',
                        'user_id' =>$v->id,
                        'created_at'=>$time,
                        'updated_at' => $time
                    ];
            }
            $res = DB::table('work_notices')->insert($work_notices);

        });


        return 0;

    }
}
