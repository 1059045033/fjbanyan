<?php

namespace App\Console\Commands;

use App\Models\WorkNotice as WorkNoticeModel;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class Test1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:bc {--day=}';

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

        #获取目录中的所有图片
        $ppp = 'task_atlas_no_sy';
        $directories=$this->scanAll(public_path($ppp).DIRECTORY_SEPARATOR.$day2,$day2);

        die;


        # 日期的起止时间戳
        //$day   = '2022-07-16';//empty($date_day) ? date('Y-m-d'):$date_day;
        $start = Carbon::parse($day)->startOfDay()->timestamp;
        $end   = Carbon::parse($day)->endOfDay()->timestamp;

        echo "补充 {$day} 当天数据 \n";
        foreach ($directories as $k=>$v)
        {
            $logs = DB::table('task_log_no_sies')
                ->where(['user_id'=>$k])
                ->whereBetWeen('created_at',[$start,$end])
                ->select('id')
                ->get()->toArray();
            echo "用户 {$k} 当天数据 有".count($logs)." 上传的数据有".count($v)."组 \n";
            foreach ($v as $kk=>$vv)
            {
                $temp = array_shift($logs);
                if($temp)
                {
                    DB::table('task_log_no_sies')
                        ->where(['id'=>$temp->id])->update([
                            'atlas' =>json_encode($vv,JSON_UNESCAPED_SLASHES)
                        ]);
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
        $list = array();
        $list[] = $dir;
        $res = [];

        $children = scandir($dir);
        $img_array = array();
        $img_dsort = array();
        $final_array = array();

        foreach ($children as $child)
        {
            if ($child !== '.' && $child !== '..')
            {

                $img_array[] = $dir.'/'.$child;
                $img_dsort[] = filemtime($dir . '/' . $child);
            }
        }
        $merge_arrays = array_combine($img_dsort, $img_array);
        krsort($merge_arrays);
        foreach($merge_arrays as $key => $value)
        {
            $final_array[] = $value;
        }

        foreach ($final_array as $k => $v){
            $file = basename($v, ".jpg");
            $fff = explode('_',$file);
            echo date('H:i:s',$fff[2])."\n";
        }

        die;


        while (count($list) > 0)
        {
            //弹出数组最后一个元素
            $file = array_pop($list);
            //如果是目录
            if (is_dir($file))
            {
                $children = scandir($file);
                foreach ($children as $child)
                {
                    if ($child !== '.' && $child !== '..')
                    {
                        $list[] = $file.'/'.$child;
                    }
                }
            }else{
                //处理当前文件
                $file = basename($file, ".jpg");
                $fff = explode('_',$file);
                $user_id =$fff[0];
                $res[$user_id][$fff[2]] =  "/task_atlas_no_sy/".$day2."/".$file.'.jpg';
            }
        }

        print_r($res[203]);
        die;
        # 排序
        foreach ($res as $k=>&$v)
        {

        }
        die;

        $newRes = [];
        foreach ($res as $k=>$v)
        {
            foreach ($v as $kk=>$vv)
            {
                $temp[] = $vv;
                if(count($temp) == 3)
                {
                    $newRes[$k][] = $temp;
                    $temp = [];
                }
            }
            if(!empty($temp)){
                $newRes[$k][] = $temp;
            }
        }
        return $newRes;die;
    }


    function dir_size($dir,$url){
        $dh = @opendir($dir);             //打开目录，返回一个目录流
        $return = array();
        $i = 0;
        while($file = @readdir($dh)){     //循环读取目录下的文件
            if($file!='.' and $file!='..'){
                $path = $dir.'/'.$file;     //设置目录，用于含有子目录的情况
                if(is_dir($path)){
                }elseif(is_file($path)){
                    $filesize[] =  round((filesize($path)/1024),2);//获取文件大小
                    $filename[] = $path;//获取文件名称
                    $filetime[] = date("Y-m-d H:i:s",filemtime($path));//获取文件最近修改日期
                    $return[] =  $url.'/'.$file;
                }
            }
        }

        @closedir($dh);             //关闭目录流
        // array_multisort($filesize,SORT_DESC,SORT_NUMERIC, $return);//按大小排序
        // array_multisort($filename,SORT_DESC,SORT_STRING, $files);//按名字排序
         array_multisort($filetime,SORT_DESC,SORT_STRING, $return);//按时间排序
        return $return;               //返回文件

    }
}
