<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $dateFormat = 'U';
    protected $guarded = [];

    protected $appends = ['status'];

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
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

    public function getStatusAttribute($value)
    {
        $status = 0 ;// [0:未知  1:待接收  2:待执行  3:完成]
        if(empty($this->attributes['complete_user']))
        {
            $status = 1 ;

        }else{
            if($this->attributes['is_complete'] == 0)
            {
                $status = 2 ;
            }else{
                $status = 3 ;
            }
        }
        return $status;
    }

    public function completeUserInfo()
    {
        return $this->belongsTo(User::class,'complete_user','id');//->select(['name']);
    }



    public function getlist($params=[],$user_id = 0)
    {
        $fillter = [];
        !empty($user_id) && $fillter['create_user'] = $user_id;
        return self::where($fillter)->select('id as task_id','content','atlas','position','address','is_complete','complete_time','complete_user','created_at','content','business_district')->orderByDesc('created_at')->paginate($params['size'] ?? 10);
    }

}
