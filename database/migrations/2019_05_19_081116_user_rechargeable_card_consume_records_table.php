<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserRechargeableCardConsumeRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_rechargeable_card_consume_records', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('user_id')->comment('用户会员ID');

            $table->unsignedInteger('customer_id')->comment('用户ID');

            $table->unsignedInteger('order_id')->comment('订单ID');

            $table->unsignedInteger('rechargeable_card_id')->comment('卡片ID');

            $table->unsignedInteger('consume')->default(0)->comment('消费金额，单位：分');

            $table->unsignedInteger('save')->default(0)->comment('节省金额，单位：分');

            $table->timestamps();
        });

        DB::statement("alter table `user_rechargeable_card_consume_records` comment '用户卡片消费记录'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}