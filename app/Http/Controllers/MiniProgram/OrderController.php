<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/10
 * Time: 18:44
 */

namespace App\Http\Controllers\MiniProgram;


use App\Entities\Card;
use App\Entities\CustomerTicketCard;
use App\Entities\MemberCard;
use App\Entities\MpUser;
use App\Entities\Order;
use App\Entities\Shop;
use App\Entities\ShoppingCart;
use App\Exceptions\UnifyOrderException;
use Carbon\Carbon;
use Dingo\Api\Http\Request;
use App\Repositories\AppRepository;
use App\Http\Requests\MiniProgram\OrderCreateRequest;
use App\Repositories\OrderRepository;
use App\Repositories\CardRepository;
use App\Repositories\ShoppingCartRepository;
use App\Repositories\MerchandiseRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\MemberCardRepository;
use App\Repositories\CustomerTicketCardRepository;
use App\Transformers\Mp\OrderTransformer;
use App\Transformers\Mp\OrderStoreBuffetTransformer;
use App\Transformers\Mp\OrderStoreSendTransformer;
use App\Transformers\Mp\StatusOrdersTransformer;
use App\Transformers\Mp\StoreOrdersSummaryTransformer;
use App\Transformers\Mp\ReceivingShopAddressTransformer;
use App\Repositories\ShopRepository;
use App\Http\Response\JsonResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Application;
use App\Exceptions\UserCodeException;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\MiniProgram\StoreOrdersSummaryRequest;
use App\Http\Requests\MiniProgram\StoreSendOrdersRequest;
use App\Http\Requests\MiniProgram\StoreBuffetOrdersRequest;

/**
 * @property CardRepository cardRepository
 */
class OrderController extends Controller
{
    /**
     * @var OrderRepository|null
     */
    protected $orderRepository = null;

    /**
     * @var null
     */
    protected $userTicketRepository = null;

    /**
     * @var ShoppingCartRepository|null
     */
    protected $shoppingCartRepository = null;

    /**
     * @var MerchandiseRepository|null
     */
    protected $merchandiseRepository = null;

    /**
     * @var ShopRepository|null
     */
    protected $shopRepository = null;

    /**
     * @var OrderItemRepository|null
     */
    protected $orderItemRepository = null;

    /**
     * @var Application|null
     */
    protected $app = null;

    /**
     * @var MemberCardRepository|null
     */
    protected $memberCardRepository = null;

    /**
     * @var CustomerTicketCardRepository|null
     */
    protected $customerTicketCardRepository = null;

    /**
     * OrderController constructor.
     * @param AppRepository $appRepository
     * @param CustomerTicketCardRepository $customerTicketCardRepository
     * @param MemberCardRepository $memberCardRepository
     * @param Application $app
     * @param OrderItemRepository $orderItemRepository
     * @param ShopRepository $shopRepository
     * @param MerchandiseRepository $merchandiseRepository
     * @param CardRepository $cardRepository
     * @param ShoppingCartRepository $shoppingCartRepository
     * @param OrderRepository $orderRepository
     * @param Request $request
     */
    public function __construct(AppRepository $appRepository,
                                CustomerTicketCardRepository $customerTicketCardRepository,
                                MemberCardRepository $memberCardRepository,
                                Application $app,
                                OrderItemRepository $orderItemRepository ,
                                ShopRepository $shopRepository,
                                MerchandiseRepository $merchandiseRepository ,
                                CardRepository $cardRepository,
                                ShoppingCartRepository $shoppingCartRepository,
                                OrderRepository $orderRepository ,
                                Request $request)
    {
        parent::__construct($request, $appRepository);

        $this->appRepository                = $appRepository;
        $this->orderRepository              = $orderRepository;
        $this->cardRepository               = $cardRepository;
        $this->shoppingCartRepository       = $shoppingCartRepository;
        $this->merchandiseRepository        = $merchandiseRepository;
        $this->shopRepository               = $shopRepository;
        $this->orderItemRepository          = $orderItemRepository;
        $this->app                          = $app;
        $this->memberCardRepository         = $memberCardRepository;
        $this->customerTicketCardRepository = $customerTicketCardRepository;
    }

    /**
     * 重新支付订单
     * @param int $orderId
     * @return mixed
     */

    public function againOrder(int $orderId){
        $order = $this->orderRepository->findWhere(['id'=>$orderId])->first();
        return $this->order($order);
    }

    protected function order($order){

        return DB::transaction(function () use(&$order){
            //跟微信打交道生成预支付订单
            $result = app('wechat')->unify($order, $order->wechatAppId, app('tymon.jwt.auth')->getToken());
            if($result['return_code'] === 'SUCCESS'){
                $order->status = Order::MAKE_SURE;
                $order->save();
                $sdkConfig  = app('wechat')->jssdk($result['prepay_id'], $order->wechatAppId);
                $result['sdk_config'] = $sdkConfig;

                return $this->response(new JsonResponse($result));
            }else{
                throw new UnifyOrderException($result['return_msg']);
            }
        });
    }

