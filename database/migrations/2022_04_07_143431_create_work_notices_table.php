<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_notices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment("任务名称");
            $table->string('type')->comment("[ 1 : 日常任务  2 : 派发任务]");
            $table->integer('is_read')->default(0)->comment("[ 0 : 未读  1: 已读]");
            $table->unsignedInteger('read_time')->comment("读取时间")->nullable();
            $table->text('content')->comment('消息内容')->nullable();
            $table->string('atlas')->comment('图集 []')->nullable();
            $table->string('cover')->comment('封面')->nullable();
            $table->string('position')->comment('位置 {lag:4,lat:5,h:8}')->nullable();
            $table->string('address')->comment('具体地址')->nullable();
            $table->integer('is_accept')->comment('是否被接受 [ 0 :未  1:已 ] ')->nullable();
            $table->unsignedInteger('accept_time')->comment('接受任务的是时间')->nullable();
            $table->integer('author')->comment('通知产生者')->nullable();
            $table->integer('user_id')->comment('通知对象');
            $table->integer('task_id')->default(0)->comment('关联的任务ID');
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
        Schema::dropIfExists('work_notices');
    }
}
