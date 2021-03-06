<?php

namespace App\Repositories;

use App\Repositories\Traits\Destruct;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\FileRepository;
use App\Entities\File;
use App\Validators\FileValidator;

/**
 * Class FileRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class FileRepositoryEloquent extends BaseRepository implements FileRepository
{
    use Destruct;
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return File::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
