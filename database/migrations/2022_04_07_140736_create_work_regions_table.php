<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkRegionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_regions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment("区域名称");
            $table->text('region_scope')->comment("区域范围");
            $table->integer('region_manager')->comment("区域经理")->nullable();
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
        Schema::dropIfExists('work_regions');
    }
}
