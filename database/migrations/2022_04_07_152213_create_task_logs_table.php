<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->comment("用户id");
            $table->string('position')->comment('位置 {lag:4,lat:5,h:8}');
            $table->integer('task_id')->comment('任务ID');
            $table->text('content')->comment('内容')->nullable();
            $table->string('atlas')->comment('图集 []');
            $table->integer('is_effective')->comment('是否有效 [0无效 1有效]');
            $table->unsignedInteger('day_first_time')->comment('当天第一条有效任务的时间');

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
        Schema::dropIfExists('task_logs');
    }
}
