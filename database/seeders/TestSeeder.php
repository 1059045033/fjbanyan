<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        一级账号	陈毅超	3	15705994656
//        一级账号	黄孝清	4	15005061271
//        一级账号	林东彬	5	15805003957
//        一级账号	肖达	    6	13075852588
//        一级账号	顾轩铭	6	13075876088
//        一级账号	林康雄	6	15306976441

//        二级账号	苑光升	7	13075888006
//        二级账号	鲍荣平	8	18859117983

//        三级账号	吴伟星	7	15080027063
//        三级账号	赵志强	8	18293948452

        // 用户表
        $users=[
            ['name'=>'陈毅超','phone'=>'15705994656','role'=>'30','company'=>3],
            ['name'=>'黄孝清','phone'=>'15005061271','role'=>'30','company'=>4],
            ['name'=>'林东彬','phone'=>'15805003957','role'=>'30','company'=>5],
            ['name'=>'肖达','phone'=>'13075852588','role'=>'30','company'=>6],
            ['name'=>'顾轩铭','phone'=>'13075876088','role'=>'30','company'=>6],
            ['name'=>'林康雄','phone'=>'15306976441','role'=>'30','company'=>6],

            ['name'=>'苑光升','phone'=>'13075888006','role'=>'20','company'=>7],
            ['name'=>'鲍荣平','phone'=>'18859117983','role'=>'20','company'=>8],

            ['name'=>'吴伟星','phone'=>'15080027063','role'=>'10','company'=>7],
            ['name'=>'赵志强','phone'=>'18293948452','role'=>'10','company'=>8]
        ];
        foreach ($users as $k=>$v){
            User::create([
                'name' => $v['name'],
                'phone' => $v['phone'],
                'company_id' => $v['company'],
                'password'=>bcrypt('123456'),
                'role'=>$v['role'],
                'image_base64'=>'',
                'avator'=>''
            ]);
        }
    }
}
