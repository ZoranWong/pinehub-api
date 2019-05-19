<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/4/28
 * Time: 上午9:27
 */

namespace App\Routes;

use Dingo\Api\Routing\Router as DingoRouter;
use Laravel\Lumen\Routing\Router as LumenRouter;

class MiniProgramApiRoutes extends ApiRoutes
{
    /**
     * @param LumenRouter|DingoRouter $router
     * */
    public function subRoutes($router)
    {
        parent::subRoutes($router); // TODO: Change the autogenerated stub

        $router->get('/wx/login/{code}', ['as' => 'user.login', 'uses' => 'AuthController@wxLogin']);
        $router->get('/ali/login/{code}', ['as' => 'user.login', 'uses' => 'AuthController@aliLogin']);
        $router->get('/app/access', ['as' => 'user.app.access', 'uses' => 'AuthController@appAccess']);
        $router->post('/wx/register/user', ['as' => 'user.register.user', 'uses' => 'AuthController@wxRegisterUser']);

        $attributes = [];

        if ($this->app->environment() !== 'local') {
            $attributes['middleware'] = ['api.auth'];
        }

        $router->group($attributes, function ($router) {
            /** @var DingoRouter $router */
            $router->post('/formid/{formId}', ['uses' => 'FormIdController@collect']);

            $router->get('/tickets', ['as' => 'tickets', 'uses' => 'TicketController@tickets']);
            $router->get('/ticket/{cardId}', ['as' => 'ticket.detail', 'uses' => 'TicketController@show']);
            $router->get('/user/receive/ticket/{cardId}', ['as' => 'tickets', 'uses' => 'TicketController@userReceiveTicket']);
            $router->get('/user/info', ['as' => 'user.info', 'uses' => 'AuthController@userInfo']);

            $router->post('/save/mobile', ['as' => 'user.save.mobile', 'uses' => 'AuthController@saveMobile']);


            $router->get('/new/events', ['as' => 'user.new.events', 'uses' => 'ActivityController@newEventsActivity']);
            $router->get('/new/events/{activityId}/merchandises', ['as' => 'user.new.activity.merchandises', 'uses' => 'ActivityController@newActivityMerchandises']);


            $router->get('/stores', ['as' => 'list.store', 'uses' => 'ShopsController@stores']);
            $router->get('/nearest/store', ['as' => 'user.nearest.store', 'uses' => 'ShopsController@nearestStore']);
            $router->get('/nearby/stores', ['as' => 'user.nearby.stores', 'uses' => 'ShopsController@nearbyStores']);
            $router->get('/store/info', ['as' => 'user.store.statistics', 'uses' => 'ShopsController@storeInfo']);
            $router->get('/store/{id}/info', ['as' => 'user.store.statistics', 'uses' => 'ShopsController@showShop']);
            $router->get('/store/sales/statistics', ['as' => 'user.store.sell.statistics', 'uses' => 'ShopsController@storeSalesStatistics']);
            $router->get('/search/shop/{shopId}/merchandises', ['as' => 'user.search.shop.merchandises', 'uses' => 'ShopsController@searchShopMerchandises']);


            $router->get('/categories', ['as' => 'user.categories', 'uses' => 'CategoriesController@categories']);
            $router->get('/store/{id}/categories', ['as' => 'user.store.categories', 'uses' => 'CategoriesController@storeCategories']);
            $router->get('/store/stock/statistics', ['as' => 'user.store.stock.statistics', 'uses' => 'CategoriesController@storeStockStatistics']);
            $router->get('/reserve/search/merchandises', ['as' => 'user.reserve.search.', 'uses' => 'CategoriesController@reserveSearchMerchandises']);
            $router->put('/store/merchandise/{merchandiseId}/stock', ['as' => 'user.store.merchandise.stock', 'uses' => 'CategoriesController@storeMerchandiseStock']);
            $router->get('/categories/{id}/merchandises', ['as' => 'user.categories.merchandises', 'uses' => 'CategoriesController@categoriesMerchandises']);
            $router->get('/store/{id}/category/{categoryId}/merchandises', ['as' => 'user.store.category.merchandise', 'uses' => 'CategoriesController@storeCategoryMerchandise']);


            $router->get('/user/tickets/{status}', ['as' => 'user.user.tickets', 'uses' => 'UserController@userTickets']);
            $router->get('/customer/ticket/cards/{status}', ['as' => 'user.customer.ticket.cards', 'uses' => 'UserController@customerTicketCards']);
            $router->post('/feed/back/message', ['as' => 'user.feed.back.message', 'uses' => 'UserController@feedBackMessage']);

            $router->get('/customer/rechargeable_cards', ['uses' => 'UserController@customerRechargeableCards']);

            $router->get('/cancel/order/{id}', ['as' => 'user.cancel.order.', 'uses' => 'OrderController@cancelOrder']);
            $router->get('/confirm/order/{id}', ['as' => 'user.confirm.order.', 'uses' => 'OrderController@confirmOrder']);
            $router->get('/{status}/orders', ['as' => 'user.orders', 'uses' => 'OrderController@userOrders']);
            $router->post('/create/order', ['as' => 'user.create.order', 'uses' => 'OrderController@createOrder']);
            $router->get('/order/{id}', ['as' => 'user.order.detail', 'uses' => 'OrderController@show']);
            $router->get('/{type}/order/{orderId}/payment', ['as' => 'user.again.order', 'uses' => 'OrderController@payByOrderId']);
            $router->get('/store/buffet/orders', ['as' => 'user.store.buffet.orders', 'uses' => 'OrderController@storeBuffetOrders']);
            $router->get('/store/send/orders', ['as' => 'user.store.send.orders', 'uses' => 'OrderController@storeSendOrders']);
            $router->get('/store/orders/summary', ['as' => 'user.store.orders.summary', 'uses' => 'OrderController@storeOrdersSummary']);

            $router->get('/shop/{storeId}/orders', ['as' => 'user.store.orders', 'uses' => 'OrderController@storeOrders']);

            $router->get('/receiving/shop/address/{activityId}', ['as' => 'user.receiving.shop.address', 'uses' => 'OrderController@receivingShopAddress']);


            $router->get('/store/purchase/statistics', ['as' => 'user.store.purchase.statistics', 'uses' => 'PurchaseOrderController@storePurchaseStatistics']);
            $router->get('/store/code/order/merchandise/up', ['as' => 'user.store.code.order.merchandise.up', 'uses' => 'PurchaseOrderController@storeCodeOrderMerchandiseUp']);


            $router->post('/shop/{storeId}/shoppingcart/merchandise', ['as' => 'user.add.shop.shoppingcart.merchandise', 'uses' => 'ShoppingCartController@storeShoppingCartAddMerchandise']);
            $router->post('/new/events/{activityId}/shoppingcart/merchandise', ['as' => 'user.add.activity.shoppingcart.merchandise', 'uses' => 'ShoppingCartController@activityShoppingCartAddMerchandise']);
            $router->post('/shoppingcart/merchandise', ['as' => 'user.add.shoppingcart.merchandise', 'uses' => 'ShoppingCartController@bookingMallShoppingCartAddMerchandise']);
            $router->post('merchant/shoppingcart/merchandise', ['as' => 'user.add.merchant.shoppingcart.merchandise', 'uses' => 'ShoppingCartController@addMerchantShoppingCart']);
            $router->post('merchant/shoppingcart/save', ['as' => 'user.save.merchant.shoppingcart.merchandise', 'uses' => 'ShoppingCartController@saveMerchantShoppingCart']);


            $router->put('/shop/{storeId}/shoppingcart/{shoppingCartId}/merchandise', ['as' => 'user.change.shop.shoppingcart.merchandise', 'uses' => 'ShoppingCartController@storeShoppingCartMerchandiseNumChange']);
            $router->put('/new/events/{activityId}/shoppingcart/{shoppingCartId}/merchandise', ['as' => 'user.change.activity.shoppingcart.merchandise', 'uses' => 'ShoppingCartController@activityShoppingCartMerchandiseNumChange']);
            $router->put('/shoppingcart/{shoppingCartId}/merchandise', ['as' => 'user.change.shoppingcart.merchandise', 'uses' => 'ShoppingCartController@bookingMallShoppingCartMerchandiseNumChange']);
            // $router->put('/shoppingcart/{shoppingCartId}/merchandise', ['as' => 'user.change.shoppingcart.merchandise','uses' => 'ShoppingCartController@bookingMallShoppingCartMerchandiseNumChange']);
            $router->put('/merchant/{storeId}/shoppingcart/{shoppingCartId}/merchandise', ['as' => 'user.change.merchant.shoppingcart.merchandise', 'uses' => 'ShoppingCartController@merchantShoppingCartMerchandiseNumChange']);


            $router->delete('/shoppingcart/{shoppingCartId}', ['as' => 'user.delete.shoppingcart', 'uses' => 'ShoppingCartController@shoppingCartDelete']);

            $router->get('/clear/shop/{storeId}/shoppingcart', ['as' => 'user.clear.shop.merchandise', 'uses' => 'ShoppingCartController@clearStoreShoppingCart']);
            $router->get('/clear/new/events/{activityId}/shoppingcart', ['as' => 'user.clear.activity.merchandise', 'uses' => 'ShoppingCartController@clearActivityShoppingCart']);
            $router->get('/clear/shoppingcart', ['as' => 'user.clear.merchandise', 'uses' => 'ShoppingCartController@clearBookingMallShoppingCart']);
            $router->get('/clear/merchant/shoppingcart', ['as' => 'user.clear.merchant.merchandise', 'uses' => 'ShoppingCartController@clearMerchantShoppingCart']);


            $router->get('/shop/{storeId}/shoppingcart/merchandises', ['as' => 'user.shoppingcart.shop.merchandises', 'uses' => 'ShoppingCartController@storeShoppingCartMerchandises']);
            $router->get('/new/events/{activityId}/shoppingcart/merchandises', ['as' => 'user.shoppingcart.activity.merchandises', 'uses' => 'ShoppingCartController@activityShoppingCartMerchandises']);
            $router->get('/shoppingcart/merchandises', ['as' => 'user.shoppingcart.merchandises', 'uses' => 'ShoppingCartController@bookingMallShoppingCartMerchandises']);
            $router->get('/merchant/shoppingcart/merchandises', ['as' => 'user.merchant.shoppingcart.merchandises', 'uses' => 'ShoppingCartController@merchantShoppingCartMerchandises']);
            $router->get('/merchant/saved/shoppingcarts', ['as' => 'user.merchant.shoppingcarts', 'uses' => 'ShoppingCartController@merchantSavedShoppingCarts']);

            $router->get('/merchant/saved/shoppingcart/{id}/use', ['as' => 'user.merchant.shoppingcart.use', 'uses' => 'ShoppingCartController@useMerchantSavedShoppingCart']);

            $router->get('/advertisement/latest', ['as' => 'advertisements', 'uses' => 'AdvertisementController@getLatestAdvertisement']);

            // 卡种
            $router->get('/rechargeable_cards', ['uses' => 'RechargeableCardController@index']);
        });

        $router->addRoute(['GET', 'POST'], '/wechat/payment/notify/{token?}', [
            'middleware' => 'setPaymentConfig:wechat',
            'as' => 'wechat.payment.notify',
            'uses' => 'PaymentController@notify']);

        $router->addRoute(['GET', 'POST'], '/ali/payment/notify/{token?}', [
            'middleware' => 'setPaymentConfig:ali',
            'as' => 'ali.payment.notify',
            'uses' => 'PaymentController@notify']);
    }
}