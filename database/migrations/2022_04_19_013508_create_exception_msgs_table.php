<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExceptionMsgsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exception_msgs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->comment("用户id");
            $table->text('content')->comment('内容')->nullable();
            $table->integer('type')->default(1)->comment('类型 [1迟到 2早退 3其他异常]');
            $table->integer('is_read')->default(0)->comment('查阅 [0未看 1查阅]');

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
        Schema::dropIfExists('exception_msgs');
    }
}
