<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExceptionMsg extends Model
{
    use HasFactory;
    protected $dateFormat = 'U';
    protected $guarded = [];


    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
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
        })->select('id as exception_msg_id','type','content','created_at')->orderByDesc('created_at')->get();
    }
}
