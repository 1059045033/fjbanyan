<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOnlineOfflinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('online_offlines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->comment("用户id");
            $table->integer('type')->comment('[ 1 : 上线  2 : 下线 ]');
            $table->string('position')->comment('位置 {lag:4,lat:5,h:8}');
            $table->string('address')->comment('详细地址');
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
        Schema::dropIfExists('online_offlines');
    }
}
