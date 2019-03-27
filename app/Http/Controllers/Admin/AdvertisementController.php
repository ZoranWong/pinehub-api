<?php/** * Created by PhpStorm. * User: katherine * Date: 19-3-19 * Time: 下午4:46 */namespace App\Http\Controllers\Admin;use App\Criteria\Admin\AdvertisementCriteria;use App\Criteria\Admin\SearchRequestCriteria;use App\Entities\Advertisement;use App\Http\Controllers\Controller;use App\Http\Requests\Admin\AdvertisementCreateRequest;use App\Http\Requests\Admin\AdvertisementUpdateRequest;use App\Repositories\AdvertisementRepository;use App\Services\AppManager;use App\Transformers\AdvertisementTransformer;use Carbon\Carbon;use Illuminate\Http\Request;use Illuminate\Support\Facades\Log;class AdvertisementController extends Controller{    /**     * @var AdvertisementRepository     */    protected $repository;    public function __construct(AdvertisementRepository $repository)    {        $this->repository = $repository;        parent::__construct();    }    // 广告列表    public function index()    {        $this->repository->pushCriteria(AdvertisementCriteria::class);        $this->repository->pushCriteria(SearchRequestCriteria::class);        $advertisements = $this->repository->orderBy('created_at', 'desc')->paginate();        return $this->response()->paginator($advertisements, new AdvertisementTransformer());    }    // 新建    public function store(AdvertisementCreateRequest $request)    {        $app = app(AppManager::class);        $data['app_id'] = $app->currentApp->id;        $data['wechat_app_id'] = $app->currentApp->wechatAppId;        $data['title'] = $request->input('title');        $data['banner_url'] = $request->input('banner_url');        $data['card_id'] = $request->input('card_id');        $data['conditions'] = $request->input('conditions');        $data['begin_at'] = $request->input('begin_at');        $data['end_at'] = $request->input('end_at');        $data['status'] = Advertisement::STATUS_WAIT;        Log::info('创建优惠券起始时间：', [Carbon::now()]);        $advertisement = $this->repository->create($data);        return $advertisement;    }    public function update(AdvertisementUpdateRequest $request, $id)    {        $data = $request->all();        $advertisement = $this->repository->find($id);        $advertisement->update($data);        return $this->response()->item($advertisement, new AdvertisementTransformer());    }}