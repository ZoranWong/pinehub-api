<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('wechat_app_id', 32)->nullable()->default(null)->comment('系统app id');
            $table->string('template_id', 16)->nullable()->default(null)->comment('模版消息ID');
            $table->string('to_user_id', 28)->comment('推送对象');
            $table->string('from_user_id', 28)->comment('消息发送对象');
            $table->string('msg_type', 16)->comment('消息类型');
            $table->unsignedInteger('create_time')->comment('创建时间');
            $table->json('content')->comment('消息内容');
            $table->unsignedTinyInteger('message_type')->comment('0-接收微信系统消息，1-平台向用户推送消息');
            $table->timestamps();
            $table->index('template_id');
            $table->index('to_user_id');
            $table->index('from_user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::drop('messages');
    }
}
