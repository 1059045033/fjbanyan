<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskLogNoSiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_log_no_sies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->comment("用户id");
            $table->string('atlas')->comment('图集 []');
            $table->string('task_log_id')->default(0)->comment('任务的id')->nullable();
            $table->string('type')->default(0)->comment('0无水印 1补了水印')->nullable();

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
        Schema::dropIfExists('task_log_no_sies');
    }
}
