<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 出勤记录表
        Schema::create('attendances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->comment("人员id");
            $table->string('user_name')->comment("人员姓名")->nullable();
            $table->string('user_phone')->comment("人员电话")->nullable();
            $table->string('user_company')->comment("人员公司")->nullable();
            $table->integer('user_role')->comment("人员等级")->nullable();
            $table->string('user_region')->comment("人员所属区域")->nullable();
            $table->string('user_work_region')->comment("人员今天工作区域")->nullable();
            $table->text('online_times')->comment("上线时间集合")->nullable();
            $table->text('offline_times')->comment("下线时间集合")->nullable();
            $table->integer('task_complete_nums')->comment("任务完成量")->nullable();
            $table->string('task_progress')->comment("任务完进度")->nullable();
            $table->integer('late_nums')->comment("迟到次数")->nullable();
            $table->integer('early_nums')->comment("早退次数")->nullable();
            $table->integer('task_dd_nums')->comment("任务段档次数")->nullable();
            $table->integer('region_not_user_nums')->comment("网格无人员出勤 ")->nullable();
            $table->float('money')->comment("扣款金额 ")->nullable();
            $table->string('money_details')->comment("扣款详情")->nullable();
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
        Schema::dropIfExists('attendances');
    }
}
