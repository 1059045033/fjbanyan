<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGroupIdToDashboardLateOrEarliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dashboard_late_or_earlies', function (Blueprint $table) {
            $table->string('region_group')->default(0)->comment("人员所属区域的组")->nullable();
            $table->string('work_region_group')->default(0)->comment("人员今天工作区域的组")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dashboard_late_or_earlies', function (Blueprint $table) {
            //
        });
    }
}
