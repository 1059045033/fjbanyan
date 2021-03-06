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

            $table->text('content')->comment('内容')->nullable();
            $table->string('atlas')->comment('图集 []');

            $table->string('position')->comment('位置 {lag:4,lat:5,h:8}');
            $table->string('address')->comment('地址')->nullable();

            $table->integer('is_effective')->comment('是否有效 [0无效 1|2有效(一小时内的第一条)]');

            $table->integer('task_id')->comment('任务ID')->nullable();

            $table->integer('type')->default(1)->comment('是否有效 [1日常 2指派]');

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
