<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Banner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            // 管理员
            $admins=[
                [
                    'name'=>'admin',
                    'phone'=>'15860816380',
                    'avator'=>'/images/admin/f778738c-e4f8-4870-b634-56703b4acafe.gif',
                    'company_id'=>1,
                    'role'=>30,
                    'image_base64'=>''
                ],
            ];
            foreach ($admins as $k=>$v){
                Admin::create([
                    'name' => $v['name'],
                    'phone' => $v['phone'],
                    'avator' => $v['avator'],
                    'company_id' => 1,
                    'password' => bcrypt('123456'),
                    'role' => $v['role'],
                    'image_base64' => '',
                ]);
            }
        });
    }
}
