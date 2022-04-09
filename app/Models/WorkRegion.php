<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkRegion extends Model
{
    use HasFactory;
    protected $dateFormat = 'U';

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function toArray()
    {
        $array = parent::toArray();

        isset($array['region_scope']) && $array['region_scope'] = json_decode($array['region_scope'],1);

        return $array;
    }
}
