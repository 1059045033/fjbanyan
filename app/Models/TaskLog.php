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
}
