<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface OrderRepository.
 *
 * @package namespace App\Repositories;
 */
interface OrderRepository extends RepositoryInterface
{
    //
    public function pushCriteria($class);

    /**
     * @param $itemMerchandises
     * @return mixed
     */
    public function insertMerchandise( array $itemMerchandises);

    /**
     * 自提订单
     * @param string $date
     * @param int $shopId
     * @return mixed
     */
    public function storeBuffetOrders(string $date, int $shopId);

    /**
     * @param string $date
     * @param int $batch
     * @param int $shopId
     * @return mixed
     */
    public function storeSendOrders(string $date, int $batch,  int $shopId);

    /**
     * @param string $status
     * @param int $customerId
     * @param int $limit
     * @return mixed
     */
    public function userOrders(string $status, int $customerId, int $limit = 15);

    /**
     * @param $date
     * @param $type
     * @param $status
     * @param int $shopId
     * @return mixed
     */
    public function storeOrdersSummary($date, $type, $status, int $shopId);

    /**
     * @param int $shopId
     * @param string $unit
     * @param Carbon $startAt
     * @param Carbon $endAt
     * @param int $limit
     * @return Collection
     */
    public function orderStatistics(int $shopId, string $unit, Carbon $startAt, Carbon $endAt, int $limit);

    /**
     *
     * @param Collection $orders
     * @param $count
     * @param $unit
     * @return array
     */
    public function buildOrderStatisticData(Collection $orders, $count, $unit);

    public function activityUsuallyReceivingStores(int $activityId, int $customerId);

}
