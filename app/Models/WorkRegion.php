<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkRegion extends Model
{
    use HasFactory,SoftDeletes;
    protected $dateFormat = 'U';
    protected $guarded = [];
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

    public function regionManagerInfo()
    {
        return $this->hasOne(User::class,'id','region_manager');
    }

    public function regionUsers()
    {
        return $this->hasMany(User::class,'region_id','id');
    }

    public function groupInfo()
    {
        return $this->belongsTo(RegionGroup::class,'group_id','id');
    }

}
