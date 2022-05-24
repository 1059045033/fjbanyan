<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVersionControlsIntoUpdateTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('version_controls', function (Blueprint $table) {
            $table->integer('update_type')->default(0)->comment("[0:所有 1:3级别 2：一二级别]");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('version_controls', function (Blueprint $table) {
            //
        });
    }
}
