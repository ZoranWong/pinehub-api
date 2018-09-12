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
        $router->post('/register/user', ['as' => 'user.register.user','uses' => 'AuthController@registerUser']);
        $router->get('/user/info', ['as' => 'user.info','uses' => 'AuthController@userInfo']);
        $router->get('/new/activity', ['as' => 'user.new.activity','uses' => 'ActivityController@newActivity']);
        $router->get('/app/access/{appid}/{appsecret}', ['as' => 'user.app.access','uses' => 'AuthController@appAccess']);
        $router->get('/nearest/store', ['as' => 'user.nearest.store','uses' => 'ShopsController@nearestStore']);
        $router->get('/nearby/stores', ['as' => 'user.nearby.stores','uses' => 'ShopsController@nearbyStores']);
        $router->get('/categories', ['as' => 'user.categories','uses' => 'CategoriesController@categories']);
        $router->get('/categories/{id}/merchandises', ['as' => 'user.categories.merchandises','uses' => 'CategoriesController@categoriesMerchandises']);
        $router->get('/store/{id}/categories', ['as' => 'user.store.categories','uses' => 'CategoriesController@storeCategories']);
        $router->get('/store/{id}/merchandise/{categoryid}', ['as' => 'user.store.merchandise','uses' => 'CategoriesController@storeMerchandise']);
        $router->get('/user/tickets/{status}', ['as' => 'user.user.tickets','uses' => 'UserController@UserTickets']);
        $router->post('/create/order', ['as' => 'user.create.order','uses' => 'OrderController@createOrder']);
        $router->post('/add/merchandise', ['as' => 'user.add.merchandise','uses' => 'ShoppingCartController@addMerchandise']);
        $router->get('/shoppingcart/merchandises/{store_id}', ['as' => 'user.shoppingcart.merchandises','uses' => 'ShoppingCartController@shoppingCartMerchandises']);
    }
}