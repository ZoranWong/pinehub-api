<?php /** @noinspection ALL */

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\OrderItemRepository;
use App\Entities\OrderItem;
use App\Validators\OrderItemValidator;

/**
 * Class OrderItemRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class OrderItemRepositoryEloquent extends BaseRepository implements OrderItemRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return OrderItem::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
        OrderItem::creating(function (OrderItem &$orderItem) {
            $orderItem->code =app('uid.generator')->getSubUid($orderItem->orderCode, ORDER_SEGMENT_MAX_LENGTH);
            unset($orderItem->orderCode);
            return $orderItem;
        });
    }
    
}
