<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVersionControlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('version_controls', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('版本控制表');
            $table->integer('version')->comment("版本号");
            $table->string('version_name')->comment('版本名称');
            $table->string('description')->comment('描述');
            $table->integer('os')->default(1)->comment('[1:android  2:ios]');
            $table->string('update_url')->comment('更新地址')->nullable();
            $table->integer('update_force')->comment('是否强制更新 [0不 1强制]');
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
        Schema::dropIfExists('version_controls');
    }
}
