<?php

namespace App\Http\Controllers\Admin\Wechat;

use App\Http\Response\JsonResponse;
use App\Repositories\WechatMaterialRepository;
use App\Transformers\WechatMaterialItemTransformer;
use App\Transformers\WechatMaterialTransformer;
use Carbon\Carbon;
use Dingo\Api\Exception\ValidationHttpException;
use Dingo\Api\Http\Response as DingoResponse;
use EasyWeChat\Kernel\Messages\Article;
use Illuminate\Http\RedirectResponse;
use \Illuminate\Http\Response;
use Dingo\Api\Http\Request;
use App\Http\Requests\Admin\Wechat\MaterialsCreateRequest;
use App\Http\Requests\Admin\Wechat\MaterialsUpdateRequest;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class MaterialsController.
 *
 * @package namespace App\Http\Controllers\Admin\Wechat;
 */
class MaterialController extends Controller
{
    /**
     * @var MaterialsRepository
     */
    protected $repository;

    /**
     * MaterialsController constructor.
     *
     * @param WechatMaterialRepository $repository
     */
    public function __construct(WechatMaterialRepository $repository)
    {
        $this->repository = $repository;

        parent::__construct();
    }

    /**
     * create new temporary media
     * @param Request $request
     * @return Response|DingoResponse|RedirectResponse
     * @throws
     * */
    public function storeTemporaryMedia(Request $request)
    {
        $field = $request->input('file_field', 'file');
        $mediaId = $this->currentWechat->uploadMedia($request->input('type'), $request->file($field)->getPath());
        $material = [
            'is_temp' => true,
            'type' => $request->input('type'),
            'media_id' => $mediaId,
            'content' => [
                'expires'  => Carbon::now()->next(3)->timestamp,
                'status'   => 1
            ]
        ];

        if($request->wantsJson()) {
            return $this->response($material);
        }

        return redirect()->back()->with('message', '临时素材创建成功');
    }


    public function storeForeverNews(Request $request)
    {
        $article =new Article($request->all());
        $mediaId = $this->currentWechat->uploadArticle($article);
        $attributes['app_id'] = $this->currentWechat->appId;
        $attributes['type'] = WECHAT_NEWS_MESSAGE;
        $attributes['media_id'] = $mediaId;
        if($request->wantsJson()) {
            return $this->response($attributes);
        }

        return redirect()->back()->with('message', '图文素材创建成功');

    }

    public function uploadForeverMaterial(Request $request, string $type = 'image')
    {
        $field = $request->input('file_field', 'file');
        $url = $this->currentWechat->uploadMaterial($type, $request->file($field)->getPath());

        if($request->wantsJson()) {
            return $this->response(new JsonResponse(['url' => $url]));
        }

        return redirect()->back()->with('message', '临时素材创建成功');
    }


    public function materialStats(Request $request) {
        $stats = $this->currentWechat->materialstats();
        if($request->wantsJson()) {
            return $this->response(new JsonResponse($stats));
        }

        return redirect()->back()->with($stats);
    }

    public function materialList(Request $request) {
        $limit = $request->input('limit', PAGE_LIMIT);
        $offset = $request->input('page', 1) * $limit;
        $result = $this->currentWechat->materialList($request->input('type'), $offset, $limit);
        if ($request->wantsJson()) {
            return $this->response(new JsonResponse($result));
        }
        return redirect()->back()->with($result);
    }

    public function materialNewsUpdate(Request $request, string $mediaId)
    {
        $attributes = $request->input('article');
        $index = $request->input('index');
        $this->currentWechat->updateArticle($mediaId, new Article($attributes), $index);
        if($request->wantsJson()) {
            return $this->response(new JsonResponse(['message' => '更新成功']));
        }

        return redirect()->back()->with('message', '更新成功');
    }

    public function deleteMaterial(Request $request, string $mediaId)
    {
        $this->currentWechat->deleteMaterial($mediaId);
        if($request->wantsJson()) {
            return $this->response(new JsonResponse(['message' => '删除成功']));
        }

        return redirect()->back()->with('message', '删除成功');
    }

    public function material(Request $request, string $mediaId, string $type = null)
    {
        if($type === null || $type === 'temporary') {
            $result = $this->currentWechat->material($mediaId, $type === 'temporary');
            if($request->wantsJson()) {
                return $this->response(new JsonResponse($result));
            }

            return redirect()->back()->with($result);
        } else {
            throw new NotFoundHttpException('');
        }
    }
}