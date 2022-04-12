<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Banner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            // 轮播表
            $banners=[
                ['name'=>'福州自驾游','cover'=>'/imgs/fzzjy.png','content'=>'福州自驾游','type'=>1,'url'=>null],
                ['name'=>'安全行动','cover'=>'/imgs/aqxd.png','content'=>'安全行动','type'=>1,'url'=>null],
                ['name'=>'福州西湖','cover'=>'/imgs/fzxh.png','content'=>null,'type'=>2,'url'=>'https://baike.baidu.com/item/%E7%A6%8F%E5%B7%9E%E8%A5%BF%E6%B9%96%E5%85%AC%E5%9B%AD/8597500?fromtitle=%E7%A6%8F%E5%B7%9E%E8%A5%BF%E6%B9%96&fromid=1285543&fr=aladdin'],
            ];
            foreach ($banners as $k=>$v){
                Activity::create([
                    'name' => $v['name'],
                    'cover' => config('app.url').$v['cover'],
                    'content' => $v['content'],
                    'type' => $v['type'],
                    'url' => $v['url'],
                ]);
            }
        });
    }
}
