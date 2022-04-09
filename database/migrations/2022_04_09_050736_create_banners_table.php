<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('banner表');
            $table->string('name')->comment("名称");
            $table->text('content')->comment('消息内容')->nullable();
            $table->string('cover')->comment('封面');
            $table->integer('type')->default(1)->comment("类型 [1:站内活动 2:外部链接]");
            $table->string('url')->comment('链接')->nullable();
            $table->integer('sort')->default(10)->comment('排序');
            $table->integer('is_show')->default(0)->comment('是否显示 [0显示|1不显示]');
            $table->integer('read_num')->default(0)->comment('查看数量');

            $table->unsignedInteger('created_at');
            $table->unsignedInteger('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banners');
    }
}
