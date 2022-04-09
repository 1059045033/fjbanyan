<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityMsgsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_msgs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment("活动名称");
            $table->text('content')->comment('消息内容')->nullable();
            $table->string('cover')->comment('封面');
            $table->integer('is_read')->default(0)->comment("[ 0 : 未读  1: 已读]");
            $table->unsignedInteger('read_time')->comment("读取时间");


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
        Schema::dropIfExists('activity_msgs');
    }
}
