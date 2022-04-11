<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('type')->comment("任务类型 [ 1 : 图文 + 位置      2 ：图 + 位置]");
            $table->text('content')->comment('任务内容')->nullable();
            $table->string('atlas')->comment('图集 []');
            $table->string('position')->comment('位置 {lag:4,lat:5,h:8}');

            $table->integer('is_complete')->default(0)->comment("任务是否完成  [ 0 : 未完成 1 : 完成]");
            $table->unsignedInteger('complete_time')->comment("完成时间")->nullable();

            $table->unsignedInteger('complete_user')->comment("任务执行者")->nullable();
            $table->unsignedInteger('create_user')->comment("任务创建者")->nullable();
            $table->unsignedInteger('region_id')->comment("任务所属区域")->nullable();


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
        Schema::dropIfExists('tasks');
    }
}
