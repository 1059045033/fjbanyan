<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;
    protected $dateFormat = 'U';

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getlist($params=[])
    {
        return self::where(['is_show'=>1])->select('id','name','content','cover','type','url','created_at')->paginate($params['size'] ?? 10);
    }
}
