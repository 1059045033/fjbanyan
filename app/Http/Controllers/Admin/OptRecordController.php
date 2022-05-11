<?php

namespace App\Http\Controllers\Admin;

use App\Models\OptRecord;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OptRecordController extends Controller
{
    public function lists(Request $request)
    {
        $search = $request->query('name');
        $sort = 'asc';
        $fillter = [];
        //$request->query('name') && $fillter['name'] = $request->query('name');

        $request->query('sort') == '-id' && $sort = 'desc';
        $page = $request->query('page') ?? 1;
        $limit = $request->query('limit') ?? 10;

        $total = OptRecord::where($fillter)->when(!empty($search), function ($query) use($search){
            $query->where('desc','like','%'.$search.'%');
        })->count();

        $list = OptRecord::when(!empty($search), function ($query) use($search){
            $query->where('desc','like','%'.$search.'%');
        })->orderBy('id',$sort)->forPage($page,$limit)->get();

        $result = [
            'total' => $total,
            'items' => $list
        ];
        return $this->myResponse($result,'',200);
    }
}
