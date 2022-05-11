<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOptRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opt_records', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("后台人员操作记录表");
            $table->integer('opt_user_id')->comment("操作人员id");
            $table->string('opt_user_name')->comment("操作人员账号名")->nullable();
            $table->string('ip')->comment("登入的ip")->nullable();
            $table->string('agent')->comment("请求的User-Agent")->nullable();
            $table->integer('type')->comment("操作类型[1:新增 2:编辑 3:删除 4:登入 5:退出]")->nullable();
            $table->text('desc')->comment("相关描述")->nullable();
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
        Schema::dropIfExists('opt_records');
    }
}
