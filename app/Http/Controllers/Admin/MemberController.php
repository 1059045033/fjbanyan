<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\User;
use App\Models\WorkingTime;
use App\Services\JPushService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    private  $admin = null;
    public function __construct(Request $request)
    {
        $token = $request->header('X-Token');
        $this->admin  =  Admin::where(['remember_token' => $token])->first();

    }

    public function lists(Request $request)
    {
        $search = $request->query('name');
        $sort = 'asc';
        $fillter = [];
       // $request->query('name') && $fillter['name'] = $request->query('name');
        $request->query('type') && $fillter['role'] = $request->query('type');

        $request->query('sort') == '-id' && $sort = 'desc';
        $page = $request->query('page') ?? 1;
        $limit = $request->query('limit') ?? 10;

        !empty( $request->query('id')) && $fillter['id'] = $request->query('id');

        $total = User::where($fillter)->when(!empty($search), function ($query) use($search){
            $query->where('name','like','%'.$search.'%')->orWhere('phone','like','%'.$search.'%');
        })->count();

        $list = User::with(['company','Region:id,name','workingTime'])
            ->where($fillter)
            ->when(!empty($search), function ($query) use($search){
                $query->where('name','like','%'.$search.'%')->orWhere('phone','like','%'.$search.'%');
            })
            ->select('id','name','avator','created_at','phone','image_base64','company_id','region_id','role','ID_Card','email','address','emergency_contact','emergency_contact_phone')
            ->orderBy('id',$sort)->forPage($page,$limit)->get();
        $result = [
            'total' => $total,
            'items' => $list
        ];
        return $this->myResponse($result,'',200);
    }


    public function edit(Request $request)
    {
        $request->validate([
            'role'    => 'required|in:10,20,30',
            'company' => 'required|exists:companies,id',
            'name'    => 'required',
            'phone'   => 'required'
        ],[
            'role.*' => '??????????????????',
            'company.required' => '??????????????????111',
            'company.exists' => '???????????????',
            'name' => '???????????????',
            'phone.required' => '????????????',
        ]);

        $userInfo = User::find($request->id);
        if($request->phone != $userInfo->phone){
            $res = DB::table('users')->where('phone',$request->phone)->where('id','<>',$request->id)->first();
            if(empty($res)){
                $userInfo->phone = $request->phone;
            }else{
                return $this->myResponse([],'????????????????????????',423);
            }
        }

        if($request->name != $userInfo->name){
            $userInfo->name = $request->name;
        }
        if($request->company != $userInfo->company_id){
            $userInfo->company_id = $request->company;
        }
        if($request->role != $userInfo->role){
            $userInfo->role = intval($request->role);
        }

        if(!empty($request->region) && $request->region != $userInfo->region_id){
            $userInfo->region_id = $request->region;
        }

        if($userInfo->save())
        {
            $new_user = User::with(['company','Region:id,name'])
                ->where('id',$request->id)
                ->first();

            # ======== ???????????? start ===============
            $desc = "???{$this->admin['name']}??? ??? ".date('Y-m-d H:i:s')."???????????????????????????????????????{$new_user['name']}???";
            $this->recordLogs($request,2,$this->admin,$desc);
            # ======== ???????????? end   ===============

            return $this->myResponse($new_user,'????????????',200);
        }
        return $this->myResponse([],'????????????',423);
    }

    public function create(Request $request)
    {
        $request->validate([
            'role'    => 'required|in:10,20,30',
            'company' => 'required|exists:companies,id',
            'name'    => 'required',
            'phone'   => 'required'
        ],[
            'role.*' => '??????????????????',
            'company.*' => '??????????????????',
            'name' => '???????????????',
            'phone.required' => '????????????',
            'phone.unique' => '??????????????????',
        ]);

        $res = User::where(['phone'=>$request->phone])->first();
        if(!empty($res)){
            return $this->myResponse([],'??????????????????',423);
        }

        if($new_id = User::create([
            'name'=>$request->name,
            'company_id'=>$request->company,
            'role'=>$request->role,
            'phone'=>$request->phone,
            'password'=>bcrypt('123456'),
            'image_base64'=>'',
            'region_id'=>$request->region,
            'avator'=>'',
            'ID_Card'=>$request->ID_Card,
            'email'=>$request->email,
            'address'=>$request->address,
            'emergency_contact'=>$request->emergency_contact,
            'emergency_contact_phone'=>$request->emergency_contact_phone,

            ])->id){
            $new_user = User::with(['company','Region:id,name'])
                ->where('id',$new_id)
                ->first();

            # ======== ???????????? start ===============
            $desc = "???{$this->admin['name']}??? ??? ".date('Y-m-d H:i:s')."???????????????????????????????????????{$new_user['name']}???";
            $this->recordLogs($request,1,$this->admin,$desc);
            # ======== ???????????? end   ===============

            # ======== ???????????????????????? start ===============
            WorkingTime::create([
                'user_id' => $new_id,
                'name' => '??????',
                'start_time' => '07:00',
                'end_time' => '11:00',
            ]);

            WorkingTime::create([
                'user_id' => $new_id,
                'name' => '??????',
                'start_time' => '14:00',
                'end_time' => '18:00',
            ]);
            # ======== ???????????????????????? end   ===============


            return $this->myResponse($new_user,'????????????',200);
        }
        return $this->myResponse([],'????????????',423);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id'
        ]);

        $o_user = User::find($request->id);
        if(empty($o_user)){
            return $this->myResponse([],'??????????????????',423);
        }
        if($o_user->delete()){
            # ======== ???????????? start ===============
            $desc = "???{$this->admin['name']}??? ??? ".date('Y-m-d H:i:s')."???????????????????????????????????????{$o_user['name']}???";
            $this->recordLogs($request,3,$this->admin,$desc);
            # ======== ???????????? end   ===============

            return $this->myResponse([],'????????????',200);
        }
        return $this->myResponse([],'????????????',423);
    }

}
