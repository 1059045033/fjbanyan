<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('姓名');

            $table->string('phone')->unique()->comment('手机号码');
            $table->string('company_id')->comment('公司');
            $table->string('password')->default(bcrypt('123456'));
            $table->integer('role')->default(10)->comment('10:工作人员 20:区域管理 30:超级管理人员');
            $table->integer('is_online')->default(0)->comment('是否上线 [0offline 1online]');
            $table->text('image_base64')->comment('存放人脸图片');
            $table->string('avator')->default('')->default()->comment('头像');

            $table->rememberToken();

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
        Schema::dropIfExists('admins');
    }
}
