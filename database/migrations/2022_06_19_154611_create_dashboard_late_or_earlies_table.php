<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDashboardLateOrEarliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dashboard_late_or_earlies', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('迟到早退 -记录表');
            $table->integer('user_id')->comment("人员id");
            $table->string('user_name')->comment("人员姓名")->nullable();
            $table->string('user_phone')->comment("人员电话")->nullable();
            $table->integer('user_role')->comment("人员等级")->nullable();
            $table->string('user_region')->comment("人员所属区域")->nullable();
            $table->string('user_work_region')->comment("人员今天工作区域")->nullable();
            $table->string('company')->comment("人员公司")->nullable();
            $table->string('company_id')->comment("人员公司id")->nullable();
            $table->string('type')->default('0')->comment('[1迟到,2早退]');

            $table->unsignedInteger('date_day')->comment('记录的日期(日)')->nullable();

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
        Schema::dropIfExists('dashboard_late_or_earlies');
    }
}
