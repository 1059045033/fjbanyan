<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExternalLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('external_links', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('外部链接表');
            $table->integer('name')->comment("外部链接名称");
            $table->string('url')->comment('链接');
            $table->integer('sort')->default(10)->comment('排序');
            $table->integer('is_show')->default(0)->comment('是否显示 [0显示|1不显示]');
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
        Schema::dropIfExists('external_links');
    }
}
