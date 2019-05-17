<?php

namespace App\Repositories;

use App\Entities\UserRechargeableCardConsumeRecords;
use App\Validators\UserRechargeableCardConsumeRecordsValidator;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class UserRechargeableCardConsumeRecordsRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class UserRechargeableCardConsumeRecordsRepositoryEloquent extends BaseRepository implements UserRechargeableCardConsumeRecordsRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserRechargeableCardConsumeRecords::class;
    }

    /**
     * Specify Validator class name
     *
     * @return mixed
     */
    public function validator()
    {

        return UserRechargeableCardConsumeRecordsValidator::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
