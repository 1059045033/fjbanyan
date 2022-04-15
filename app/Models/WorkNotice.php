<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkNotice extends Model
{
    use HasFactory;
    protected $dateFormat = 'U';

    protected $guarded = [];

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getlist($params=[],$user_id = 0)
    {
        $fillter = [];
        !empty($user_id) && $fillter['user_id'] = $user_id;
        return self::where($fillter)->select('id as notice_id','name','content','is_read','created_at')->orderByDesc('created_at')->paginate($params['size'] ?? 10);
    }
}
