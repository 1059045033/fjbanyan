<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkNotice extends Model
{
    use HasFactory;
    protected $dateFormat = 'U';

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
