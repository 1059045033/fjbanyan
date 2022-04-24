<?php

namespace App\Http\Controllers\Admin;

use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CompanyController extends Controller
{
    public function lists(Request $request)
    {
        $list = Company::select('id','name')->get()->toArray();
        $result = [
            'total' => count($list),
            'items' => $list
        ];
        return $this->myResponse($result,'',200);
    }
}
