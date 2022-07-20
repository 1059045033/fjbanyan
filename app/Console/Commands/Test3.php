<?php

namespace App\Console\Commands;

use App\Models\WorkNotice as WorkNoticeModel;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class Test3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:zbc {--day=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '补充无水印图片';

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

        $start = Carbon::parse($day)->startOfDay()->timestamp;
        $end   = Carbon::parse($day)->endOfDay()->timestamp;

        #获取目录中的所有图片
        $ppp = 'task_atlas_no_sy';
        $directories=$this->scanAll(public_path($ppp).DIRECTORY_SEPARATOR.$day2,$day2);

        $logs = DB::table('task_log_no_sies')
            ->whereBetWeen('created_at',[$start,$end])
            ->select('id','user_id','atlas')
            ->get()->toArray();

        foreach ($logs as $k=>$v)
        {
            $s = json_decode($v->atlas,1);
            if(count($s) == 2){
                if(!empty($s[1])){
                    $temp = explode('/',$s[1])[3];
                    $t = explode('_',$temp);
                    $user_id = $t[0];
                    $timep   = $t[2];
                    echo  $user_id.' -- '.$timep."\n";
                    foreach ($directories as $kk=>$vv){
                        if (strpos($vv, $timep) !== false  && strpos($vv,$user_id) !== false) {
                            if($temp != $vv){
                                $s[] = $vv;
                                DB::table('task_log_no_sies')
                                    ->where(['id'=>$v->id])->update([
                                        'atlas' =>json_encode($s,JSON_UNESCAPED_SLASHES)
                                    ]);
                            }
                        }
                    }
                }
            }
        }

        return 0;
    }

    /**
     * 遍历某个目录下的所有文件
     * @param string $dir
     */
    function scanAll($dir,$day2)
    {

        $children = scandir($dir);
        $img_array = [];
        foreach ($children as $child)
        {
            if ($child !== '.' && $child !== '..')
            {
                $img_array[] = $child;
            }
        }
        return $img_array;
    }


}
