<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;
    protected $dateFormat = 'U';

    public function getlist($params=[])
    {
        return self::where(['is_show'=>1])->select('id','name','content','cover','type','url')->paginate($params['size'] ?? 10);
    }
}
