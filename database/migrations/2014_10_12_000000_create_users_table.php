<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('姓名');

            $table->string('phone')->unique()->comment('手机号码');
            $table->string('company_id')->comment('公司');
            $table->string('work_region_id')->comment('工作区域')->nullable();
            $table->string('password')->default(bcrypt('123456'));
            $table->string('ID_Card')->comment('身份证号')->nullable();
            $table->string('ID_Card_img')->comment('身份证照片')->nullable();
            $table->string('emergency_contact')->comment('紧急联系人')->nullable();
            $table->string('emergency_contact_phone')->comment('紧急联系人电话')->nullable();
            $table->integer('role')->default(10)->comment('10:工作人员 20:区域管理 30:超级管理人员');
            $table->string('province')->comment('省')->nullable();
            $table->string('city')->comment('市')->nullable();
            $table->string('district')->comment('区')->nullable();
            $table->string('address')->comment('地址')->nullable();
            $table->integer('is_online')->default(0)->comment('是否上线 [0offline 1online]');

            $table->text('image_base64')->comment('存放人脸图片');
            $table->string('avator')->default('')->comment('头像');


            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            //$table->timestamps();

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
        Schema::dropIfExists('users');
    }
}
