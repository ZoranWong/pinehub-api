<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Entities\Order;

class CreateStorePurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_purchase_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 16)->comment('订单编号');
            $table->string('open_id', 64)->nullable()->default(null)->comment('微信open id或支付宝user ID');
            $table->string('wechat_app_id', 32)->nullable()->default(null)->comment('维系app id');
            $table->string('ali_app_id', 32)->nullable()->default(null)->comment('支付宝app id');
            $table->string('app_id', 16)->nullable()->default(null)->comment('系统app id');
            $table->unsignedInteger('shop_id')->nullable()->default(null)->comment('店铺id');
            $table->unsignedInteger('merchandise_num')->nullable()->default(null)->comment('此订单商品数量总数');
            $table->float('total_amount')->default(0)->comment('应付款');
            $table->float('payment_amount')->default('0')->comment('实际付款');
            $table->float('discount_amount')->default(0)->comment('优惠价格');
            $table->timestamp('paid_at')->nullable()->default(null)->comment('支付时间');
            $table->unsignedTinyInteger('pay_type')->default(Order::WECHAT_PAY)
                ->comment('支付方式默认微信支付:0-未知，1-支付宝，2-微信支付');
            $table->unsignedInteger('status')->default(10)
                ->comment('订单状态：1-待发货 2-配送中 3-已完成 4-申请中 5-退货中 6-已拒绝 ');
            $table->unsignedTinyInteger('cancellation')->default(0)
                ->comment('取消人 0未取消 1买家取消 2 卖家取消  3系统自动取消 ');
            $table->timestamp('signed_at')->nullable()->default(null)->comment('签收时间');
            $table->string('receiver_city', 16)->nullable()->default(null)->comment('收货城市');
            $table->string('receiver_district', 16)->nullable()->default(null)->comment('收货人所在城市区县');
            $table->string('receiver_name', 16)->nullable()->default(null)->comment('收货姓名');
            $table->string('receiver_address', 32)->nullable()->default(null)->comment('收货地址');
            $table->string('receiver_mobile', 11)->nullable()->default(null)->comment('收货人电话');
            $table->timestamp('send_time')->nullable()->default(null)->comment('配送时间');
            $table->string('comment', 255)->nullable()->default(null)->comment('备注');
            $table->timestamp('consigned_at')->nullable()->default(null)->comment('发货时间');
            $table->unsignedTinyInteger('type')->default(0)->comment('订单类型：1-进货订单 2-退货订单');
            $table->unsignedMediumInteger('post_type')->default(0)->comment('0-无需物流，1000 - 未知运输方式 2000-空运， 3000-公路， 4000-铁路， 5000-高铁， 6000-海运 ');
            $table->boolean('score_settle')->default(false)->comment('积分是否已经结算');
            $table->string('post_no', 32)->nullable()->default(null)->comment('快递编号');
            $table->string('post_code', 6)->nullable()->default(null)->comment('邮编');
            $table->string('post_name', 64)->nullable()->default(null)->comment('快递公司名称');
            $table->string('transaction_id', 32)->nullable()->default(null)->comment('支付交易流水');
            $table->string('ip', 15)->nullable()->default(null)->comment('支付终端ip地址');
            $table->string('trade_status', 16)->nullable()->default(Order::TRADE_FINISHED)->comment('交易状态:TRADE_WAIT 等待交易 TRADE_FAILED 交易失败 TRADE_SUCCESS 交易成功 
                TRADE_FINISHED 交易结束禁止退款操作 TRADE_CANCEL 交易关闭禁止继续支付');
            $table->timestamps();
            $table->softDeletes();
            $table->index('code');
            $table->index('post_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_purchase_orders');
    }
}