    /**
     * 获取购物车
     * @param array $order
     * @param MpUser $user
     * @return Collection
     */
    protected function getShoppingCarts(array $order, MpUser $user)
    {
        //有店铺id就是今日店铺下单的购物车,有活动商品id就是在活动商品里的购物车信息,两个都没有的话就是预定商城下单的购物车
        if (isset($order['store_id']) && $order['store_id']){
            return $this->shoppingCartRepository
                ->findWhere([
                    'customer_id' => $user->id,
                    'shop_id'     =>$order['store_id']
                ]);

        }elseif (isset($order['activity_id']) && $order['activity_id']){
            return $this->shoppingCartRepository
                ->findWhere([
                    'customer_id'              => $user->id,
                    'activity_id' => $order['activity_id']]);

        }else{
            return $this->shoppingCartRepository
                ->findWhere([
                    'customer_id'               => $user->id,
                    'activity_id'  => null,
                    'shop_id'                   => null
                ]);

        }
    }

    protected function useTicket(array &$order, MpUser $user)
    {
        if(isset($order['card_id']) && $order['card_id'] ){
            $condition = [
                'card_id' => $order['card_id'],
                'status'  => CustomerTicketCard::STATUS_ON,
                'active'  => CustomerTicketCard::ACTIVE_ON,
            ];
            if (isset($order['card_code']) && $order['card_code']) {
                $condition['card_code'] = $order['card_code'];
            }
            $customerTicketRecord = $user->ticketRecords()->with('card')
                ->where($condition)
                ->orderByDesc('created_at')
                ->first();
            if ($customerTicketRecord){
                $card = $customerTicketRecord['card'];
                with($card, function (Card $card) use($order){
                    if ($card->cardType === Card::DISCOUNT) {
                        $order['discount_amount'] = $card->cardInfo['discount'] * $order['total_amount'];
                    }else if($card->cardType === Card::CASH){
                        $order['discount_amount'] = $card ? $card['card_info']['reduce_cost'] : 0;
                    }
                });
                $order['card_id'] = $card['card_id'];
            }else{
                throw new ModelNotFoundException('使用的优惠券不存在');
            }
        }
    }

    /**
     * 创建自订单
     *
     * @param array $order
     * @param Collection $shoppingCarts
     */
    protected function buildOrderItemsFromShoppingCarts(array &$order, Collection $shoppingCarts)
    {
        $order['order_items'] = [];
        $order['shopping_cart_ids']  = [];
        //取出购物车商品信息组装成一个子订单数组
        $shoppingCarts->map(function (ShoppingCart $cart) use(&$order){
            $orderItem = $cart->only(['activity_id', 'shop_id', 'customer_id', 'merchandise_id', 'quality', 'sku_product_id']);
            $orderItem['total_amount'] = $cart->amount;
            $orderItem['payment_amount'] = $cart->amount;
            $orderItem['status'] = Order::WAIT;
            $orderItem['type'] = $order['type'];
            $orderItem['pick_up_method'] = $order['pick_up_method'];
            array_push($order['order_items'], $orderItem);
            array_push($order['shopping_cart_ids'], $cart->id);
        });
    }

    protected function setCustomerInfoForOrder(array &$order, MpUser $user)
    {
        $order['app_id'] = $user->appId;
        $order['member_id'] = $user->memberId;
        $order['wechat_app_id'] = $user->platformAppId;
        $order['customer_id'] = $user->id;
        $order['open_id']  = $user->platformOpenId;
        $order['app_id'] = $user->appId;
        $order['member_id'] = $user->memberId;
        $order['wechat_app_id'] = $user->platformAppId;
        $order['customer_id'] = $user->id;
        $order['open_id']  = $user->platformOpenId;
    }

    /**
     * 创建订单
     * @param OrderCreateRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function createOrder(OrderCreateRequest $request)
    {
        $user = $this->mpUser();
        $order = $request->all();
        $now = Carbon::now();
        if (isset($order['receiver_address']) && isset($order['build_num']) && isset($order['room_num'])){
            $address = [
                'receiver_address' => $order['receiver_address'],
                'build_num'        => $order['build_num'],
                'room_num'         => $order['room_num']
            ];
            $order['receiver_address'] = json_encode($address);
        }

        $this->setCustomerInfoForOrder($order, $user);
        $order['discount_amount'] = 0;
        $shop = null;
        if($order['receiving_shop_id']) {
            $shop = Shop::find($order['receiving_shop_id']);
        }
        if(!$shop && $order['store_id']) {
            $shop = Shop::find($order['store_id']);
        }
        if (!isset($order['pick_date']) || !$order['pick_date']){
            $order['pick_date'] = Carbon::now()->format('Y-m-d');
        }

        $order['pick_up_start_time'] = "{$order['pick_date']} {$shop->startAt}:00";
        $order['pick_up_end_time']   = "{$order['pick_date']} {$shop->endAt}:00";
        unset($order['pick_date']);

        /** @var Collection $shoppingCarts */
        $shoppingCarts = $this->getShoppingCarts($order, $user);

