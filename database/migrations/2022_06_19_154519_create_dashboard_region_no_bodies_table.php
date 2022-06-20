<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDashboardRegionNoBodiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dashboard_region_no_bodies', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('网格缺岗-记录表');
            $table->integer('group_id')->comment("网格组id");
            $table->string('group_name')->comment("网格组名字");
            $table->integer('body_nums')->default(0)->comment("网格到岗人数")->nullable();
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
        Schema::dropIfExists('dashboard_region_no_bodies');
    }
}
