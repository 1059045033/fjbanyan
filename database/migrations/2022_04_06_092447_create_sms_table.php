<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code',10)->comment("验证码");
            $table->string('mobile')->unique();
            $table->integer('type')->default(0)->comment("0:登入");
            $table->unsignedInteger('expire_time')->comment('过期时间');;
            $table->unsignedInteger('created_at');
            $table->unsignedInteger('updated_at');


            //$table->softDeletes();
            //$table->id();
            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms');
    }
}
