<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
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

    public function toArray()
    {
        $array = parent::toArray();
        isset($array['position']) && $array['position'] = json_decode($array['position'],1);

        return $array;
    }

    public function getlist($params=[],$user_id = 0)
    {
        $fillter = [];
        !empty($user_id) && $fillter['user_id'] = $user_id;

        return self::where($fillter)->when(!empty($params['start_date']), function ($query) use($params){
            $tt = strtotime($params['start_date']);
            $data['start'] = strtotime(date('Y-m-d 00:00:00',$tt));
            $data['end']   = strtotime(date('Y-m-d 23:59:59',$tt));
            $query->whereBetween('created_at', [$data['start'], $data['end']]);
        })->select('id as track_id','position','address','created_at')->orderByDesc('created_at')->get();
    }
}
