<?php

namespace App\Repositories;

use App\Repositories\Traits\Destruct;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Entities\ScoreSettleCash;
use App\Validators\ScoreSettleCashValidator;

/**
 * Class ScoreSettleCashRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class ScoreSettleCashRepositoryEloquent extends BaseRepository implements ScoreSettleCashRepository
{
    use Destruct;
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ScoreSettleCash::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