        $order['total_amount']    = round($shoppingCarts->sum('amount'),2);

        $this->useTicket($order, $user);

        $order['shop_id'] = isset($order['store_id']) ? $order['store_id'] : null;

        $order['merchandise_num'] = $shoppingCarts->sum('quality');

        $order['payment_amount']  = round(($order['total_amount'] - $order['discount_amount']),2);

        $order['year'] = $now->year;
        $order['month'] = $now->month;
        $order ['day']   = $now->day;
        $order['week']  = $now->dayOfWeekIso;
        $order['hour']  = $now->hour;
        $this->buildOrderItemsFromShoppingCarts($order, $shoppingCarts);
        Log::info('create order info', $order);
        //生成提交中的订单
        $order = $this->app
            ->make('order.builder')
            ->setInput($order)
            ->handle();

        return $this->order($order);
    }

    /**
     * 自提订单
     * @param StoreBuffetOrdersRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function storeBuffetOrders(StoreBuffetOrdersRequest $request){
        $user = $this->shopManager();

        /** @var Shop $shop */
        $shop = $this->shopRepository
            ->findWhere(['user_id'  =>  $user->id])
            ->first();

        if ($shop){
            $sendTime = $request->all();
            //查询今日下单和预定商城的所有自提订单
            $items = $this->orderRepository
                ->storeBuffetOrders($sendTime,  $shop->id);

            return $this->response()
                ->paginator($items,new OrderStoreBuffetTransformer());
        }else{
            throw new ModelNotFoundException('您不是店铺老板无权查询此接口');
        }
    }

    /**
     * 配送订单
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     */
    public function storeSendOrders(StoreSendOrdersRequest $request)
    {
        $user = $this->mpUser();

        $shopUser = $this->shopRepository
            ->findWhere(['user_id'   =>  $user['member_id']])
            ->first();

        if ($shopUser) {
            $userId     = $shopUser['id'];
            $sendTime   = $request->all();

           //查询今日下单和预定商城的所有配送订单
            $items = $this->orderRepository
                ->storeSendOrders($sendTime,$userId);

            return $this->response()->paginator($items,new OrderStoreSendTransformer());
        }

        return $this->response(new JsonResponse(['shop_id' => $shopUser]));
    }

    /**
     * 所有订单信息
     * @param string $status
     *
     * @return \Dingo\Api\Http\Response
     */

    public function orders(string  $status){
        $user   = $this->mpUser();

        $customerId = $user['id'];

        $items = $this->orderRepository
            ->orders($status,   $customerId);
        return $this->response()
            ->paginator($items, new StatusOrdersTransformer());
    }

    /**
     * 销售订单汇总
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     */
    public function storeOrdersSummary(StoreOrdersSummaryRequest $request){
        $user = $this->mpUser();

        $shopUser = $this->shopRepository
            ->findWhere(['user_id'  =>  $user['member_id']])
            ->first();

        if ($shopUser) {
            $userId = $shopUser['id'];

            $request = $request->all();

            $items = $this->orderRepository
                ->storeOrdersSummary($request,  $userId);
            return $this->response()
                ->paginator($items, new StoreOrdersSummaryTransformer());
        }

        return $this->response(new JsonResponse(['shop_id' => $shopUser]));
    }

    /**
     * 取消订单
     * @param int $id
     */
    public function cancelOrder(int $id){
        $status = ['status' => Order::CANCEL];

        $statusOrder = $this->orderRepository->find($id);

        if ($statusOrder['status'] == '100' || $statusOrder['status'] == '200'){

            $items = $this->orderItemRepository
                ->findWhere(['order_id' => $id]);


            foreach ($items as $v) {
                $this->orderItemRepository
                    ->update($status, $v['id']);
            }

            $item = $this->orderRepository
                ->update($status, $id);

            return $this->response()->item($item, new StatusOrdersTransformer());
        }else{
            $errCode = '状态提交错误';
            throw new UserCodeException($errCode);
        }

    }

    /**
     * 确认订单
     * @param int $id
     * @return mixed
     */
    public function confirmOrder(int $id){
        $status = ['status' => Order::COMPLETED];

        $statusOrder = $this->orderRepository->find($id);

        if ($statusOrder['status'] == '300' || $statusOrder['status'] == '400'){

            $items = $this->orderItemRepository
                ->findWhere(['order_id'=>$id]);

            foreach ($items as $v){
                $this->orderItemRepository
                    ->update($status,$v['id']);
            }

            $item = $this->orderRepository
                ->update($status, $id);

            return $this->response()->item($item, new StatusOrdersTransformer());
        }else{
            $errCode = '状态提交错误';
            throw new UserCodeException($errCode);
        }

    }

    /**
     * 新品预定获取常用地址
     * @param int $activityId
     * @return \Dingo\Api\Http\Response
     */
    public function receivingShopAddress(int $activityId){
        $user = $this->mpUser();
        $receivingShopOrders = $this->orderRepository->receivingShopAddress($activityId,$user['id']);
        return $this->response()->paginator($receivingShopOrders,new ReceivingShopAddressTransformer());
    }

}