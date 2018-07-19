<?php

namespace App\Entities;

use App\Entities\Traits\ModelAttributesAccess;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * App\Entities\OrderGift
 *
 * @property int $id
 * @property string $appId 系统应用id
 * @property string $type 支付活动方式：满减/支付礼包
 * @property \Carbon\Carbon $beginAt 开始时间
 * @property \Carbon\Carbon $endAt 结束时间
 * @property int $status 状态：0-未开始 1-进行中 2-结束 3-失效
 * @property mixed $gift 礼包json：{discount:0.9, cost: 10.00, ticket_id: XXX}
 * @property \Carbon\Carbon|null $createdAt
 * @property \Carbon\Carbon|null $updatedAt
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderGift whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderGift whereBeginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderGift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderGift whereEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderGift whereGift($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderGift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderGift whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderGift whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\OrderGift whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderGift extends Model implements Transformable
{
    use TransformableTrait, ModelAttributesAccess;

    const PAY_FULL = "PAY_FULL";
    const PAY_GIFT = "PAY_GIFT";

    const WAIT = 0;
    const RUNNING = 1;
    const END = 2;
    const INVALID = 3;

    protected $dates = [
        'begin_at',
        'end_at'
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['app_id', 'name', 'begin_at', 'end_at', 'gift', 'type', 'status'];

}
