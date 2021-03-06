<?php

namespace App\Entities;

use App\Entities\Traits\ModelAttributesAccess;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class StoreShoppingCart.
 * @property int $id
 * @property int $shopId
 * @property int $appId
 * @property string $name
 * @property array $shoppingCarts
 * @package namespace App\Entities;
 */
class StoreShoppingCart extends Model implements Transformable
{
    use TransformableTrait, ModelAttributesAccess;

    protected $casts = [
        'shopping_carts' => 'json'
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'shop_id',
        'app_id',
        'name',
        'shopping_carts'
    ];

}
