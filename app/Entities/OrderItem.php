<?php /** @noinspection ALL */

namespace App\Entities;

use App\Entities\Traits\ModelAttributesAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * App\Entities\OrderItem
 *
 * @property int $id
 * @property string|null $appId 系统appid
 * @property int|null $shopId 店铺ID
 * @property int|null $memberId 买家会员id
 * @property int|null $customerId 买家ID
 * @property int $orderId 订单id
 * @property string $code 订单子项编码
 * @property float $totalAmount 应付
 * @property float $discountAmount 优惠
 * @property float $paymentAmount 实付
 * @property int $status 订单状态：0-订单取消 10-已确定 20-已支付 30-已发货 40-已完成
 * @property \Carbon\Carbon|null $signedAt 签收时间
 * @property \Carbon\Carbon|null $consignedAt 发货时间
 * @property \Carbon\Carbon|null $createdAt
 * @property \Carbon\Carbon|null $updatedAt
 * @property string|null $deletedAt
 * @property-read \App\Entities\Customer $customer
 * @property-read \App\Entities\Member|null $member
 * @property-read \App\Entities\Order $order
 * @property-read \App\Entities\OrderItemMerchandise $orderMerchandise
 * @property-read \App\Entities\Shop|null $shop
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderItem whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderItem whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderItem whereConsignedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderItem whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderItem whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderItem whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderItem wherePaymentAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderItem whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderItem whereSignedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderItem whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderItem whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderItem extends Model implements Transformable
{
    use TransformableTrait, ModelAttributesAccess;

    const ORDERITEM_NUMBER_PREFIX = 'PHS';

    protected $dates = [
        'signed_at',
        'consigned_at'
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'customer_id', 'total_amount', 'payment_amount', 'discount_amount',
        'status', 'shop_id', 'signed_at', 'consigned_at', 'order_id', 'app_id', 'member_id'
    ];

    public function member() : BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }


    public function customer() : BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function shop() : BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }

    public function order() : BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function orderMerchandise(): HasOne
    {
        return $this->hasOne(OrderItemMerchandise::class, 'order_item_id', 'id');
    }
}
