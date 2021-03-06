<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskLog extends Model
{
    use HasFactory;
    protected $dateFormat = 'U';
    protected $guarded = [];

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function userInfo()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function workRegionInfo()
    {
        return $this->belongsTo(WorkRegion::class,'work_region_id','id');
    }

    public function noSyInfo()
    {
        return $this->hasOne(TaskLogNoSy::class,'task_log_id','task_log_id');
    }

    public function toArray()
    {
        $array = parent::toArray();

        if(isset($array['atlas'])){
            $atlas = json_decode($array['atlas'],1);
            foreach ($atlas as $k=>&$v){
                $v = config('app.url').$v;
            }
            $array['atlas'] = $atlas;
        }

        isset($array['position']) && $array['position'] = json_decode($array['position'],1);

        return $array;
    }

    public function getlist($params=[],$user_id = 0)
    {
        $fillter = [];
        !empty($user_id) && $fillter['user_id'] = $user_id;
        !empty($params['user_id']) && $fillter['user_id'] = $params['user_id'];
        return self::where($fillter)->when(!empty($params['start_date']), function ($query) use($params){//with('noSyInfo:task_log_id,atlas')->
                $tt = strtotime($params['start_date']);
                $data['start'] = strtotime(date('Y-m-d 00:00:00',$tt));
                $data['end']   = strtotime(date('Y-m-d 23:59:59',$tt));
                $query->whereBetween('created_at', [$data['start'], $data['end']]);
                })->select('id as task_log_id','atlas','position','address','is_effective','task_id','type','created_at','content','business_district')->orderByDesc('created_at')->paginate($params['size'] ?? 10);
    }
}
