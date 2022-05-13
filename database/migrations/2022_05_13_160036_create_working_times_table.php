<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkingTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('working_times', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("人员上班时间表");
            $table->integer('user_id')->comment("人员id");
            $table->string('name')->comment("时间段别名[eg 早班]")->nullable();
            $table->string('start_time')->comment("上班时间[eg 07:00]")->nullable();
            $table->string('end_time')->comment("下班时间[eg 11:00]")->nullable();
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
        Schema::dropIfExists('working_times');
    }
}
