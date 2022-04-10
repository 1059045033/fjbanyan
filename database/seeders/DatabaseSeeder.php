<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Models\VersionControl;
use App\Models\WorkRegion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        //\App\Models\User::factory(10)->create();
        DB::transaction(function () {
            if (User::count() === 0) {
                // 填充公司表
                $company_id = Company::create([
                    'name' => '福州勘测有限公司'
                ])->id;

                // 用户表
                $users=[
                        ['name'=>'admin','phone'=>'18046032888','role'=>'30'],
                        ['name'=>'周星驰','phone'=>'15860816380','role'=>'20'],
                        ['name'=>'王祖贤','phone'=>'15860816381','role'=>'20'],
                        ['name'=>'刘德华','phone'=>'1370042235','role'=>'10'],
                        ['name'=>'吴彦祖','phone'=>'18046032876','role'=>'10']
                    ];
                foreach ($users as $k=>$v){
                    User::create([
                        'name' => $v['name'],
                        'phone' => $v['phone'],
                        'company_id' => $company_id,
                        'password'=>bcrypt('123456'),
                        'role'=>$v['role'],
                        'image_base64'=>'',
                        'avator'=>''
                    ]);
                }

                // 版本控制
                VersionControl::create([
                    'version'=>'1',
                    'version_name'=>'v1.o',
                    'description'=>'init',
                    'update_url'=>'http://www.baidu.com',
                    'update_force'=>0,
                ]);

                // 区域表
                $regions = [
                        ['name'=>'福建大剧院','scope'=>[
                            ['lng'=>119.313369, 'lat'=>26.082198],['lng'=>119.313549, 'lat'=>26.079017],['lng'=>119.316172, 'lat'=>26.079017],['lng'=>119.315956, 'lat'=>26.082133],['lng'=>119.314735, 'lat'=>26.082133]]],
                        ['name'=>'福建医科大学(台江)','scope'=>[
                                ['lng'=>119.298134, 'lat'=>26.077979],
                                ['lng'=>119.30356, 'lat'=>26.077395],
                                ['lng'=>119.310028, 'lat'=>26.077719],
                                ['lng'=>119.311285, 'lat'=>26.073046],
                                ['lng'=>119.300254, 'lat'=>26.072138]
                            ]
                        ],
                        ['name'=>'上下杭','scope'=>[
                                ['lng'=>119.306794, 'lat'=>26.058344],
                                ['lng'=>119.315597, 'lat'=>26.064089],
                                ['lng'=>119.320053, 'lat'=>26.058344],
                                ['lng'=>119.309632, 'lat'=>26.053086]
                            ]
                        ]

                ];

                foreach ($regions as $k=>$v){
                    WorkRegion::create([
                        'name'=>$v['name'],
                        'region_scope' => json_encode($v['scope'])
                    ]);
                }

                //外部链接
                $external_links = [
                    ['name'=>'隐私协议','url'=>'http://www.baidu.com'],
                    ['name'=>'帮助说明','url'=>'http://www.baidu.com'],
                    ['name'=>'安全教育','url'=>'http://www.baidu.com'],
                    ['name'=>'轮播图链接','url'=>'http://www.baidu.com']
                ];
            }
        });

    }
}
